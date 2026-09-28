<?php

namespace Database\Factories;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<SubscriptionPlan>
 */
class SubscriptionPlanFactory extends Factory
{
    protected $model = SubscriptionPlan::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => ucwords($name),
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
            'monthly_price' => fake()->randomFloat(2, 9, 99),
            'annual_price' => fake()->randomFloat(2, 89, 499),
            'daily_profile_views' => -1,
            'daily_interests' => -1,
            'advanced_filters' => true,
            'profile_boost' => false,
            'see_who_viewed' => true,
            'dedicated_advisor' => false,
            'badge_text' => fake()->optional()->word(),
            'features' => [
                'Unlimited profile views',
                'Priority verification',
                'Advanced search filters',
            ],
            'is_active' => true,
            'is_popular' => false,
            'sort_order' => 1,
        ];
    }
}
