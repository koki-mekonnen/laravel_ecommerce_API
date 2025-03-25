<?php
namespace Database\Factories;

use App\Models\SuperAdmin;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Superadmin>
 */
class SuperadminFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = SuperAdmin::class;

    public function definition(): array
    {
        return [
            'firstname' => $this->faker->firstName(),
            'lastname'  => $this->faker->lastName(),
            'email'     => $this->faker->unique()->safeEmail(),
            'phone'     => '251905126321',
            'password'  => bcrypt('password'), // Hash password
            'role'      => 'superadmin',
        ];
    }
}
