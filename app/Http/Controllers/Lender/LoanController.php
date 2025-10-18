<?php

namespace App\Http\Controllers\Lender;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Models\LoanRequest;
use App\Models\Transaction;
use App\Notifications\LoanFundedNotification;
use App\Services\PlatformRevenueService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LoanController extends Controller
{
    /**
     * Display a listing of the active loan requests.
     */
    public function index()
    {
        // MODIFIED: Eager load relationships for efficiency
        $loanRequests = LoanRequest::with(['loans', 'borrower'])
            ->where('status', 'active') // Status for 'approved, ready for funding'
            ->latest()
            ->get();

        return view('lender.loans.index', compact('loanRequests'));
    }

    /**
     * Fund the specified loan request (partially or fully).
     */
    public function fund(Request $request, LoanRequest $loanRequest)
    {
        // --- NEW PARTIAL FUNDING LOGIC ---

        // 1. Validate the amount the lender wants to invest
        $validated = $request->validate([
            // Assuming amounts are submitted in KES, not cents
            'amount' => 'required|numeric|min:1',
        ]);
        $fundingAmountInCents = $validated['amount'] * 100;

        $lender = Auth::user();
        $lenderWallet = $lender->wallet;

        // 2. Check if the loan request is still active
        if ($loanRequest->status !== 'active') {
            return back()->with('error', 'This loan request is no longer active for funding.');
        }

        // 3. Calculate remaining amount needed
        $totalNeeded = $loanRequest->amount;
        $currentlyFunded = $loanRequest->loans()->sum('principal_amount');
        $remainingNeeded = $totalNeeded - $currentlyFunded;

        // 4. Check if the funding amount is valid
        if ($fundingAmountInCents > $remainingNeeded) {
            $maxAmountKES = number_format($remainingNeeded / 100, 2);

            return back()->with('error', "Your funding amount exceeds the remaining required amount of KES {$maxAmountKES}.");
        }

        // 5. Check if lender has enough funds
        if ($lenderWallet->balance < $fundingAmountInCents) {
            return back()->with('error', 'Your wallet balance is insufficient to fund this amount.');
        }

        // 6. Use a database transaction for safety
        DB::transaction(function () use ($loanRequest, $lender, $lenderWallet, $fundingAmountInCents, $remainingNeeded) {
            $borrower = $loanRequest->borrower;
            $borrowerWallet = $borrower->wallet;

            // Debit lender, credit borrower
            $lenderWallet->decrement('balance', $fundingAmountInCents);
            $borrowerWallet->increment('balance', $fundingAmountInCents);

            // Create the Loan record for this specific contribution
            $interestRatio = $fundingAmountInCents / $loanRequest->amount;
            $totalInterestForLoan = $loanRequest->amount * ($loanRequest->interest_rate / 100);
            $interestForThisLender = $totalInterestForLoan * $interestRatio;

            $loan = Loan::create([
                'loan_request_id' => $loanRequest->id,
                'borrower_id' => $borrower->id,
                'lender_id' => $lender->id,
                'principal_amount' => $fundingAmountInCents,
                'interest_amount' => $interestForThisLender,
                'total_repayable' => $fundingAmountInCents + $interestForThisLender,
                'due_date' => Carbon::now()->addDays($loanRequest->repayment_period),
                'status' => 'active',
            ]);

            // Record platform revenue from interest commission
            $revenueService = new PlatformRevenueService;
            $revenueService->recordInterestCommission($loan);

            // Log transactions
            Transaction::create(['user_id' => $lender->id, 'type' => 'loan_funding', 'amount' => -$fundingAmountInCents, 'status' => 'successful']);
            Transaction::create(['user_id' => $borrower->id, 'type' => 'deposit', 'amount' => $fundingAmountInCents, 'status' => 'successful']);

            // Check if the loan is now fully funded
            if ($fundingAmountInCents >= $remainingNeeded) {
                $loanRequest->update(['status' => 'funded']);
                // Notify borrower only when fully funded
                $loan->load('borrower');
                $borrower->notify(new LoanFundedNotification($loan));
            }
        });

        return redirect()->route('lender.loans.investments')->with('success', 'Loan funded successfully!');
    }

    /**
     * Display the loans this lender has funded.
     */
    public function investments()
    {
        // CONFIRMED: This query is correct.
        $myLoans = Loan::where('lender_id', Auth::id())
            ->with(['borrower', 'loanRequest']) // Added loanRequest for more details
            ->latest()
            ->get();

        return view('lender.investments', compact('myLoans'));
    }
}
