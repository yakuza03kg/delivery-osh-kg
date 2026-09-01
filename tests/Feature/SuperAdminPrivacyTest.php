<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminPrivacyTest extends TestCase
{
    use RefreshDatabase;

    public function test_regular_admin_cannot_see_or_manage_super_admin_accounts(): void
    {
        $admin = User::query()->create([
            'name' => 'Администратор',
            'email' => 'admin@example.com',
            'password' => 'password',
            'role' => 'admin',
        ]);
        $superAdmin = User::query()->create([
            'name' => 'Владелец',
            'email' => 'owner@example.com',
            'password' => 'password',
            'role' => 'super_admin',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertDontSee('owner@example.com');

        $this->actingAs($admin)
            ->get(route('admin.users.edit', $superAdmin))
            ->assertNotFound();

        $this->actingAs($admin)
            ->post(route('admin.users.store'), [
                'name' => 'Новый',
                'email' => 'new@example.com',
                'role' => 'super_admin',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ])
            ->assertSessionHasErrors('role');
    }
}
