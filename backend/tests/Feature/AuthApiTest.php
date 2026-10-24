<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_and_receive_token(): void
    {
        User::factory()->create(['email' => 'ghani@example.com', 'password' => 'secret123']);

        $this->postJson('/api/login', ['email' => 'ghani@example.com', 'password' => 'secret123'])
            ->assertOk()
            ->assertJsonStructure(['message', 'token', 'token_type', 'user' => ['id', 'name', 'email']]);
    }

    public function test_login_with_wrong_password_returns_422(): void
    {
        User::factory()->create(['email' => 'ghani@example.com', 'password' => 'secret123']);

        $this->postJson('/api/login', ['email' => 'ghani@example.com', 'password' => 'salah'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('email');
    }

    public function test_protected_endpoint_without_token_returns_401_json(): void
    {
        // Plain get() without an Accept header: the API must still answer JSON, not redirect.
        $this->get('/api/project-ideas')
            ->assertStatus(401)
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_logout_revokes_current_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)->postJson('/api/logout')->assertOk();

        $this->assertSame(0, PersonalAccessToken::count());
    }
}
