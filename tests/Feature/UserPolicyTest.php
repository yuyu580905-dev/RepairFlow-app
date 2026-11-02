<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

/**
 * UserPolicyの認可処理を検証する。
 */
class UserPolicyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 有効な管理者はユーザー一覧を閲覧できる。
     */
    public function test_active_admin_can_view_any_users(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->assertTrue(
            Gate::forUser($admin)->allows('viewAny', User::class)
        );
    }

    /**
     * 管理者以外はユーザー一覧を閲覧できない。
     */
    public function test_non_admin_cannot_view_any_users(): void
    {
        foreach (['requester', 'technician'] as $role) {
            $user = User::factory()->create([
                'role' => $role,
                'is_active' => true,
            ]);

            $this->assertFalse(
                Gate::forUser($user)->allows('viewAny', User::class)
            );
        }
    }

    /**
     * 無効化された管理者はユーザー一覧を閲覧できない。
     */
    public function test_inactive_admin_cannot_view_any_users(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => false,
        ]);

        $this->assertFalse(
            Gate::forUser($admin)->allows('viewAny', User::class)
        );
    }

    /**
     * 有効な管理者はユーザーを登録できる。
     */
    public function test_active_admin_can_create_users(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->assertTrue(
            Gate::forUser($admin)->allows('create', User::class)
        );
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

            $this->assertFalse(
                Gate::forUser($user)->allows('create', User::class)
            );
        }
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

        $this->assertFalse(
            Gate::forUser($admin)->allows('create', User::class)
        );
    }

    /**
     * 有効な管理者はユーザー情報を更新できる。
     */
    public function test_active_admin_can_update_users(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $target = User::factory()->create();

        $this->assertTrue(
            Gate::forUser($admin)->allows('update', $target)
        );
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

            $this->assertFalse(
                Gate::forUser($user)->allows('update', $target)
            );
        }
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

        $this->assertFalse(
            Gate::forUser($admin)->allows('update', $target)
        );
    }
}
