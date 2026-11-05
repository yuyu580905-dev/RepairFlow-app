<?php

namespace Tests\Feature;

use App\Models\RepairRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RepairRequestPolicyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 有効なユーザーは、各ロールに応じて一覧を閲覧できる。
     */
    public function test_active_users_can_view_repair_request_list(): void
    {
        $users = [
            User::factory()->requester()->create(),
            User::factory()->technician()->create(),
            User::factory()->admin()->create(),
        ];

        foreach ($users as $user) {
            $this->assertTrue($user->can('viewAny', RepairRequest::class));
        }
    }

    /**
     * 無効化されたユーザーは一覧を閲覧できない。
     */
    public function test_inactive_user_cannot_view_repair_request_list(): void
    {
        $user = User::factory()->requester()->inactive()->create();

        $this->assertFalse($user->can('viewAny', RepairRequest::class));
    }

    /**
     * 依頼者は自分の依頼のみ閲覧できる。
     */
    public function test_requester_can_only_view_own_repair_request(): void
    {
        $requester = User::factory()->requester()->create();
        $otherRequester = User::factory()->requester()->create();

        $ownRequest = RepairRequest::factory()
            ->for($requester, 'requester')
            ->received()
            ->unassigned()
            ->create();

        $othersRequest = RepairRequest::factory()
            ->for($otherRequester, 'requester')
            ->received()
            ->unassigned()
            ->create();

        $this->assertTrue($requester->can('view', $ownRequest));
        $this->assertFalse($requester->can('view', $othersRequest));
    }

    /**
     * 技術者は自分の担当案件のみ閲覧できる。
     */
    public function test_technician_can_only_view_assigned_repair_request(): void
    {
        $requester = User::factory()->requester()->create();
        $technician = User::factory()->technician()->create();
        $otherTechnician = User::factory()->technician()->create();

        $assignedRequest = RepairRequest::factory()
            ->for($requester, 'requester')
            ->received()
            ->assignedTo($technician)
            ->create();

        $otherRequest = RepairRequest::factory()
            ->for($requester, 'requester')
            ->received()
            ->assignedTo($otherTechnician)
            ->create();

        $this->assertTrue($technician->can('view', $assignedRequest));
        $this->assertFalse($technician->can('view', $otherRequest));
    }

    /**
     * 管理者はすべての修理依頼を閲覧できる。
     */
    public function test_admin_can_view_any_repair_request(): void
    {
        $requester = User::factory()->requester()->create();
        $admin = User::factory()->admin()->create();

        $repairRequest = RepairRequest::factory()
            ->for($requester, 'requester')
            ->received()
            ->unassigned()
            ->create();

        $this->assertTrue($admin->can('view', $repairRequest));
    }

    /**
     * 無効化されたユーザーは修理依頼を閲覧できない。
     */
    public function test_inactive_user_cannot_view_repair_request(): void
    {
        $requester = User::factory()->requester()->create();

        $repairRequest = RepairRequest::factory()
            ->for($requester, 'requester')
            ->received()
            ->unassigned()
            ->create();

        $inactiveUser = User::factory()->admin()->inactive()->create();

        $this->assertFalse($inactiveUser->can('view', $repairRequest));
    }

    /**
     * 有効な依頼者のみ修理依頼を作成できる。
     */
    public function test_only_active_requester_can_create_repair_request(): void
    {
        $requester = User::factory()->requester()->create();
        $technician = User::factory()->technician()->create();
        $admin = User::factory()->admin()->create();
        $inactiveRequester = User::factory()->requester()->inactive()->create();

        $this->assertTrue($requester->can('create', RepairRequest::class));
        $this->assertFalse($technician->can('create', RepairRequest::class));
        $this->assertFalse($admin->can('create', RepairRequest::class));
        $this->assertFalse($inactiveRequester->can('create', RepairRequest::class));
    }

    /**
     * 有効な管理者のみ担当技術者を割り当てられる。
     */
    public function test_only_active_admin_can_assign_technician(): void
    {
        $admin = User::factory()->admin()->create();
        $inactiveAdmin = User::factory()->admin()->inactive()->create();
        $requester = User::factory()->requester()->create();
        $technician = User::factory()->technician()->create();

        $this->assertTrue($admin->can('assignTechnician', RepairRequest::class));
        $this->assertFalse($inactiveAdmin->can('assignTechnician', RepairRequest::class));
        $this->assertFalse($requester->can('assignTechnician', RepairRequest::class));
        $this->assertFalse($technician->can('assignTechnician', RepairRequest::class));
    }

    /**
     * 技術者は自分の担当案件のステータスを更新できる。
     */
    public function test_technician_can_update_status_of_assigned_repair_request(): void
    {
        $requester = User::factory()->requester()->create();
        $technician = User::factory()->technician()->create();
        $otherTechnician = User::factory()->technician()->create();

        $assignedRequest = RepairRequest::factory()
            ->for($requester, 'requester')
            ->received()
            ->assignedTo($technician)
            ->create();

        $otherRequest = RepairRequest::factory()
            ->for($requester, 'requester')
            ->received()
            ->assignedTo($otherTechnician)
            ->create();

        $this->assertTrue($technician->can('updateStatus', $assignedRequest));
        $this->assertFalse($technician->can('updateStatus', $otherRequest));
    }

    /**
     * 管理者・依頼者・無効化された技術者はステータスを更新できない。
     */
    public function test_only_active_assigned_technician_can_update_status(): void
    {
        $requester = User::factory()->requester()->create();
        $technician = User::factory()->technician()->create();
        $admin = User::factory()->admin()->create();
        $otherRequester = User::factory()->requester()->create();

        $repairRequest = RepairRequest::factory()
            ->for($requester, 'requester')
            ->received()
            ->assignedTo($technician)
            ->create();

        $inactiveTechnician = User::factory()->technician()->inactive()->create();

        $this->assertFalse($admin->can('updateStatus', $repairRequest));
        $this->assertFalse($otherRequester->can('updateStatus', $repairRequest));
        $this->assertFalse($inactiveTechnician->can('updateStatus', $repairRequest));
    }

    /**
     * 依頼者は自分の受付済み修理依頼にキャンセル申請できる。
     */
    public function test_requester_can_request_cancellation_of_own_received_repair(): void
    {
        $requester = User::factory()->requester()->create();
        $otherRequester = User::factory()->requester()->create();

        $ownRequest = RepairRequest::factory()
            ->for($requester, 'requester')
            ->received()
            ->unassigned()
            ->create();

        $othersRequest = RepairRequest::factory()
            ->for($otherRequester, 'requester')
            ->received()
            ->unassigned()
            ->create();

        $this->assertTrue($requester->can('requestCancellation', $ownRequest));
        $this->assertFalse($requester->can('requestCancellation', $othersRequest));
    }

    /**
     * 受付済み以外の修理依頼にはキャンセル申請できない。
     */
    public function test_requester_cannot_request_cancellation_of_non_received_repair(): void
    {
        $requester = User::factory()->requester()->create();

        $repairRequest = RepairRequest::factory()
            ->for($requester, 'requester')
            ->inProgress()
            ->unassigned()
            ->create();

        $this->assertFalse($requester->can('requestCancellation', $repairRequest));
    }

    /**
     * 技術者・管理者・無効化された依頼者はキャンセル申請できない。
     */
    public function test_only_active_owner_requester_can_request_cancellation(): void
    {
        $requester = User::factory()->requester()->create();

        $repairRequest = RepairRequest::factory()
            ->for($requester, 'requester')
            ->received()
            ->unassigned()
            ->create();

        $technician = User::factory()->technician()->create();
        $admin = User::factory()->admin()->create();
        $inactiveRequester = User::factory()->requester()->inactive()->create();

        $this->assertFalse($technician->can('requestCancellation', $repairRequest));
        $this->assertFalse($admin->can('requestCancellation', $repairRequest));
        $this->assertFalse($inactiveRequester->can('requestCancellation', $repairRequest));
    }
}
