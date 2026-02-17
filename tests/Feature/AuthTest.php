<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Login
    // -------------------------------------------------------------------------

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'personnummer' => '19900101-1234',
            'password' => Hash::make('password'),
        ]);

        $response = $this->postJson('/api/login', [
            'personnummer' => '19900101-1234',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('user.id', $user->id)
            ->assertJsonPath('user.personnummer', '19900101-1234');
    }

    public function test_login_fails_with_wrong_password(): void
    {
        User::factory()->create([
            'personnummer' => '19900101-1234',
            'password' => Hash::make('password'),
        ]);

        $response = $this->postJson('/api/login', [
            'personnummer' => '19900101-1234',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['personnummer']);
    }

    public function test_login_fails_with_unknown_personnummer(): void
    {
        $response = $this->postJson('/api/login', [
            'personnummer' => '19001234-0000',
            'password' => 'password',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['personnummer']);
    }

    public function test_login_requires_personnummer_and_password(): void
    {
        $response = $this->postJson('/api/login', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['personnummer', 'password']);
    }

    // -------------------------------------------------------------------------
    // User profile
    // -------------------------------------------------------------------------

    public function test_authenticated_user_can_get_their_profile(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/user');

        $response->assertStatus(200)
            ->assertJsonPath('user.id', $user->id)
            ->assertJsonPath('user.email', $user->email);
    }

    public function test_unauthenticated_request_to_user_returns_401(): void
    {
        $response = $this->getJson('/api/user');

        $response->assertStatus(401);
    }

    // -------------------------------------------------------------------------
    // Update password
    // -------------------------------------------------------------------------

    public function test_user_can_update_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);

        $response = $this->actingAs($user)->putJson('/api/user/password', [
            'current_password' => 'old-password',
            'new_password' => 'new-password',
            'new_password_confirmation' => 'new-password',
        ]);

        $response->assertStatus(200);
        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
    }

    public function test_password_update_fails_with_wrong_current_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('correct-password'),
        ]);

        $response = $this->actingAs($user)->putJson('/api/user/password', [
            'current_password' => 'wrong-password',
            'new_password' => 'new-password',
            'new_password_confirmation' => 'new-password',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['current_password']);
    }

    public function test_password_update_requires_confirmation(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);

        $response = $this->actingAs($user)->putJson('/api/user/password', [
            'current_password' => 'old-password',
            'new_password' => 'new-password',
            // missing new_password_confirmation
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['new_password']);
    }

    public function test_password_update_requires_min_8_characters(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);

        $response = $this->actingAs($user)->putJson('/api/user/password', [
            'current_password' => 'old-password',
            'new_password' => 'short',
            'new_password_confirmation' => 'short',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['new_password']);
    }

    // -------------------------------------------------------------------------
    // Update profile
    // -------------------------------------------------------------------------

    public function test_user_can_update_profile(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->putJson('/api/user/profile', [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);
    }

    public function test_profile_update_fails_with_duplicate_email(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create(['email' => 'taken@example.com']);

        $response = $this->actingAs($user)->putJson('/api/user/profile', [
            'name' => 'Name',
            'email' => 'taken@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_profile_update_allows_keeping_own_email(): void
    {
        $user = User::factory()->create(['email' => 'myemail@example.com']);

        $response = $this->actingAs($user)->putJson('/api/user/profile', [
            'name' => 'Updated Name',
            'email' => 'myemail@example.com',
        ]);

        $response->assertStatus(200);
    }

    public function test_profile_update_requires_name_at_least_3_characters(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->putJson('/api/user/profile', [
            'name' => 'Ab',
            'email' => 'valid@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }
}
