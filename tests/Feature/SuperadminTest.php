<?php
namespace Tests\Feature;

use App\Models\SuperAdmin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperadminTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_createsuperadmin(): void
    {
        $response = $this->postJson('/api/admin/register', [
            "firstname"             => "selam2",
            "lastname"              => "mekonnen2",
            "email"                 => "selam@gmail.com",
            "phone"                 => "251905126321",
            "password"              => "password",
            "password_confirmation" => "password",
            "role"                  => "superadmin",
        ]);

        $response->assertStatus(201);

        $response->assertHeader('Content-Type', 'application/json');

        $response->assertJson([
            "message" => "SuperAdmin registered successfully",
            "data"    => [
                "firstname" => "selam2",
                "lastname"  => "mekonnen2",
                "email"     => "selam@gmail.com",
                "phone"     => "251905126321",
                "role"      => "superadmin",
            ],
        ]);

    }

    public function test_superadminlogin(): string
    {

        $user = Superadmin::factory()->create([
            "firstname" => "selam2",
            "lastname"  => "mekonnen2",
            "email"     => "selam@gmail.com",
            "phone"     => "251905126321",
            "password"  => bcrypt('password'),
            "role"      => "superadmin",
        ]);

        $response = $this->postJson('/api/admin/login', [
            "email"    => "selam@gmail.com",
            "password" => "password",
            "role"     => "superadmin",

        ]);
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');

        $response->assertJsonStructure([
            "message",
            "token",
            "data" => [
                "id",
                "firstname",
                "lastname",
                "phone",
                "email",
                "role",
            ],
        ]);

        return $response->json('token');
    }

    public function test_updatesuperadmin(): void
    {
        $user = Superadmin::factory()->create([
            "firstname" => "selam2",
            "lastname"  => "mekonnen2",
            "email"     => "selam2@gmail.com",
            "phone"     => "251905126323",
            "password"  => bcrypt('password'),
            "role"      => "superadmin",
        ]);
        $token    = $this->test_superadminlogin();
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept'        => 'application/json',
        ])->get('/api/admin/me');

        $response = $this->putJson('api/admin/update', [
            "firstname" => "selam2updated",
            "lastname"  => "mekonnen2updated",
            "email"     => "selam3@gmail.com",
            "phone"     => "251905126322",
            "role"      => "superadmin",
        ]);

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure([
            "message",
            "data" => [
                "id",
                "firstname",
                "lastname",
                "phone",
                "email",
                "role",

            ]]);
    }

}
