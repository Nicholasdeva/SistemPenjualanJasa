# Marketplace Penjualan Jasa — Proyek UTS PWL

Aplikasi web Marketplace Penjualan Jasa berbasis **Laravel 12** dan **PostgreSQL** dengan sistem pembayaran **Escrow (Rekening Bersama)** dan **Workspace Kolaborasi**.

## Anggota Tim & Pembagian Peran
- **Faqih**: Setup, Database & Migration PostgreSQL, Model Eloquent, Seeder, dan Area Penjual (`/seller/*`).
- **Nico**: Autentikasi (`login`, `register`, `forgot-password`) dan Halaman Publik (`landing`, `catalog`, `service-detail`).
- **Annis**: Middleware (`role`), Otorisasi/Policy, Routing, dan Area Admin (`/admin/*`).
- **Fiki**: Layout Master (`layouts/app`), Komponen Blade (`components/*`), Halaman Error (`403`, `404`, `500`), Area Pembeli (`/buyer/*`), dan Workspace Pesanan (`/workspace/*`).

---

## Standar UI & Framework CSS (Dikunci Bersama Tim)
- **Framework CSS**: **Bootstrap 5.3.3** + **Bootstrap Icons 1.11.3** (dimuat via CDN di `resources/views/layouts/app.blade.php`).
- **Aturan Penting**: Jangan mencampur class Tailwind CSS ke dalam file Blade. Gunakan utility class Bootstrap 5 dan komponen Blade di bawah ini agar seluruh halaman konsisten.

---

## Panduan Pemakaian Layout Master (`layouts/app.blade.php`)

Semua halaman wajib mewarisi (`@extends`) layout master `layouts.app`. Layout ini otomatis menampilkan **Navbar** dan **Sidebar** yang menyesuaikan peran pengguna yang sedang login (`buyer`, `seller`, `admin`), **Area Pesan Flash**, serta **Footer**.

```blade
@extends('layouts.app')

@section('title', 'Judul Halaman')

{{-- Opsional: sembunyikan sidebar pada halaman publik/auth/error --}}
{{-- @section('hide_sidebar', true) --}}

@section('content')
    <h1 class="h3 fw-bold mb-4">Judul Konten</h1>
    {{-- Isi halaman di sini --}}
@endsection
```

### Menampilkan Pesan Flash dari Controller
Layout master otomatis merender pesan flash dari session berikut tanpa perlu menulis ulang markup alert di tiap view:
- `->with('status', 'Pesan berhasil...')` atau `->with('success', '...')` (Alert Hijau)
- `->with('error', 'Pesan gagal...')` (Alert Merah)
- `->with('warning', 'Pesan peringatan...')` (Alert Kuning)
- `->with('info', 'Pesan informasi...')` (Alert Biru)

---

## Panduan Pemakaian Komponen Blade (`resources/views/components/`)

Hanya **Fiki** yang memelihara file di `resources/views/layouts/` dan `resources/views/components/`. Anggota tim cukup memanggil tag `<x-...>` berikut di view masing-masing:

### 1. Kartu (`<x-card>`)
Membungkus konten dalam kartu Bootstrap dengan header, ikon, subtitle, action button, dan footer opsional.
```blade
<x-card title="Daftar Pesanan" subtitle="Ringkasan transaksi terbaru" icon="bi-bag-check">
    <x-slot:headerAction>
        <x-button href="/buyer/orders" size="sm" variant="outline-primary">Lihat Semua</x-button>
    </x-slot:headerAction>

    <p class="mb-0">Isi body kartu di sini.</p>

    <x-slot:footer>
        <span class="small text-muted">Diperbarui hari ini</span>
    </x-slot:footer>
</x-card>
```

### 2. Tombol (`<x-button>`)
Otomatis merender `<a class="btn ...">` bila diberi prop `href`, atau `<button class="btn ...">` bila tanpa `href`.
```blade
{{-- Tombol Link --}}
<x-button :href="route('buyer.orders.index')" variant="primary" size="sm" icon="bi-eye">
    Detail Pesanan
</x-button>

{{-- Tombol Submit Form --}}
<x-button type="submit" variant="success" icon="bi-check-lg">
    Simpan Data
</x-button>
```

