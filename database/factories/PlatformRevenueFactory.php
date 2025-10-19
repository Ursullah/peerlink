<?php

namespace Database\Factories;

use App\Models\Loan;
use App\Models\PlatformRevenue;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PlatformRevenue>
 */
class PlatformRevenueFactory extends Factory
{
    protected $model = PlatformRevenue::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = ['interest_commission', 'transaction_fee', 'late_fee', 'processing_fee'];
        $type = $this->faker->randomElement($types);

        $amount = $this->faker->numberBetween(1000, 50000); // KES 10 - 500

        return [
            'type' => $type,
            'source_id' => $this->faker->numberBetween(1, 100),
            'source_type' => $this->faker->randomElement([Loan::class, Transaction::class]),
            'amount' => $amount,
            'percentage' => $this->faker->randomFloat(2, 1, 20),
            'description' => $this->faker->sentence(),
        ];
    }

    /**
     * Create interest commission revenue
     */
    public function interestCommission(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'interest_commission',
            'percentage' => 15.00,
            'description' => 'Interest commission from loan',
        ]);
    }

    /**
     * Create transaction fee revenue
     */
    public function transactionFee(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'transaction_fee',
            'percentage' => 2.00,
            'description' => 'Transaction processing fee',
        ]);
    }

    /**
     * Create late fee revenue
     */
    public function lateFee(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'late_fee',
            'percentage' => 5.00,
            'description' => 'Late payment fee',
        ]);
    }
}
