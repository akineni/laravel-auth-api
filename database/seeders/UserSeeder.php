<?php

namespace Database\Seeders;

use App\Enums\SignupSourceEnum;
use App\Enums\UserStatusEnum;
use App\Models\User;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        for ($i = 0; $i < 10; $i++) {
            User::create([
                'firstname' => $faker->firstName(),
                'lastname' => $faker->lastName(),
                'avatar' => null,
                'gender' => $faker->randomElement(['male', 'female', 'other']),
                'phone_number' => $faker->phoneNumber(),
                'postcode' => $faker->postcode(),
                'state' => $faker->state(),
                'country' => $faker->country(),
                'address' => $faker->address(),
                'email' => $faker->unique()->safeEmail(),
                'email_verified_at' => now(),
                'signup_source' => SignupSourceEnum::SEEDER->value,
                'password' => '12345678', // auto hashed from model
                'last_login' => now(),
                'status' => UserStatusEnum::ACTIVE->value,
                'two_fa' => $faker->boolean(30),
                'two_fa_secret' => null,
                'failed_logins' => 0,
                'locked_until' => null,
                'remember_token' => Str::random(10),
            ]);
        }

        $this->command->info('Seeded 10 users');
    }
}