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

    /**
     * 管理者はユーザー編集画面を表示できる。
     */
    public function test_admin_can_view_user_edit_form(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $target = User::factory()->create();

        $this->actingAs($admin)
            ->get(route('users.edit', $target))
            ->assertOk()
            ->assertViewIs('users.edit')
            ->assertViewHas('user', $target);
    }

    /**
     * 管理者はユーザー情報を更新できる。
     */
    public function test_admin_can_update_user_information(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $target = User::factory()->create([
            'role' => 'requester',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->put(route('users.update', $target), [
                'name' => '更新後ユーザー',
                'email' => 'updated@example.com',
                'role' => 'technician',
                'is_active' => '0',
            ])
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('success', 'ユーザー情報を更新しました。');

        $this->assertDatabaseHas('users', [
            'id' => $target->id,
            'name' => '更新後ユーザー',
            'email' => 'updated@example.com',
            'role' => 'technician',
            'is_active' => false,
        ]);
    }

    /**
     * 自分自身のメールアドレスを変更しなければ更新できる。
     */
    public function test_admin_can_update_user_without_changing_email(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $target = User::factory()->create([
            'email' => 'same@example.com',
        ]);

        $this->actingAs($admin)
            ->put(route('users.update', $target), [
                'name' => '名前変更',
                'email' => 'same@example.com',
                'role' => 'requester',
                'is_active' => '1',
            ])
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'id' => $target->id,
            'name' => '名前変更',
            'email' => 'same@example.com',
        ]);
    }

    /**
     * 他のユーザーと重複するメールアドレスには更新できない。
     */
    public function test_user_update_rejects_duplicate_email(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $target = User::factory()->create();

        $existingUser = User::factory()->create([
            'email' => 'existing@example.com',
        ]);

        $this->actingAs($admin)
            ->put(route('users.update', $target), [
                'name' => '更新テスト',
                'email' => $existingUser->email,
                'role' => 'requester',
                'is_active' => '1',
            ])
            ->assertSessionHasErrors('email');

        $this->assertDatabaseHas('users', [
            'id' => $target->id,
            'email' => $target->email,
        ]);
    }

    /**
     * 不正なユーザー種別では更新できない。
     */
    public function test_user_update_rejects_invalid_role(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $target = User::factory()->create();

        $this->actingAs($admin)
            ->put(route('users.update', $target), [
                'name' => '不正種別テスト',
                'email' => $target->email,
                'role' => 'super_admin',
                'is_active' => '1',
            ])
            ->assertSessionHasErrors('role');
    }

    /**
     * 管理者以外はユーザー情報を更新できない。
     */
    public function test_non_admin_cannot_update_users(): void
    {
        $target = User::factory()->create();

        foreach (['requester', 'technician'] as $role) {
            $user = User::factory()->create([
                'role' => $role,
                'is_active' => true,
            ]);

            $this->actingAs($user)
                ->put(route('users.update', $target), [
                    'name' => '不正更新',
                    'email' => $target->email,
                    'role' => 'admin',
                    'is_active' => '1',
                ])
                ->assertForbidden();
        }

        $this->assertDatabaseMissing('users', [
            'id' => $target->id,
            'name' => '不正更新',
        ]);
    }

    /**
     * 無効化された管理者はユーザー情報を更新できない。
     */
    public function test_inactive_admin_cannot_update_users(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => false,
        ]);

        $target = User::factory()->create();

        $this->actingAs($admin)
            ->put(route('users.update', $target), [
                'name' => '不正更新',
                'email' => $target->email,
                'role' => 'requester',
                'is_active' => '1',
            ])
            ->assertForbidden();
    }

    /**
     * 未認証ユーザーはユーザー情報を更新できない。
     */
    public function test_guest_cannot_update_users(): void
    {
        $target = User::factory()->create();

        $this->put(route('users.update', $target), [
            'name' => '未認証更新',
            'email' => $target->email,
            'role' => 'requester',
            'is_active' => '1',
        ])->assertRedirect(route('login'));
    }

    /**
     * 最後の有効な管理者は無効化できない。
     */
    public function test_last_active_admin_cannot_be_deactivated(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->put(route('users.update', $admin), [
                'name' => $admin->name,
                'email' => $admin->email,
                'role' => 'admin',
                'is_active' => '0',
            ])
            ->assertSessionHasErrors('role');

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    /**
     * 他に有効な管理者がいれば管理者を無効化できる。
     */
    public function test_admin_can_be_deactivated_when_another_active_admin_exists(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $target = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->put(route('users.update', $target), [
                'name' => $target->name,
                'email' => $target->email,
                'role' => 'admin',
                'is_active' => '0',
            ])
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'id' => $target->id,
            'is_active' => false,
        ]);
    }
}
