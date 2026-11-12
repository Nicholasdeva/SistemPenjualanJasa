<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Dispute;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Review;
use App\Models\SellerProfile;
use App\Models\Service;
use App\Models\User;
use App\Models\Withdrawal;
use App\Models\Workspace;
use App\Models\WorkspaceMessage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Data dummy untuk demo. Semua akun memakai password: 123
 *
 * Jalankan: php artisan db:seed --class=DummyDataSeeder
 */
class DummyDataSeeder extends Seeder
{
    private const PASSWORD = '123';

    public function run(): void
    {
        if (User::where('email', 'rizky.design@gmail.com')->exists()) {
            $this->command?->warn('Data dummy sudah ada, seeder dilewati.');
            return;
        }

        DB::transaction(function () {
            // ---------- Akun ----------
            $admin = User::updateOrCreate(
                ['email' => 'admin@gmail.com'],
                ['name' => 'Admin Marketplace', 'password' => self::PASSWORD, 'role' => 'admin', 'email_verified_at' => now()]
            );

            $sellerData = [
                ['Rizky Pratama',  'rizky.design@gmail.com',  'BCA',     '5271029384', true],
                ['Dewi Lestari',   'dewi.writer@gmail.com',   'Mandiri', '1370019283745', true],
                ['Andi Saputra',   'andi.dev@gmail.com',      'BRI',     '002301029384501', true],
                ['Nadia Putri',    'nadia.video@gmail.com',   'BNI',     '0827364519', true],
                ['Fajar Hidayat',  'fajar.marketing@gmail.com', 'BCA',   '8830192746', false],
            ];

            $sellers = [];
            foreach ($sellerData as $i => [$name, $email, $bank, $account, $verified]) {
                $user = User::create([
                    'name' => $name, 'email' => $email, 'password' => self::PASSWORD,
                    'role' => 'seller', 'email_verified_at' => now(),
                ]);
                SellerProfile::create([
                    'user_id'        => $user->id,
                    'ktp_number'     => '32710' . str_pad((string) (1000000000 + $i * 7919), 11, '0', STR_PAD_LEFT),
                    'bank_name'      => $bank,
                    'bank_account'   => $account,
                    'wallet_balance' => 0,
                    'escrow_balance' => 0,
                    'is_verified'    => $verified,
                ]);
                $sellers[$email] = $user;
            }

            $buyerData = [
                ['Budi Santoso',   'budi.santoso@gmail.com'],
                ['Siti Rahmawati', 'siti.rahma@gmail.com'],
                ['Agus Wijaya',    'agus.wijaya@gmail.com'],
                ['Maya Anggraini', 'maya.anggraini@gmail.com'],
            ];

            $buyers = [];
            foreach ($buyerData as [$name, $email]) {
                $buyers[] = User::create([
                    'name' => $name, 'email' => $email, 'password' => self::PASSWORD,
                    'role' => 'buyer', 'email_verified_at' => now(),
                ]);
            }

            // ---------- Kategori ----------
            $cat = [];
            foreach (['Desain', 'Pemrograman', 'Penulisan', 'Penerjemahan', 'Video', 'Pemasaran Digital', 'Data & Analitik', 'Musik & Audio'] as $name) {
                $cat[$name] = Category::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name]);
            }

            // ---------- Jasa + paket ----------
            // [seller email, kategori, judul, deskripsi, harga basic, status]
            $serviceData = [
                ['rizky.design@gmail.com', 'Desain', 'Desain Logo Minimalis untuk UMKM', 'Logo minimalis dan mudah diingat untuk usaha kamu. Termasuk 3 konsep awal, file PNG, JPG, dan file sumber AI/SVG.', 150000, 'published'],
                ['rizky.design@gmail.com', 'Desain', 'Desain Feed Instagram 9 Post', 'Desain konten Instagram yang konsisten dengan identitas brand. Cocok untuk toko online dan bisnis kuliner.', 120000, 'published'],
                ['rizky.design@gmail.com', 'Desain', 'Desain Kemasan Produk (Packaging)', 'Desain label dan kemasan produk siap cetak, lengkap dengan mockup 3D.', 250000, 'published'],
                ['dewi.writer@gmail.com', 'Penulisan', 'Penulisan Artikel SEO 1000 Kata', 'Artikel original yang dioptimasi untuk SEO, riset kata kunci, dan bebas plagiarisme.', 75000, 'published'],
                ['dewi.writer@gmail.com', 'Penulisan', 'Copywriting Landing Page', 'Teks landing page yang persuasif untuk meningkatkan konversi penjualan produk atau jasa.', 200000, 'published'],
                ['dewi.writer@gmail.com', 'Penerjemahan', 'Terjemahan Inggris ↔ Indonesia', 'Terjemahan dokumen umum, jurnal, atau website. Hasil natural dan sesuai konteks.', 50000, 'published'],
                ['andi.dev@gmail.com', 'Pemrograman', 'Pembuatan Website Company Profile Laravel', 'Website company profile responsif dengan Laravel, termasuk halaman admin untuk kelola konten.', 1500000, 'published'],
                ['andi.dev@gmail.com', 'Pemrograman', 'Perbaikan Bug Website PHP / Laravel', 'Perbaikan error, bug, dan optimasi performa website berbasis PHP atau Laravel.', 100000, 'published'],
                ['andi.dev@gmail.com', 'Data & Analitik', 'Dashboard Excel & Google Sheets', 'Pembuatan dashboard laporan otomatis dengan rumus, pivot, dan grafik interaktif.', 175000, 'published'],
                ['nadia.video@gmail.com', 'Video', 'Edit Video YouTube & Reels', 'Editing video dengan transisi, subtitle, color grading, dan musik bebas hak cipta.', 100000, 'published'],
                ['nadia.video@gmail.com', 'Musik & Audio', 'Voice Over Bahasa Indonesia', 'Rekaman voice over untuk iklan, video edukasi, atau presentasi dengan kualitas studio.', 80000, 'published'],
                ['nadia.video@gmail.com', 'Video', 'Animasi Motion Graphic 30 Detik', 'Video animasi explainer untuk promosi produk atau layanan.', 450000, 'draft'],
                ['fajar.marketing@gmail.com', 'Pemasaran Digital', 'Kelola Iklan Meta Ads 1 Bulan', 'Setup dan optimasi iklan Facebook & Instagram, termasuk laporan performa mingguan.', 500000, 'published'],
            ];

            $packages = [];
            foreach ($serviceData as [$sellerEmail, $category, $title, $desc, $price, $status]) {
                $service = Service::create([
                    'seller_id'   => $sellers[$sellerEmail]->id,
                    'category_id' => $cat[$category]->id,
                    'title'       => $title,
                    'description' => $desc,
                    'status'      => $status,
                ]);

                $packages[$title] = [
                    'Basic' => $service->packages()->create(['tier_name' => 'Basic', 'price' => $price, 'delivery_days' => 3, 'revision_limit' => 1]),
                    'Standard' => $service->packages()->create(['tier_name' => 'Standard', 'price' => round($price * 1.8, -3), 'delivery_days' => 5, 'revision_limit' => 3]),
                    'Premium' => $service->packages()->create(['tier_name' => 'Premium', 'price' => round($price * 3, -3), 'delivery_days' => 7, 'revision_limit' => 5]),
                ];
            }

            // ---------- Pesanan ----------
            // [buyer idx, judul jasa, paket, status, hari lalu, brief, rating?, komentar?]
            $orderData = [
                [0, 'Desain Logo Minimalis untuk UMKM', 'Standard', 'completed', 30, 'Logo untuk kedai kopi "Kopi Senja". Nuansa hangat, warna cokelat dan oranye.', 5, 'Hasilnya bagus banget, revisinya cepat. Recommended!'],
                [1, 'Desain Logo Minimalis untuk UMKM', 'Basic', 'completed', 22, 'Logo untuk usaha laundry kiloan "Bersih Wangi".', 4, 'Sesuai brief, komunikasinya enak.'],
                [2, 'Penulisan Artikel SEO 1000 Kata', 'Standard', 'completed', 18, '5 artikel tentang tips merawat tanaman hias untuk blog toko online.', 5, 'Artikelnya rapi dan sudah masuk halaman 1 Google untuk beberapa keyword.'],
                [3, 'Pembuatan Website Company Profile Laravel', 'Premium', 'completed', 40, 'Website company profile untuk CV konstruksi, 6 halaman + admin panel.', 5, 'Profesional, dokumentasinya lengkap. Mantap.'],
                [0, 'Edit Video YouTube & Reels', 'Basic', 'completed', 12, 'Edit 1 video vlog kuliner durasi 10 menit.', 4, 'Editannya rapi, cuma subtitle ada sedikit typo tapi langsung diperbaiki.'],
                [1, 'Dashboard Excel & Google Sheets', 'Standard', 'completed', 9, 'Dashboard laporan penjualan bulanan dari data Shopee dan Tokopedia.', 5, 'Sangat membantu, sekarang laporan tinggal klik.'],
                [2, 'Desain Feed Instagram 9 Post', 'Basic', 'in_progress', 2, 'Feed untuk launching produk skincare lokal, warna pastel.', null, null],
                [3, 'Copywriting Landing Page', 'Standard', 'in_progress', 3, 'Landing page untuk kelas online belajar desain.', null, null],
                [0, 'Perbaikan Bug Website PHP / Laravel', 'Basic', 'delivered', 4, 'Form kontak di website error 500 setelah update PHP 8.', null, null],
                [1, 'Voice Over Bahasa Indonesia', 'Standard', 'revision', 5, 'Voice over iklan produk minuman, durasi 30 detik, suara wanita ceria.', null, null],
                [2, 'Desain Kemasan Produk (Packaging)', 'Standard', 'paid', 1, 'Kemasan keripik pisang ukuran 100 gram.', null, null],
                [3, 'Terjemahan Inggris ↔ Indonesia', 'Basic', 'pending', 0, 'Terjemahan abstrak jurnal 500 kata ke bahasa Inggris.', null, null],
                [0, 'Kelola Iklan Meta Ads 1 Bulan', 'Basic', 'pending', 0, 'Iklan untuk toko baju muslim online.', null, null],
                [1, 'Edit Video YouTube & Reels', 'Standard', 'disputed', 10, 'Edit 3 video reels untuk promosi katering.', null, null],
                [2, 'Penulisan Artikel SEO 1000 Kata', 'Basic', 'cancelled', 15, 'Artikel tentang wisata Bandung.', null, null],
            ];

            $wallet = [];
            $escrow = [];

            foreach ($orderData as $n => [$buyerIdx, $title, $tier, $status, $daysAgo, $brief, $rating, $comment]) {
                $package = $packages[$title][$tier];
                $buyer = $buyers[$buyerIdx];
                $seller = $package->service->seller;
                $created = now()->subDays($daysAgo)->subHours(rand(1, 10));
                $total = (float) $package->price;
                $fee = round($total * 0.1);

                $order = Order::create([
                    'invoice_no'         => 'INV-' . $created->format('Ymd') . '-' . str_pad((string) ($n + 101), 4, '0', STR_PAD_LEFT),
                    'buyer_id'           => $buyer->id,
                    'service_package_id' => $package->id,
                    'status'             => $status,
                    'project_brief'      => $brief,
                    'total_price'        => $total,
                    'platform_fee'       => $fee,
                    'deadline_at'        => $created->copy()->addDays($package->delivery_days),
                ]);
                $order->forceFill(['created_at' => $created, 'updated_at' => $created])->saveQuietly();

                if ($status === 'pending') {
                    Payment::create(['order_id' => $order->id, 'payment_method' => 'bank_transfer', 'amount' => $total, 'status' => 'pending']);
                    continue;
                }

                Payment::create([
                    'order_id'       => $order->id,
                    'payment_method' => ['bank_transfer', 'e_wallet', 'virtual_account'][$n % 3],
                    'amount'         => $total,
                    'status'         => $status === 'cancelled' ? 'refunded' : 'paid',
                    'paid_at'        => $created->copy()->addMinutes(20),
                ]);

                if ($status === 'cancelled') {
                    continue;
                }

                Workspace::create(['order_id' => $order->id]);
                $this->messages($order, $buyer, $seller, $status, $created);

                $net = $total - $fee;
                if ($status === 'completed') {
                    $wallet[$seller->id] = ($wallet[$seller->id] ?? 0) + $net;
                    Review::create([
                        'order_id'   => $order->id,
                        'service_id' => $package->service_id,
                        'rating'     => $rating,
                        'comment'    => $comment,
                        'created_at' => $created->copy()->addDays($package->delivery_days + 1),
                    ]);
                } else {
                    $escrow[$seller->id] = ($escrow[$seller->id] ?? 0) + $net;
                }

                if ($status === 'disputed') {
                    Dispute::create([
                        'order_id'  => $order->id,
                        'raised_by' => $buyer->id,
                        'reason'    => 'Sudah lewat deadline 4 hari dan hasil yang dikirim baru 1 dari 3 video. Penjual tidak membalas chat.',
                        'status'    => 'open',
                    ]);
                }
            }

            // ---------- Penarikan saldo ----------
            $withdrawals = [
                ['rizky.design@gmail.com', 200000, 'approved', 15],
                ['andi.dev@gmail.com', 1000000, 'approved', 20],
                ['andi.dev@gmail.com', 500000, 'pending', 1],
                ['dewi.writer@gmail.com', 150000, 'pending', 2],
                ['nadia.video@gmail.com', 50000, 'rejected', 6],
            ];
            foreach ($withdrawals as [$email, $amount, $status, $daysAgo]) {
                $seller = $sellers[$email];
                $w = Withdrawal::create([
                    'seller_id'    => $seller->id,
                    'amount'       => $amount,
                    'bank_account' => $seller->sellerProfile->bank_name . ' - ' . $seller->sellerProfile->bank_account,
                    'status'       => $status,
                ]);
                $w->forceFill(['created_at' => now()->subDays($daysAgo)])->saveQuietly();

                if ($status !== 'rejected') {
                    $wallet[$seller->id] = ($wallet[$seller->id] ?? 0) - $amount;
                }
            }

            foreach ($sellers as $seller) {
                $seller->sellerProfile->update([
                    'wallet_balance' => max(0, $wallet[$seller->id] ?? 0),
                    'escrow_balance' => $escrow[$seller->id] ?? 0,
                ]);
            }
        });

        $this->command?->info('Data dummy berhasil dibuat. Password semua akun: ' . self::PASSWORD);
    }

    private function messages(Order $order, User $buyer, User $seller, string $status, $start): void
    {
        $chat = [
            [$buyer, 'Halo kak, saya sudah order ya. Brief-nya sudah saya tulis di pesanan.', false],
            [$seller, 'Halo, terima kasih sudah order! Brief sudah saya baca, saya mulai kerjakan hari ini.', false],
            [$buyer, 'Siap kak, ditunggu progresnya.', false],
        ];

        if (in_array($status, ['delivered', 'revision', 'completed', 'disputed'])) {
            $chat[] = [$seller, 'Berikut hasil pekerjaannya, silakan dicek dulu ya.', true];
        }
        if ($status === 'revision') {
            $chat[] = [$buyer, 'Kak, tolong suaranya dibuat sedikit lebih semangat di bagian akhir ya.', false];
            $chat[] = [$seller, 'Baik kak, saya revisi dan kirim ulang besok.', false];
        }
        if ($status === 'completed') {
            $chat[] = [$buyer, 'Sudah oke kak, saya setujui ya. Terima kasih!', false];
        }
        if ($status === 'disputed') {
            $chat[] = [$buyer, 'Kak, ini baru 1 video, yang 2 lagi kapan? Sudah lewat deadline.', false];
        }

        foreach ($chat as $i => [$sender, $text, $isDelivery]) {
            $msg = WorkspaceMessage::create([
                'order_id'       => $order->id,
                'sender_id'      => $sender->id,
                'message'        => $text,
                'attachment_url' => $isDelivery ? 'deliveries/' . $order->invoice_no . '.zip' : null,
                'is_delivery'    => $isDelivery,
            ]);
            $at = $start->copy()->addHours(2 + $i * 6);
            $msg->forceFill(['created_at' => $at, 'updated_at' => $at])->saveQuietly();
        }
    }
}
