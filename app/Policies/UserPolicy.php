<?php

namespace App\Policies;

use App\Models\User;

/**
 * ユーザー管理に関する認可を管理する。
 */
class UserPolicy
{
    /**
     * ユーザー一覧を閲覧できるか判定する。
     */
    public function viewAny(User $user): bool
    {
        return $user->is_active
            && $user->role === 'admin';
    }

    /**
     * ユーザーを登録できるか判定する。
     */
    public function create(User $user): bool
    {
        return $user->is_active
            && $user->role === 'admin';
    }
}
