<?php
namespace Tests\Feature;

use App\Models\Merchant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    /**
     * A basic feature test example.
     */

    use RefreshDatabase;
    public function test_loginmerchant()
    {
        $merchant = Merchant::firstOrCreate(
            ['phone' => '251909126328'], // Check if a merchant with this phone exists
            [
                'firstname' => 'John',
                'lastname'  => 'Doe',
                'email'     => 'mohndoee@example.com',
                "password"  => bcrypt('password'),
                'license'   => 'ABC1234567890',
                'tinnumber' => '67890',
                'role'      => 'merchant',
            ]
        );

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

    public function test_createcategory()
    {
        $token    = $this->test_loginmerchant();
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept'        => 'application/json',
        ]);

        $response = $this->postJSON('api/merchant/category', [
            'category_name' => 'Electronics',
            'category_type' => 'Speaker',

        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            "success",
            "message",
            "data" => [
                "category_name",
                "category_type",
                "owner_id",
                "id",

            ],
        ]);
        return $response->Json('data.id');
    }

    public function test_getcategory()
    {
        $token = $this->test_loginmerchant();

        // Create category using API and get the ID
        $categoryId = $this->test_createcategory();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept'        => 'application/json',
        ])->get("api/merchant/category");

        $response->assertStatus(200);

        // Ensure the response contains the expected structure
        $response->assertJsonStructure([
            "success",
            "message",
            "data" => [
                '*' => [ // Ensures it checks an array of categories
                    "category_name",
                    "category_type",
                    "owner_id",
                    "id",
                ],
            ],
        ]);

        // ✅ Check if the response contains the created category
        $response->assertJsonFragment([
            "category_name" => "Electronics",
            "category_type" => "Speaker",
        ]);
    }

}
