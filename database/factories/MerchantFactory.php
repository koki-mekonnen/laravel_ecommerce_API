<?php
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Merchant>
 */
class MerchantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'firstname' => $this->faker->firstName(),
            'lastname'  => $this->faker->lastName(),
            'phone'     => $this->faker->phoneNumber(),
            'email'     => $this->faker->unique()->safeEmail(),
            'password'  => bcrypt('secret'),
            'license'   => $this->faker->bothify(''),
            'tinnumber' => $this->faker->bothify(''),
            'role'      => 'merchant',
        ];
    }
}