### 3. Alert (`<x-alert>`)
Props: `type` (`success`, `danger`/`error`, `warning`, `info`), `title` (opsional), `dismissible` (`true`/`false`).
```blade
<x-alert type="warning" title="Perhatian">
    Silakan lengkapi nomor KTP dan rekening bank Anda sebelum menarik saldo.
</x-alert>
```

### 4. Badge Status Pesanan (`<x-status-badge>`)
Mendukung seluruh siklus status pesanan (`pending`, `pending_payment`, `paid`, `in_progress`, `delivered`, `revision`, `completed`, `disputed`, `cancelled`) serta status layanan/penarikan (`published`, `draft`, `approved`, `rejected`).
```blade
<x-status-badge :status="$order->status" />
{{-- Contoh manual: --}}
<x-status-badge status="in_progress" />
<x-status-badge status="delivered" />
<x-status-badge status="completed" />
<x-status-badge status="disputed" />
```

### 5. Modal Konfirmasi (`<x-confirm-modal>`)
Digunakan untuk aksi penting (hapus jasa, setujui hasil kerja, minta revisi, ajukan sengketa, putuskan sengketa).
```blade
{{-- Tombol Pemicu Modal --}}
<button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#hapusJasaModal">
    Hapus Jasa
</button>

{{-- Komponen Modal --}}
<x-confirm-modal
    id="hapusJasaModal"
    title="Hapus Layanan Jasa?"
    message="Data jasa yang dihapus tidak dapat dikembalikan."
    :action="route('seller.services.destroy', $service->id)"
    method="DELETE"
    confirmText="Ya, Hapus"
    variant="danger"
/>
```

### 6. Bintang Rating (`<x-star-rating>`)
Memiliki 2 mode: **Tampilan (read-only)** untuk kartu katalog/ulasan, dan **Interaktif (`interactive="true"`)** untuk form input ulasan.
```blade
{{-- Mode Tampilan (Read-Only) --}}
<x-star-rating :rating="4.8" :showValue="true" :count="24" />

{{-- Mode Input Form Ulasan (1 sampai 5 Bintang) --}}
<x-star-rating :interactive="true" name="rating" :rating="old('rating', 5)" />
```

### 7. Tampilan Data Kosong (`<x-empty-state>`)
Ditampilkan saat tabel atau daftar belum memiliki data.
```blade
<x-empty-state
    title="Belum Ada Pesanan"
    description="Anda belum memiliki pesanan aktif saat ini."
    icon="bi-inbox"
    actionText="Cari Jasa di Katalog"
    :actionUrl="url('/')"
/>
```

---

## Daftar Halaman Area Pembeli & Workspace (Fiki — Tahap 1)

| Halaman | File View | Route Name | URL |
| :--- | :--- | :--- | :--- |
| Dashboard Pembeli | `resources/views/buyer/dashboard.blade.php` | `buyer.dashboard` | `/buyer/dashboard` |
| Pesanan Saya | `resources/views/buyer/orders/index.blade.php` | `buyer.orders.index` | `/buyer/orders` |
| Checkout & Pembayaran | `resources/views/buyer/checkout.blade.php` | `buyer.checkout` | `/buyer/checkout/{package?}` |
| Workspace Pesanan | `resources/views/shared/workspace/show.blade.php` | `workspace.show` | `/workspace/{order?}` |
| Form Ulasan | `resources/views/buyer/reviews/create.blade.php` | `buyer.reviews.create` | `/buyer/orders/{order?}/review` |
| Error 403, 404, 500 | `resources/views/errors/{403,404,500}.blade.php` | Otomatis Laravel | Otomatis saat `abort()` |

---

## Akun Uji Coba Seeder (`php artisan migrate:fresh --seed`)

| Peran | Email | Password |
| :--- | :--- | :--- |
| **Pembeli (Buyer)** | `buyer@marketplace.test` | `password` |
| **Penjual (Seller)** | `seller@marketplace.test` | `password` |
| **Admin** | `admin@marketplace.test` | `password` |
