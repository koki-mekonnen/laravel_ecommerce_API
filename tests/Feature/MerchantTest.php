<?php
namespace Tests\Feature;

use App\Models\Merchant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MerchantTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     */

    use RefreshDatabase;
    public function test_createmerchant(): void
    {
        $response = $this->postJson('api/merchant/register', [
            'firstname' => 'John',
            'lastname'  => 'Doe',
            'phone'     => '251909126327',
            'email'     => 'kohndoee@example.com',
            'password'  => 'password123',
            'license'   => 'ABC1234567890',
            'tinnumber' => '67890',
            'role'      => 'merchant',

        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'message',
            'data' => [
                'id',
                'firstname',
                'lastname',
                'phone',
                'email',
                'license',
                'tinnumber',
                'role',
                'created_at',
                'updated_at',
            ]]);
    }

    public function test_loginmerchant()
    {
        $merchant = Merchant::factory()->create([
            'firstname' => 'John',
            'lastname'  => 'Doe',
            'phone'     => '251909126328',
            'email'     => 'mohndoee@example.com',
            "password"  => bcrypt('password'),
            'license'   => 'ABC1234567890',
            'tinnumber' => '67890',
            'role'      => 'merchant',
        ]);

        $response = $this->postJson('/api/merchant/login', [
            'phone'    => '251909126328',
            'password' => 'password',
            'role'     => 'merchant',
        ]);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
            'token',
            'data' => [
                'id',
                'firstname',
                'lastname',
                'phone',
                'email',
                'license',
                'tinnumber',
                'role',
            ],
        ]);
        return $response->json('token');
    }

    public function test_getmerchant()
    {
        $token = $this->test_loginmerchant();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept'        => 'application/json']
        )->get('api/merchant/me');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'firstname',
                'lastname',
            ]]);

    }

    public function test_createmerchant_fails_with_missing_fields(): void
    {
        $response = $this->postJson('api/merchant/register', [
            // Missing required fields like 'firstname', 'lastname', 'email'
            // 'firstname' => 'John',
            // 'lastname'  => 'Doe',
            'phone'    => '251909126327',
            'password' => 'password123',
        ]);

        $response->assertStatus(422); // Validation error
        $response->assertJsonValidationErrors(['firstname', 'lastname', 'license', 'tinnumber']);
    }

    public function test_createmerchant_fails_with_invalid_email(): void
    {
        $response = $this->postJson('api/merchant/register', [
            'firstname' => 'John',
            'lastname'  => 'Doe',
            'phone'     => '251909126327',
            'email'     => 'invalidemail', // Invalid email format
            'password'  => 'password123',
            'license'   => 'ABC1234567890',
            'tinnumber' => '67890',
            'role'      => 'merchant',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

}
