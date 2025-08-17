<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Event::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $eventTypes = ['personal', 'work', 'social', 'family'];
        $isRecurring = $this->faker->boolean(20); // 20% chance of recurring

        return [
            'user_id' => User::factory(),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->optional(0.7)->paragraph(),
            'date' => $this->faker->dateTimeBetween('now', '+2 months'),
            'location' => $this->faker->optional(0.6)->city(),
            'event_type' => $this->faker->randomElement($eventTypes),
            'is_recurring' => $isRecurring,
            'recurrence_pattern' => $isRecurring ? $this->faker->randomElement(['daily', 'weekly', 'monthly', 'yearly']) : null,
            'reminder' => $this->faker->optional(0.4)->randomElement([15, 30, 60, 120, 1440]), // minutes
        ];
    }

    /**
     * Indicate that the event is personal.
     */
    public function personal(): static
    {
        return $this->state(fn (array $attributes) => [
            'event_type' => 'personal',
        ]);
    }

    /**
     * Indicate that the event is work-related.
     */
    public function work(): static
    {
        return $this->state(fn (array $attributes) => [
            'event_type' => 'work',
        ]);
    }

    /**
     * Indicate that the event is social.
     */
    public function social(): static
    {
        return $this->state(fn (array $attributes) => [
            'event_type' => 'social',
        ]);
    }

    /**
     * Indicate that the event is family-related.
     */
    public function family(): static
    {
        return $this->state(fn (array $attributes) => [
            'event_type' => 'family',
        ]);
    }

    /**
     * Indicate that the event is today.
     */
    public function today(): static
    {
        return $this->state(fn (array $attributes) => [
            'date' => $this->faker->dateTimeBetween('today', 'today +23 hours'),
        ]);
    }

    /**
     * Indicate that the event is this week.
     */
    public function thisWeek(): static
    {
        return $this->state(fn (array $attributes) => [
            'date' => $this->faker->dateTimeBetween('now', '+1 week'),
        ]);
    }

    /**
     * Indicate that the event is this month.
     */
    public function thisMonth(): static
    {
        return $this->state(fn (array $attributes) => [
            'date' => $this->faker->dateTimeBetween('now', '+1 month'),
        ]);
    }
}
