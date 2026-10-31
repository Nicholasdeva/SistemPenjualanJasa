@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Dispute Center</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Dispute Center</li>
    </ol>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card mb-4">
        <div class="card-body">
            <table class="table table-bordered align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>ID Pesanan</th>
                        <th>Alasan Sengketa</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($disputes as $dispute)
                    <tr>
                        <td>{{ $dispute->id }}</td>
                        <td>#{{ $dispute->order_id }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($dispute->reason, 50) }}</td>
                        <td>
                            <span class="badge bg-{{ $dispute->status == 'open' ? 'danger' : 'secondary' }}">
                                {{ ucfirst($dispute->status) }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('admin.disputes.show', $dispute->id) }}" class="btn btn-sm btn-info text-white">Detail</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center">Tidak ada sengketa aktif.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection