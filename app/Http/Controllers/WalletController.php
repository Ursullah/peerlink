<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http; // Import Laravel's HTTP client

class WalletController extends Controller
{
    /**
     * Show the form for depositing funds.
     */
    public function showDepositForm()
    {
        return view('wallet.deposit');
    }

    /**
     * Process the deposit request by initiating a PayHero STK Push.
     */
    public function processDeposit(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:10', // Minimum KES 10
        ]);

        $user = Auth::user();
        $amount = $validated['amount'];

        // === SIMULATED PAYHERO API CALL ===
        // Here, we would use Laravel's HTTP Client to talk to PayHero's API.
        // We are simulating a successful response for now.
        
        $payheroResponse = [
            'success' => true,
            'message' => 'STK Push initiated successfully. Please enter your PIN.',
            'transaction_id' => 'PH_'.uniqid()
        ];
        
        if ($payheroResponse['success']) {
            // If the API call is successful, we create a 'pending' transaction.
            // This will be updated to 'successful' by our webhook later.
            $user->transactions()->create([
                'type' => 'deposit',
                'amount' => $amount * 100, // Store in cents
                'status' => 'pending',
                'payhero_transaction_id' => $payheroResponse['transaction_id'],
            ]);

            return redirect()->route('dashboard')->with('success', $payheroResponse['message']);
        } else {
            return back()->with('error', 'Payment could not be initiated. Please try again.');
        }
    }
}