<?php

namespace App\Services;

use App\Models\SellerProfile;
use App\Models\User;
use App\Models\Withdrawal;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Penarikan saldo wallet penjual.
 *
 * Saldo dipotong saat permintaan diajukan (bukan saat disetujui), supaya penjual tidak bisa
 * mengajukan beberapa penarikan yang totalnya melebihi saldo. Jika admin menolak, saldo dikembalikan.
 */
class WithdrawalService
{
    public const MIN_AMOUNT = 10000;

    /**
     * Ajukan penarikan: cek saldo & rekening, potong wallet_balance, catat permintaan pending.
     *
     * @throws ValidationException jika rekening belum diisi atau saldo tidak cukup
     */
    public function request(User $seller, float $amount): Withdrawal
    {
        return DB::transaction(function () use ($seller, $amount) {
            $profile = SellerProfile::where('user_id', $seller->id)->lockForUpdate()->first();

            if (! $profile || blank($profile->bank_account)) {
                throw ValidationException::withMessages([
                    'amount' => 'Lengkapi nomor rekening di Profil Penjual terlebih dahulu.',
                ]);
            }

            // Rekening yang baru diubah harus diverifikasi ulang admin sebelum bisa menerima dana.
            if (! $profile->is_verified) {
                throw ValidationException::withMessages([
                    'amount' => 'Akun penjual belum diverifikasi admin, penarikan belum bisa diajukan.',
                ]);
            }

            if ($amount < self::MIN_AMOUNT) {
                throw ValidationException::withMessages(['amount' => 'Minimal penarikan Rp 10.000.']);
            }

            if (round($amount, 2) > round((float) $profile->wallet_balance, 2)) {
                throw ValidationException::withMessages(['amount' => 'Nominal melebihi saldo tersedia.']);
            }

            $profile->decrement('wallet_balance', $amount);

            return Withdrawal::create([
                'seller_id'    => $seller->id,
                'amount'       => $amount,
                'bank_account' => trim(($profile->bank_name ? $profile->bank_name . ' - ' : '') . $profile->bank_account),
                'status'       => 'pending',
            ]);
        });
    }

    /** Admin menyetujui: dana dianggap sudah ditransfer. Saldo sudah dipotong saat pengajuan. */
    public function approve(Withdrawal $withdrawal): Withdrawal
    {
        return DB::transaction(function () use ($withdrawal) {
            $locked = $this->lockPending($withdrawal);
            $locked->update(['status' => 'approved']);

            return $locked;
        });
    }

    /** Admin menolak: kembalikan nominal ke wallet penjual. */
    public function reject(Withdrawal $withdrawal): Withdrawal
    {
        return DB::transaction(function () use ($withdrawal) {
            $locked = $this->lockPending($withdrawal);

            SellerProfile::where('user_id', $locked->seller_id)
                ->lockForUpdate()
                ->firstOrFail()
                ->increment('wallet_balance', (float) $locked->amount);

            $locked->update(['status' => 'rejected']);

            return $locked;
        });
    }

    private function lockPending(Withdrawal $withdrawal): Withdrawal
    {
        $locked = Withdrawal::whereKey($withdrawal->id)->lockForUpdate()->firstOrFail();

        if ($locked->status !== 'pending') {
            throw new DomainException('Permintaan ini sudah diproses sebelumnya.');
        }

        return $locked;
    }
}
