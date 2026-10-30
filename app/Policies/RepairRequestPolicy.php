<?php

namespace App\Policies;

use App\Models\RepairRequest;
use App\Models\User;

/**
 * 修理依頼に関する認可を管理する。
 */
class RepairRequestPolicy
{
    /**
     * 修理依頼一覧を閲覧できるか判定する。
     */
    public function viewAny(User $user): bool
    {
        return $user->is_active
            && in_array($user->role, [
                'requester',
                'technician',
                'admin',
            ], true);
    }

    /**
     * 修理依頼の詳細を閲覧できるか判定する。
     */
    public function view(User $user, RepairRequest $repairRequest): bool
    {
        if (! $user->is_active) {
            return false;
        }

        return match ($user->role) {
            'requester' => (int) $repairRequest->requester_id === (int) $user->id,
            'technician' => (int) $repairRequest->assigned_to === (int) $user->id,
            'admin' => true,
            default => false,
        };
    }

    /**
     * 修理依頼を登録できるか判定する。
     */
    public function create(User $user): bool
    {
        return $user->is_active
            && $user->role === 'requester';
    }

    /**
     * 担当技術者を割り当てられるか判定する。
     */
    public function assignTechnician(User $user): bool
    {
        return $user->is_active
            && $user->role === 'admin';
    }

    /**
     * ステータスを更新できるか判定する。
     */
    public function updateStatus(
        User $user,
        RepairRequest $repairRequest
    ): bool {
        return $user->is_active
            && $user->role === 'technician'
            && (int) $repairRequest->assigned_to === (int) $user->id;
    }

    /**
     * キャンセル申請を提出できるか判定する。
     */
    public function requestCancellation(
        User $user,
        RepairRequest $repairRequest
    ): bool {
        return $user->is_active
            && $user->role === 'requester'
            && (int) $repairRequest->requester_id === (int) $user->id
            && $repairRequest->status === 'received';
    }
}
