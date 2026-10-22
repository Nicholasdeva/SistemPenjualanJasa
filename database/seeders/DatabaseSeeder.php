<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Order;
use App\Models\SellerProfile;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Akun uji (password semuanya: "password")
        User::create([
            'name' => 'Admin', 'email' => 'admin@marketplace.test',
            'password' => 'password', 'role' => 'admin',
        ]);

        $buyer = User::create([
            'name' => 'Budi Pembeli', 'email' => 'buyer@marketplace.test',
            'password' => 'password', 'role' => 'buyer',
        ]);

        $seller = User::create([
            'name' => 'Sinta Penjual', 'email' => 'seller@marketplace.test',
            'password' => 'password', 'role' => 'seller',
        ]);

        SellerProfile::create([
            'user_id'        => $seller->id,
            'ktp_number'     => '1871010101010001',
            'bank_name'      => 'BCA',
            'bank_account'   => '1234567890',
            'wallet_balance' => 150000,
            'escrow_balance' => 50000,
            'is_verified'    => true,
        ]);

        // Kategori
        $categories = [];
        foreach (['Desain', 'Pemrograman', 'Penulisan', 'Penerjemahan', 'Video'] as $name) {
            $categories[$name] = Category::create(['name' => $name, 'slug' => Str::slug($name)]);
        }

        // Contoh jasa + 3 paket
        $service = Service::create([
            'seller_id'   => $seller->id,
            'category_id' => $categories['Desain']->id,
            'title'       => 'Desain Logo Profesional',
            'description' => 'Pembuatan logo untuk brand atau usaha Anda, lengkap dengan file sumber.',
            'status'      => 'published',
        ]);

        $basic = $service->packages()->create([
            'tier_name' => 'Basic', 'price' => 150000, 'delivery_days' => 3, 'revision_limit' => 1,
        ]);
        $service->packages()->create([
            'tier_name' => 'Standard', 'price' => 300000, 'delivery_days' => 5, 'revision_limit' => 3,
        ]);
        $service->packages()->create([
            'tier_name' => 'Premium', 'price' => 500000, 'delivery_days' => 7, 'revision_limit' => 5,
        ]);

        // Contoh pesanan agar halaman "Pesanan Masuk" tidak kosong
        Order::create([
            'invoice_no'         => 'INV-20261022-0001',
            'buyer_id'           => $buyer->id,
            'service_package_id' => $basic->id,
            'status'             => 'paid',
            'project_brief'      => 'Logo untuk toko kopi bernama "Kopi Senja", nuansa hangat.',
            'total_price'        => 150000,
            'platform_fee'       => 15000,
            'deadline_at'        => now()->addDays(3),
        ]);
    }
}