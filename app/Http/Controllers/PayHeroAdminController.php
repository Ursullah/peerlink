<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\PayHeroService;

class PayHeroAdminController extends Controller
{
    public function status(Request $request, $transactionId, PayHeroService $payHero)
    {
        // Allow unauthenticated access only in local environment for quick debugging.
        if (! app()->environment('local') && ! auth()->check()) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $transaction = Transaction::find($transactionId);
        if (! $transaction) {
            return response()->json(['error' => 'Transaction not found'], 404);
        }

        if (! $transaction->payhero_transaction_id) {
            return response()->json(['error' => 'No PayHero identifier stored for this transaction'], 400);
        }

        $identifier = $transaction->payhero_transaction_id;
        $response = $payHero->fetchPaymentStatus($identifier);

        if (! $response) {
            return response()->json(['error' => 'No response from PayHero or not found'], 502);
        }

        return response()->json([
            'local_transaction' => $transaction->toArray(),
            'payhero' => $response->json(),
            'status_code' => $response->status(),
        ]);
    }
}
