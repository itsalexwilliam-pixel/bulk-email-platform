<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_users_index(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        User::factory()->create([
            'role' => 'operator',
        ]);

        $response = $this->actingAs($admin)->get(route('users.index'));

        $response->assertOk();
        $response->assertSee('Users');
    }

    public function test_admin_can_create_user(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $payload = [
            'name' => 'Test Manager',
            'email' => 'manager@example.com',
            'role' => 'manager',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->actingAs($admin)->post(route('users.store'), $payload);

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'name' => 'Test Manager',
            'email' => 'manager@example.com',
            'role' => 'manager',
        ]);
    }

    public function test_admin_can_edit_and_update_user(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $target = User::factory()->create([
            'role' => 'operator',
            'email' => 'old@example.com',
            'name' => 'Old Name',
        ]);

        $editResponse = $this->actingAs($admin)->get(route('users.edit', $target));
        $editResponse->assertOk();

        $updateResponse = $this->actingAs($admin)->put(route('users.update', $target), [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'role' => 'manager',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $updateResponse->assertRedirect(route('users.index'));
        $updateResponse->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $target->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'role' => 'manager',
        ]);
    }

    public function test_non_admin_cannot_access_user_management_routes(): void
    {
        $operator = User::factory()->create([
            'role' => 'operator',
        ]);

        $target = User::factory()->create([
            'role' => 'manager',
        ]);

        $this->actingAs($operator)->get(route('users.index'))->assertStatus(403);
        $this->actingAs($operator)->get(route('users.create'))->assertStatus(403);
        $this->actingAs($operator)->get(route('users.edit', $target))->assertStatus(403);
        $this->actingAs($operator)->post(route('users.store'), [])->assertStatus(403);
        $this->actingAs($operator)->put(route('users.update', $target), [])->assertStatus(403);
    }

    public function test_duplicate_email_is_blocked_on_create(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        User::factory()->create([
            'email' => 'exists@example.com',
            'role' => 'operator',
        ]);

        $response = $this->actingAs($admin)->from(route('users.create'))->post(route('users.store'), [
            'name' => 'Another User',
            'email' => 'exists@example.com',
            'role' => 'operator',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('users.create'));
        $response->assertSessionHasErrors('email');
    }

    public function test_admin_cannot_remove_own_admin_role(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->from(route('users.edit', $admin))->put(route('users.update', $admin), [
            'name' => $admin->name,
            'email' => $admin->email,
            'role' => 'operator',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertRedirect(route('users.edit', $admin));
        $response->assertSessionHasErrors('role');

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'role' => 'admin',
        ]);
    }
}
