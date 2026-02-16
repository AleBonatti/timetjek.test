<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'personnummer' => $this->generatePersonnummer(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Generate a random valid Swedish personnummer.
     */
    private function generatePersonnummer(): string
    {
        $year = fake()->numberBetween(1950, 2005);
        $month = str_pad(fake()->numberBetween(1, 12), 2, '0', STR_PAD_LEFT);
        $day = str_pad(fake()->numberBetween(1, 28), 2, '0', STR_PAD_LEFT);
        $sequence = str_pad(fake()->numberBetween(0, 999), 3, '0', STR_PAD_LEFT);
        $gender = fake()->numberBetween(0, 9);

        return $year . $month . $day . '-' . $sequence . $gender;
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
