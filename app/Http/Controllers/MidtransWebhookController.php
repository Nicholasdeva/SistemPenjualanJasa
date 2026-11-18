<?php

namespace App\Http\Controllers;

use App\Services\MidtransPaymentService;
use Illuminate\Http\Request;

class MidtransWebhookController extends Controller
{
    public function handle(Request $request, MidtransPaymentService $midtrans)
    {
        $result = $midtrans->handleNotification($request->all());

        return match ($result) {
            'invalid_signature' => response()->json(['message' => 'Signature tidak valid.'], 403),
            'not_found'         => response()->json(['message' => 'Transaksi tidak ditemukan.'], 404),
            'amount_mismatch'   => response()->json(['message' => 'Nominal tidak sesuai.'], 422),
            default             => response()->json(['message' => 'OK']),
        };
    }
}