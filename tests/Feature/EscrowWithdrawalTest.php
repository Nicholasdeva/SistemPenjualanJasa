<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Dispute;
use App\Models\Order;
use App\Models\Payment;
use App\Models\SellerProfile;
use App\Models\Service;
use App\Models\ServicePackage;
use App\Models\User;
use App\Models\Withdrawal;
use App\Services\EscrowService;
use App\Services\WithdrawalService;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class EscrowWithdrawalTest extends TestCase
{
    use RefreshDatabase;

    private User $seller;
    private User $buyer;
    private User $admin;
    private ServicePackage $package;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seller = User::factory()->create(['role' => 'seller']);
        $this->buyer  = User::factory()->create(['role' => 'buyer']);
        $this->admin  = User::factory()->create(['role' => 'admin']);

        SellerProfile::create([
            'user_id'        => $this->seller->id,
            'ktp_number'     => '1871000000000001',
            'bank_name'      => 'BCA',
            'bank_account'   => '1234567890',
            'wallet_balance' => 0,
            'escrow_balance' => 0,
            'is_verified'    => true,
        ]);

        $category = Category::create(['name' => 'Desain', 'slug' => 'desain']);
        $service  = Service::create([
            'seller_id'   => $this->seller->id,
            'category_id' => $category->id,
            'title'       => 'Desain Logo',
            'description' => 'Desain logo profesional',
            'status'      => Service::STATUS_PUBLISHED,
        ]);
        $this->package = ServicePackage::create([
            'service_id'     => $service->id,
            'tier_name'      => 'Basic',
            'price'          => 100000,
            'delivery_days'  => 3,
            'revision_limit' => 2,
        ]);
    }

    private function makeOrder(string $status = 'paid'): Order
    {
        static $n = 0;
        $n++;

        $order = Order::create([
            'invoice_no'         => "INV-TEST-{$n}",
            'buyer_id'           => $this->buyer->id,
            'service_package_id' => $this->package->id,
            'status'             => $status,
            'total_price'        => 100000,
            'platform_fee'       => 10000,
        ]);

        Payment::create([
            'order_id'       => $order->id,
            'payment_method' => 'simulation',
            'amount'         => 100000,
            'status'         => 'paid',
            'paid_at'        => now(),
        ]);

        return $order;
    }

    private function profile(): SellerProfile
    {
        return SellerProfile::where('user_id', $this->seller->id)->first();
    }

    // ---------------- EscrowService ----------------

    public function test_hold_adds_full_total_to_escrow(): void
    {
        app(EscrowService::class)->hold($this->makeOrder());

        $this->assertEquals(100000, (float) $this->profile()->escrow_balance);
        $this->assertEquals(0, (float) $this->profile()->wallet_balance);
    }

    public function test_release_moves_net_amount_to_wallet_and_completes_order(): void
    {
        $order  = $this->makeOrder('delivered');
        $escrow = app(EscrowService::class);
        $escrow->hold($order);

        $escrow->release($order);

        $this->assertEquals(0, (float) $this->profile()->escrow_balance);
        $this->assertEquals(90000, (float) $this->profile()->wallet_balance);
        $this->assertSame('completed', $order->fresh()->status);
    }

    public function test_release_cannot_run_twice(): void
    {
        $order  = $this->makeOrder('delivered');
        $escrow = app(EscrowService::class);
        $escrow->hold($order);
        $escrow->release($order);

        $this->expectException(DomainException::class);
        $escrow->release($order);
    }

    public function test_refund_removes_escrow_and_cancels_order(): void
    {
        $order  = $this->makeOrder('disputed');
        $escrow = app(EscrowService::class);
        $escrow->hold($order);

        $escrow->refund($order);

        $this->assertEquals(0, (float) $this->profile()->escrow_balance);
        $this->assertEquals(0, (float) $this->profile()->wallet_balance);
        $this->assertSame('cancelled', $order->fresh()->status);
        $this->assertSame('refunded', $order->payments()->first()->status);
    }

    public function test_release_fails_when_escrow_is_insufficient(): void
    {
        $order = $this->makeOrder('delivered'); // escrow tidak pernah di-hold

        $this->expectException(DomainException::class);
        app(EscrowService::class)->release($order);
    }

    // ---------------- WithdrawalService ----------------

    public function test_request_deducts_wallet_immediately(): void
    {
        $this->profile()->update(['wallet_balance' => 150000]);

        $w = app(WithdrawalService::class)->request($this->seller, 50000);

        $this->assertSame('pending', $w->status);
        $this->assertSame('BCA - 1234567890', $w->bank_account);
        $this->assertEquals(100000, (float) $this->profile()->wallet_balance);
    }

    public function test_request_cannot_exceed_wallet_even_across_multiple_requests(): void
    {
        $this->profile()->update(['wallet_balance' => 60000]);
        $service = app(WithdrawalService::class);
        $service->request($this->seller, 50000);

        $this->expectException(ValidationException::class);
        $service->request($this->seller, 20000);
    }

    public function test_unverified_seller_cannot_withdraw(): void
    {
        $this->profile()->update(['wallet_balance' => 100000, 'is_verified' => false]);

        try {
            app(WithdrawalService::class)->request($this->seller, 50000);
            $this->fail('Penarikan seharusnya ditolak untuk penjual yang belum diverifikasi.');
        } catch (ValidationException) {
            $this->assertEquals(100000, (float) $this->profile()->wallet_balance);
            $this->assertDatabaseCount('withdrawals', 0);
        }
    }

    public function test_reject_returns_amount_to_wallet(): void
    {
        $this->profile()->update(['wallet_balance' => 100000]);
        $service = app(WithdrawalService::class);
        $w = $service->request($this->seller, 40000);

        $service->reject($w, $this->admin);

        $this->assertSame('rejected', $w->fresh()->status);
        $this->assertSame($this->admin->id, $w->fresh()->admin_id);
        $this->assertEquals(100000, (float) $this->profile()->wallet_balance);
    }

    public function test_approve_keeps_wallet_deducted_and_cannot_be_reprocessed(): void
    {
        $this->profile()->update(['wallet_balance' => 100000]);
        $service = app(WithdrawalService::class);
        $w = $service->request($this->seller, 40000);

        $this->actingAs($this->admin)
            ->patch(route('admin.withdrawals.process', $w))
            ->assertSessionHas('success');

        $this->assertSame('approved', $w->fresh()->status);
        $this->assertSame($this->admin->id, $w->fresh()->admin_id);
        $this->assertEquals(60000, (float) $this->profile()->wallet_balance);

        $this->expectException(DomainException::class);
        $service->reject($w, $this->admin);
    }

    // ---------------- HTTP integration ----------------

    public function test_seller_withdrawal_form_deducts_wallet(): void
    {
        $this->profile()->update(['wallet_balance' => 80000]);

        $this->actingAs($this->seller)
            ->post(route('seller.withdrawals.store'), ['amount' => 30000])
            ->assertRedirect(route('seller.withdrawals.index'));

        $this->assertEquals(50000, (float) $this->profile()->wallet_balance);
        $this->assertDatabaseHas('withdrawals', ['seller_id' => $this->seller->id, 'status' => 'pending']);
    }

    public function test_seller_withdrawal_form_rejects_amount_over_wallet(): void
    {
        $this->profile()->update(['wallet_balance' => 20000]);

        $this->actingAs($this->seller)
            ->post(route('seller.withdrawals.store'), ['amount' => 30000])
            ->assertSessionHasErrors('amount');

        $this->assertEquals(20000, (float) $this->profile()->wallet_balance);
        $this->assertDatabaseCount('withdrawals', 0);
    }

    public function test_admin_reject_route_refunds_wallet(): void
    {
        $this->profile()->update(['wallet_balance' => 50000]);
        $w = app(WithdrawalService::class)->request($this->seller, 50000);

        $this->actingAs($this->admin)
            ->patch(route('admin.withdrawals.reject', $w))
            ->assertSessionHas('success');

        $this->assertEquals(50000, (float) $this->profile()->wallet_balance);
        $this->assertSame($this->admin->id, $w->fresh()->admin_id);
    }

    public function test_non_admin_cannot_access_admin_panel_or_process_withdrawals(): void
    {
        $this->profile()->update(['wallet_balance' => 50000]);
        $withdrawal = app(WithdrawalService::class)->request($this->seller, 50000);

        $this->actingAs($this->buyer)
            ->get(route('admin.dashboard'))
            ->assertForbidden();

        $this->actingAs($this->seller)
            ->patch(route('admin.withdrawals.process', $withdrawal))
            ->assertForbidden();

        $this->assertSame('pending', $withdrawal->fresh()->status);
        $this->assertNull($withdrawal->fresh()->admin_id);
        $this->assertEquals(0, (float) $this->profile()->wallet_balance);
    }

    public function test_seller_verification_records_admin_and_requires_complete_data(): void
    {
        $this->profile()->update(['is_verified' => false, 'ktp_number' => null]);

        $this->actingAs($this->admin)
            ->patch(route('admin.sellers.verify', $this->seller))
            ->assertSessionHas('warning');

        $this->assertFalse($this->profile()->fresh()->is_verified);

        $this->profile()->update(['ktp_number' => '1871000000000001']);
        $this->actingAs($this->admin)
            ->patch(route('admin.sellers.verify', $this->seller))
            ->assertSessionHas('success');

        $this->assertTrue($this->profile()->fresh()->is_verified);
        $this->assertSame($this->admin->id, $this->profile()->fresh()->verified_by);

        $this->actingAs($this->admin)
            ->patch(route('admin.sellers.unverify', $this->seller))
            ->assertSessionHas('success');

        $this->assertFalse($this->profile()->fresh()->is_verified);
        $this->assertSame($this->admin->id, $this->profile()->fresh()->verified_by);
    }

    public function test_moderating_service_hides_it_from_public_and_records_admin(): void
    {
        $service = Service::where('seller_id', $this->seller->id)->firstOrFail();

        $this->actingAs($this->admin)
            ->patch(route('admin.services.deactivate', $service))
            ->assertSessionHas('success');

        $this->assertSame(Service::STATUS_DRAFT, $service->fresh()->status);
        $this->assertSame($this->admin->id, $service->fresh()->moderated_by);
        $this->assertTrue($service->fresh()->moderation_locked);
        $this->get(route('catalog.show', $service))->assertNotFound();

        $this->actingAs($this->seller)
            ->put(route('seller.services.update', $service), [
                'category_id' => $service->category_id,
                'title' => 'Jasa yang dicoba ditayangkan ulang',
                'description' => 'Deskripsi jasa yang cukup panjang untuk lolos validasi server.',
                'status' => Service::STATUS_PUBLISHED,
                'packages' => [
                    ['tier_name' => 'Basic', 'price' => 10000, 'delivery_days' => 1, 'revision_limit' => 0],
                ],
            ])
            ->assertSessionHasErrors('status');

        $this->assertSame(Service::STATUS_DRAFT, $service->fresh()->status);

        $this->actingAs($this->admin)
            ->patch(route('admin.services.activate', $service))
            ->assertSessionHas('success');

        $this->assertSame(Service::STATUS_PUBLISHED, $service->fresh()->status);
        $this->assertFalse($service->fresh()->moderation_locked);
        $this->get(route('catalog.show', $service))->assertOk();
    }

    public function test_dashboard_and_cash_flow_use_database_totals_and_date_filters(): void
    {
        $order = $this->makeOrder('completed');
        $payment = $order->payments()->firstOrFail();
        $payment->update(['transaction_id' => 'TXN-TEST-1', 'paid_at' => now()]);

        $this->profile()->update(['wallet_balance' => 20000]);
        $withdrawal = app(WithdrawalService::class)->request($this->seller, 20000);
        $this->actingAs($this->admin)
            ->patch(route('admin.withdrawals.process', $withdrawal))
            ->assertSessionHas('success');

        $this->actingAs($this->admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Pendapatan platform')
            ->assertSee('Rp10.000')
            ->assertSee('Pesanan berdasarkan status');

        $this->actingAs($this->admin)
            ->get(route('admin.cash-flow.index', ['from' => now()->toDateString(), 'to' => now()->toDateString()]))
            ->assertOk()
            ->assertSee('TXN-TEST-1')
            ->assertSee('Pembayaran masuk')
            ->assertSee('Rp100.000')
            ->assertSee('WD-' . $withdrawal->id)
            ->assertSee('Rp20.000');

        $this->actingAs($this->admin)
            ->get(route('admin.cash-flow.index', ['from' => now()->addDay()->toDateString()]))
            ->assertOk()
            ->assertSee('Tidak ada pembayaran');
    }

    public function test_buyer_approval_releases_escrow(): void
    {
        $order = $this->makeOrder('delivered');
        app(EscrowService::class)->hold($order);

        $this->actingAs($this->buyer)
            ->post(route('workspace.approve', $order))
            ->assertRedirect(route('buyer.reviews.create', $order->id));

        $this->assertSame('completed', $order->fresh()->status);
        $this->assertEquals(90000, (float) $this->profile()->wallet_balance);
        $this->assertEquals(0, (float) $this->profile()->escrow_balance);
    }

    public function test_admin_dispute_refund_moves_escrow_back(): void
    {
        $order = $this->makeOrder('disputed');
        app(EscrowService::class)->hold($order);
        $dispute = Dispute::create(['order_id' => $order->id, 'raised_by' => $this->buyer->id, 'reason' => 'Hasil tidak sesuai brief.']);

        $this->actingAs($this->admin)
            ->patch(route('admin.disputes.resolve', $dispute), ['resolution' => 'refund'])
            ->assertRedirect(route('admin.disputes.index'));

        $this->assertSame('resolved_buyer_refund', $dispute->fresh()->status);
        $this->assertSame('cancelled', $order->fresh()->status);
        $this->assertEquals(0, (float) $this->profile()->escrow_balance);
    }

    public function test_admin_dispute_release_pays_seller(): void
    {
        $order = $this->makeOrder('disputed');
        app(EscrowService::class)->hold($order);
        $dispute = Dispute::create(['order_id' => $order->id, 'raised_by' => $this->buyer->id, 'reason' => 'Pembeli tidak merespons.']);

        $this->actingAs($this->admin)
            ->patch(route('admin.disputes.resolve', $dispute), ['resolution' => 'release'])
            ->assertRedirect(route('admin.disputes.index'));

        $this->assertSame('resolved_seller_payout', $dispute->fresh()->status);
        $this->assertSame('completed', $order->fresh()->status);
        $this->assertEquals(90000, (float) $this->profile()->wallet_balance);
    }
}
