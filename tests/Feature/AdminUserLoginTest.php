<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminUserLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_created_user_is_verified_and_can_login_directly(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post('/admin/users', [
            'name' => 'Guru Baru',
            'email' => 'guru.baru@example.com',
            'phone' => '08123456789',
            'role' => 'guru',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect(route('admin.users.index'));

        $createdUser = User::where('email', 'guru.baru@example.com')->firstOrFail();
        $this->assertNotNull($createdUser->email_verified_at);
        $this->assertTrue(Hash::check('password', $createdUser->password));

        $this->post('/logout');
        $this->post('/login', [
            'email' => 'guru.baru@example.com',
            'password' => 'password',
        ])->assertRedirect(route('dashboard', absolute: false));

        $this->get('/dashboard')->assertRedirect(route('guru.dashboard'));
        $this->assertAuthenticatedAs($createdUser);
    }

    public function test_admin_created_pic_can_login_directly_to_pic_dashboard(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post('/admin/users', [
            'name' => 'PIC Baru',
            'email' => 'pic.baru@example.com',
            'role' => 'pic',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect(route('admin.users.index'));

        $this->post('/logout');
        $this->post('/login', [
            'email' => 'pic.baru@example.com',
            'password' => 'password',
        ])->assertRedirect(route('dashboard', absolute: false));

        $this->get('/dashboard')->assertRedirect(route('pic.dashboard'));
    }

    public function test_inactive_user_cannot_login(): void
    {
        User::factory()->create([
            'email' => 'inactive@example.com',
            'password' => 'password',
            'is_active' => false,
        ]);

        $this->post('/login', [
            'email' => 'inactive@example.com',
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }
}
