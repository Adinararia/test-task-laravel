<?php

namespace Database\Factories;

use App\Models\Position\Position;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{

    protected $count = 45;
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;
    private array            $positionIds = [];


    /**
     * @return void
     */
    public function __invoke(): void
    {
        $this->positionIds = $this->getPositionIds();
    }

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'              => $this->faker->name(),
            'email'             => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password'          => Hash::make('password'),
            'remember_token'    => Str::random(10),
            'phone'             => $this->faker->unique()->phoneNumber,
            'position_id'       => $this->faker->randomElement($this->positionIds),
        ];
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

    /**
     * @return array
     */
    private function getPositionIds(): array
    {
        return Position::query()->select('id')->get()->pluck('id')->toArray();
    }
}
