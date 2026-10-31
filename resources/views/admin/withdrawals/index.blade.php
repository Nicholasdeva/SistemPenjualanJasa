@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Penarikan Dana</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Penarikan Dana</li>
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
                        <th>Penjual</th>
                        <th>Jumlah</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($withdrawals as $withdrawal)
                    <tr>
                        <td>{{ $withdrawal->id }}</td>
                        <td>{{ $withdrawal->user->name ?? '-' }}</td>
                        <td>Rp {{ number_format($withdrawal->amount, 0, ',', '.') }}</td>
                        <td>
                            <span class="badge bg-{{ $withdrawal->status == 'pending' ? 'warning' : 'success' }}">
                                {{ ucfirst($withdrawal->status) }}
                            </span>
                        </td>
                        <td>
                            @if($withdrawal->status == 'pending')
                            <form action="{{ route('admin.withdrawals.process', $withdrawal->id) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-sm btn-primary">Proses</button>
                            </form>
                            @else
                            <span class="text-muted">Selesai</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center">Belum ada permintaan penarikan dana.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection