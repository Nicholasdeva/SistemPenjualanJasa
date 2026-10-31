@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Detail Sengketa #{{ $dispute->id }}</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('admin.disputes.index') }}">Dispute Center</a></li>
        <li class="breadcrumb-item active">Detail Sengketa</li>
    </ol>

    <div class="card mb-4">
        <div class="card-body">
            <h5>Informasi Sengketa</h5>
            <hr>
            <p><strong>ID Pesanan:</strong> #{{ $dispute->order_id }}</p>
            <p><strong>Status:</strong> {{ ucfirst($dispute->status) }}</p>
            <p><strong>Alasan:</strong> {{ $dispute->reason }}</p>
            
            @if($dispute->status == 'open')
            <form action="{{ route('admin.disputes.resolve', $dispute->id) }}" method="POST" class="mt-4">
                @csrf
                @method('PATCH')
                <div class="mb-3">
                    <label class="form-label font-weight-bold">Putusan Admin:</label>
                    <select name="resolution" class="form-select" required>
                        <option value="refund">Refund ke Pembeli</option>
                        <option value="release">Cairkan ke Penjual</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-danger">Putuskan Sengketa</button>
            </form>
            @else
            <div class="alert alert-info mt-3">
                <strong>Keputusan:</strong> {{ $dispute->resolution == 'refund' ? 'Refund ke Pembeli' : 'Cairkan ke Penjual' }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection