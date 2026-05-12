<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UserManagementFeatureTest extends TestCase
{
    use RefreshDatabase;

    private function createAccountId(): int
    {
        return (int) DB::table('accounts')->insertGetId([
            'name' => 'Test Account '.uniqid(),
            'plan_id' => DB::table('plans')->value('id'),
            'owner_user_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function actingAsAdmin(?int $accountId = null): User
    {
        $accountId = $accountId ?? $this->createAccountId();

        $admin = User::factory()->create([
            'role' => 'admin',
            'account_id' => $accountId,
        ]);

        $this->actingAs($admin);

        return $admin;
    }

    public function test_admin_can_view_users_index(): void
    {
        $accountId = $this->createAccountId();
        $admin = $this->actingAsAdmin($accountId);

        User::factory()->create([
            'role' => 'operator',
            'account_id' => $accountId,
        ]);

        $response = $this->actingAs($admin)->get(route('users.index'));

        $response->assertOk();
        $response->assertSee('Users');
    }

    public function test_admin_can_create_user(): void
    {
        $admin = $this->actingAsAdmin();

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
        $admin = $this->actingAsAdmin();

        $target = User::factory()->create([
            'role' => 'operator',
            'account_id' => $admin->account_id,
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
        $accountId = $this->createAccountId();

        $operator = User::factory()->create([
            'role' => 'operator',
            'account_id' => $accountId,
        ]);

        $target = User::factory()->create([
            'role' => 'manager',
            'account_id' => $accountId,
        ]);

        $this->actingAs($operator)->get(route('users.index'))->assertStatus(403);
        $this->actingAs($operator)->get(route('users.create'))->assertStatus(403);
        $this->actingAs($operator)->get(route('users.edit', $target))->assertStatus(403);
        $this->actingAs($operator)->post(route('users.store'), [])->assertStatus(403);
        $this->actingAs($operator)->put(route('users.update', $target), [])->assertStatus(403);
    }

    public function test_duplicate_email_is_blocked_on_create(): void
    {
        $admin = $this->actingAsAdmin();

        User::factory()->create([
            'email' => 'exists@example.com',
            'role' => 'operator',
            'account_id' => $admin->account_id,
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
        $admin = $this->actingAsAdmin();

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
