<?php

namespace Database\Factories;

use App\Models\LoanRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LoanRequest>
 */
class LoanRequestFactory extends Factory
{
    protected $model = LoanRequest::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $amount = $this->faker->numberBetween(10000, 500000); // KES 100 - 5,000
        $collateralRatio = 0.20; // 20%
        $requiredCollateral = (int) ($amount * $collateralRatio);

        return [
            'user_id' => User::factory()->create(['role' => 'borrower']),
            'amount' => $amount,
            'repayment_period' => $this->faker->numberBetween(7, 30),
            'interest_rate' => 12.5,
            'reason' => $this->faker->sentence(),
            'collateral_locked' => $requiredCollateral,
            'status' => 'pending_approval',
        ];
    }

    /**
     * Create an approved loan request
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
        ]);
    }

    /**
     * Create a funded loan request
     */
    public function funded(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'funded',
        ]);
    }

    /**
     * Create a repaid loan request
     */
    public function repaid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'repaid',
        ]);
    }

    /**
     * Create a rejected loan request
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
        ]);
    }
}
