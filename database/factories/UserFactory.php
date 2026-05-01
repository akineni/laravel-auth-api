<?php

namespace Database\Factories;

use App\Enums\SignupSourceEnum;
use App\Enums\UserStatusEnum;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'firstname'         => fake()->firstName(),
            'lastname'          => fake()->lastName(),
            'username'          => fake()->unique()->userName(),
            'email'             => fake()->unique()->safeEmail(),
            'password'          => 'T3st#Secure!XyZ9', // password is hashed in the model's $casts
            'email_verified_at' => now(),
            'status'            => UserStatusEnum::ACTIVE->value,
            'signup_source'     => SignupSourceEnum::SELF->value,
            'two_fa'            => false,
            'two_fa_method'     => 'default',
            'failed_logins'     => 0,
            'locked_until'      => null,
            'remember_token'    => Str::random(10),
        ];
    }

    public function pending(): static
    {
        return $this->state(['status' => UserStatusEnum::PENDING->value]);
    }

    public function inactive(): static
    {
        return $this->state(['status' => UserStatusEnum::INACTIVE->value]);
    }

    public function locked(): static
    {
        return $this->state([
            'failed_logins' => 5,
            'locked_until'  => now()->addMinutes(15),
        ]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(['email_verified_at' => null]);
    }

    public function withTwoFa(): static
    {
        return $this->state(['two_fa' => true]);
    }
}