<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TimeEntry>
 */
class TimeEntryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $clockIn = now()->setTime(9, 0);
        $clockOut = now()->setTime(17, 0);

        return [
            'user_id' => User::factory(),
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
            'clock_in_latitude' => null,
            'clock_in_longitude' => null,
            'clock_out_latitude' => null,
            'clock_out_longitude' => null,
            'notes' => null,
        ];
    }

    /**
     * State for an open (active) time entry with no clock_out.
     */
    public function open(): static
    {
        return $this->state(fn (array $attributes) => [
            'clock_out' => null,
        ]);
    }

    /**
     * State for a time entry with GPS coordinates.
     */
    public function withCoordinates(float $lat = 59.3293, float $lon = 18.0686): static
    {
        return $this->state(fn (array $attributes) => [
            'clock_in_latitude' => $lat,
            'clock_in_longitude' => $lon,
            'clock_out_latitude' => $lat,
            'clock_out_longitude' => $lon,
        ]);
    }
}
