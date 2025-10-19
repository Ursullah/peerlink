<?php

namespace Database\Factories;

use App\Models\Loan;
use App\Models\LoanRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Loan>
 */
class LoanFactory extends Factory
{
    protected $model = Loan::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $principalAmount = $this->faker->numberBetween(10000, 500000); // KES 100 - 5,000
        $interestRate = $this->faker->randomFloat(1, 10, 20);
        $interestAmount = (int) ($principalAmount * ($interestRate / 100));

        return [
            'loan_request_id' => LoanRequest::factory(),
            'lender_id' => User::factory()->create(['role' => 'lender']),
            'borrower_id' => User::factory()->create(['role' => 'borrower']),
            'principal_amount' => $principalAmount,
            'interest_rate' => $interestRate,
            'interest_amount' => $interestAmount,
            'total_repayable' => $principalAmount + $interestAmount,
            'amount_repaid' => 0,
            'status' => 'active',
            'funded_at' => now(),
        ];
    }

    /**
     * Create a repaid loan
     */
    public function repaid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'repaid',
            'amount_repaid' => $attributes['total_repayable'],
        ]);
    }

    /**
     * Create a partially repaid loan
     */
    public function partiallyRepaid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'amount_repaid' => (int) ($attributes['total_repayable'] * 0.5),
        ]);
    }
}
