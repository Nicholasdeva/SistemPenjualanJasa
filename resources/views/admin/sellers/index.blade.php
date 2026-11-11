@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2 class="mb-4">Verifikasi Penjual</h2>

    <div class="card shadow-sm">
        <div class="card-body">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Penjual</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="5" class="text-center text-muted">Belum ada pengajuan verifikasi penjual.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection