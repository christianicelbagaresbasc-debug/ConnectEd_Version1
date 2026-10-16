<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
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
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => UserRole::STUDENT,
            'status' => UserStatus::ACTIVE,
            'must_change_password' => false,
            'approved_at' => now(),
            'remember_token' => Str::random(10),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => [
            'status' => UserStatus::PENDING,
            'approved_at' => null,
            'approved_by' => null,
        ]);
    }

    public function suspended(): static
    {
        return $this->state(fn () => [
            'status' => UserStatus::SUSPENDED,
        ]);
    }

    public function oc(): static
    {
        return $this->state(fn () => [
            'role' => UserRole::OC,
            'status' => UserStatus::ACTIVE,
        ]);
    }

    public function coc(): static
    {
        return $this->state(fn () => [
            'role' => UserRole::COC,
            'status' => UserStatus::ACTIVE,
        ]);
    }

    public function ua(): static
    {
        return $this->state(fn () => [
            'role' => UserRole::UA,
            'status' => UserStatus::ACTIVE,
        ]);
    }

    public function pc(): static
    {
        return $this->state(fn () => [
            'role' => UserRole::PC,
            'status' => UserStatus::ACTIVE,
        ]);
    }

    public function student(): static
    {
        return $this->state(fn () => [
            'role' => UserRole::STUDENT,
            'status' => UserStatus::ACTIVE,
        ]);
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
