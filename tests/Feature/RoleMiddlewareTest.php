<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_opening_a_protected_role_page(): void
    {
        $this->get('/guru/dashboard')
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_user_is_redirected_to_the_dashboard_for_their_role(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get('/guru/dashboard')
            ->assertRedirect(route('admin.dashboard'));
    }
}
