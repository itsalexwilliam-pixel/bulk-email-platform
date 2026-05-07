<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Public registration is intentionally disabled; users are created by admins.
     */
    public function test_registration_screen_is_not_available_publicly(): void
    {
        $response = $this->get('/register');

        $response->assertNotFound();
    }

    /**
     * Public registration is intentionally disabled; users are created by admins.
     */
    public function test_new_users_cannot_register_publicly(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertNotFound();
        $this->assertGuest();
        $this->assertDatabaseMissing('users', [
            'email' => 'test@example.com',
        ]);
    }
}
