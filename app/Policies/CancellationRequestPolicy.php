<?php

namespace App\Policies;

use App\Models\CancellationRequest;
use App\Models\User;

/**
 * キャンセル申請に関する認可を管理する。
 */
class CancellationRequestPolicy
{
    /**
     * キャンセル申請を審査できるか判定する。
     */
    public function review(
        User $user,
        CancellationRequest $cancellationRequest
    ): bool {
        return $user->is_active
            && $user->role === 'admin';
    }
}
