<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * ユーザー管理機能を検証する。
 */
class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 管理者はユーザー登録画面を表示できる。
     */
    public function test_admin_can_view_user_creation_form(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('users.create'))
            ->assertOk()
            ->assertViewIs('users.create');
    }

    /**
     * 管理者は3種類のユーザーを登録できる。
     */
    public function test_admin_can_create_users_with_each_role(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $roles = ['requester', 'technician', 'admin'];

        foreach ($roles as $index => $role) {
            $email = "new-user-{$index}@example.com";
            $password = 'password123';

            $response = $this->actingAs($admin)
                ->post(route('users.store'), [
                    'name' => "新規ユーザー{$index}",
                    'email' => $email,
                    'password' => $password,
                    'password_confirmation' => $password,
                    'role' => $role,
                ]);

            $response->assertRedirect(route('users.index'));
            $response->assertSessionHas(
                'success',
                'ユーザーを登録しました。'
            );

            $this->assertDatabaseHas('users', [
                'name' => "新規ユーザー{$index}",
                'email' => $email,
                'role' => $role,
                'is_active' => true,
            ]);

            $user = User::where('email', $email)->firstOrFail();

            $this->assertTrue(
                Hash::check($password, $user->password)
            );
        }
    }

    /**
     * is_active=falseを送信しても有効状態で登録される。
     */
    public function test_created_user_is_active_even_if_inactive_value_is_submitted(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->post(route('users.store'), [
                'name' => '不正入力テストユーザー',
                'email' => 'tampered@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => 'requester',
                'is_active' => false,
            ])
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'tampered@example.com',
            'role' => 'requester',
            'is_active' => true,
        ]);
    }

    /**
     * 管理者以外はユーザーを登録できない。
     */
    public function test_non_admin_cannot_create_users(): void
    {
        foreach (['requester', 'technician'] as $role) {
            $user = User::factory()->create([
                'role' => $role,
                'is_active' => true,
            ]);

            $this->actingAs($user)
                ->post(route('users.store'), [
                    'name' => '登録拒否テスト',
                    'email' => "{$role}@example.com",
                    'password' => 'password123',
                    'password_confirmation' => 'password123',
                    'role' => 'requester',
                ])
                ->assertForbidden();
        }

        $this->assertDatabaseMissing('users', [
            'email' => 'requester@example.com',
        ]);

        $this->assertDatabaseMissing('users', [
            'email' => 'technician@example.com',
        ]);
    }

    /**
     * 無効化された管理者はユーザーを登録できない。
     */
    public function test_inactive_admin_cannot_create_users(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => false,
        ]);

        $this->actingAs($admin)
            ->post(route('users.store'), [
                'name' => '登録拒否テスト',
                'email' => 'inactive-admin@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => 'requester',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('users', [
            'email' => 'inactive-admin@example.com',
        ]);
    }

    /**
     * 未認証ユーザーはユーザーを登録できない。
     */
    public function test_guest_cannot_create_users(): void
    {
        $this->post(route('users.store'), [
            'name' => '未認証テスト',
            'email' => 'guest@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'requester',
        ])->assertRedirect(route('login'));

        $this->assertDatabaseMissing('users', [
            'email' => 'guest@example.com',
        ]);
    }

    /**
     * 必須項目が未入力の場合は登録できない。
     */
    public function test_user_creation_requires_all_fields(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->post(route('users.store'), [])
            ->assertSessionHasErrors([
                'name',
                'email',
                'password',
                'role',
            ]);
    }

    /**
     * 登録済みのメールアドレスは使用できない。
     */
    public function test_user_creation_rejects_duplicate_email(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $existingUser = User::factory()->create([
            'email' => 'existing@example.com',
        ]);

        $this->actingAs($admin)
            ->post(route('users.store'), [
                'name' => '重複テスト',
                'email' => $existingUser->email,
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => 'requester',
            ])
            ->assertSessionHasErrors('email');
    }

    /**
     * 不正なユーザー種別は登録できない。
     */
    public function test_user_creation_rejects_invalid_role(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->post(route('users.store'), [
                'name' => '不正種別テスト',
                'email' => 'invalid-role@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => 'super_admin',
            ])
            ->assertSessionHasErrors('role');
    }

    /**
     * 8文字未満のパスワードは登録できない。
     */
    public function test_user_creation_rejects_short_password(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->post(route('users.store'), [
                'name' => '短いパスワードテスト',
                'email' => 'short-password@example.com',
                'password' => '1234567',
                'password_confirmation' => '1234567',
                'role' => 'requester',
            ])
            ->assertSessionHasErrors('password');
    }

    /**
     * パスワード確認が一致しない場合は登録できない。
     */
    public function test_user_creation_rejects_password_mismatch(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->post(route('users.store'), [
                'name' => '確認入力テスト',
                'email' => 'mismatch@example.com',
                'password' => 'password123',
                'password_confirmation' => 'different123',
                'role' => 'requester',
            ])
            ->assertSessionHasErrors('password');
    }
}
