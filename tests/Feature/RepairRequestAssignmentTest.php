<?php

namespace Tests\Feature;

use App\Models\RepairRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RepairRequestAssignmentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 管理者が有効な技術者を担当に割り当て、履歴を記録できること。
     */
    public function test_admin_can_assign_active_technician_and_append_log(): void
    {
        $admin = User::factory()->admin()->create();
        $requester = User::factory()->requester()->create();
        $technician = User::factory()->technician()->create();

        $repairRequest = RepairRequest::factory()
            ->for($requester, 'requester')
            ->received()
            ->normalPriority()
            ->create();

        $response = $this->actingAs($admin)->patch(
            route('repair-requests.assign-technician', $repairRequest),
            ['assigned_to' => $technician->id]
        );

        $response
            ->assertRedirect(route('repair-requests.show', $repairRequest))
            ->assertSessionHas('status', '担当技術者を割り当てました。');

        $this->assertDatabaseHas('repair_requests', [
            'id' => $repairRequest->id,
            'assigned_to' => $technician->id,
        ]);

        $this->assertDatabaseHas('repair_logs', [
            'repair_request_id' => $repairRequest->id,
            'user_id' => $admin->id,
            'action' => 'technician_assigned',
            'message' => "担当技術者を {$technician->name} に割り当てました。",
            'from_status' => null,
            'to_status' => null,
        ]);
    }

    /**
     * 管理者以外は担当技術者を割り当てられないこと。
     */
    public function test_non_admin_cannot_assign_technician(): void
    {
        $requester = User::factory()->requester()->create();
        $technician = User::factory()->technician()->create();

        $repairRequest = RepairRequest::factory()
            ->for($requester, 'requester')
            ->received()
            ->create();

        $otherRequester = User::factory()->requester()->create();

        $response = $this->actingAs($otherRequester)->patch(
            route('repair-requests.assign-technician', $repairRequest),
            ['assigned_to' => $technician->id]
        );

        $response->assertForbidden();

        $this->assertDatabaseHas('repair_requests', [
            'id' => $repairRequest->id,
            'assigned_to' => null,
        ]);

        $this->assertDatabaseMissing('repair_logs', [
            'repair_request_id' => $repairRequest->id,
            'action' => 'technician_assigned',
        ]);
    }

    /**
     * 依頼者を担当技術者として指定できないこと。
     */
    public function test_admin_cannot_assign_requester_as_technician(): void
    {
        $admin = User::factory()->admin()->create();
        $requester = User::factory()->requester()->create();

        $repairRequest = RepairRequest::factory()
            ->for($requester, 'requester')
            ->received()
            ->create();

        $response = $this->actingAs($admin)->patch(
            route('repair-requests.assign-technician', $repairRequest),
            ['assigned_to' => $requester->id]
        );

        $response->assertSessionHasErrors('assigned_to');

        $this->assertDatabaseHas('repair_requests', [
            'id' => $repairRequest->id,
            'assigned_to' => null,
        ]);
    }

    /**
     * 無効化された技術者を担当に割り当てられないこと。
     */
    public function test_admin_cannot_assign_inactive_technician(): void
    {
        $admin = User::factory()->admin()->create();
        $requester = User::factory()->requester()->create();
        $technician = User::factory()
            ->technician()
            ->inactive()
            ->create();

        $repairRequest = RepairRequest::factory()
            ->for($requester, 'requester')
            ->received()
            ->create();

        $response = $this->actingAs($admin)->patch(
            route('repair-requests.assign-technician', $repairRequest),
            ['assigned_to' => $technician->id]
        );

        $response->assertSessionHasErrors('assigned_to');

        $this->assertDatabaseHas('repair_requests', [
            'id' => $repairRequest->id,
            'assigned_to' => null,
        ]);
    }

    /**
     * 管理者が担当技術者を変更すると、変更前後の名前を履歴に記録すること。
     */
    public function test_admin_can_reassign_technician_and_append_change_log(): void
    {
        $admin = User::factory()->admin()->create();
        $requester = User::factory()->requester()->create();
        $previousTechnician = User::factory()->technician()->create();
        $newTechnician = User::factory()->technician()->create();

        $repairRequest = RepairRequest::factory()
            ->for($requester, 'requester')
            ->assignedTo($previousTechnician)
            ->received()
            ->create();

        $response = $this->actingAs($admin)->patch(
            route('repair-requests.assign-technician', $repairRequest),
            ['assigned_to' => $newTechnician->id]
        );

        $response
            ->assertRedirect(route('repair-requests.show', $repairRequest))
            ->assertSessionHas('status', '担当技術者を割り当てました。');

        $this->assertDatabaseHas('repair_requests', [
            'id' => $repairRequest->id,
            'assigned_to' => $newTechnician->id,
        ]);

        $this->assertDatabaseHas('repair_logs', [
            'repair_request_id' => $repairRequest->id,
            'user_id' => $admin->id,
            'action' => 'technician_assigned',
            'message' => "担当技術者を「{$previousTechnician->name}」から「{$newTechnician->name}」に変更しました。",
            'from_status' => null,
            'to_status' => null,
        ]);

        $this->assertDatabaseCount('repair_logs', 1);
    }

    /**
     * 同じ技術者を再選択しても、履歴を追加しないこと。
     */
    public function test_admin_reselecting_same_technician_does_not_append_log(): void
    {
        $admin = User::factory()->admin()->create();
        $requester = User::factory()->requester()->create();
        $technician = User::factory()->technician()->create();

        $repairRequest = RepairRequest::factory()
            ->for($requester, 'requester')
            ->assignedTo($technician)
            ->received()
            ->create();

        $response = $this->actingAs($admin)->patch(
            route('repair-requests.assign-technician', $repairRequest),
            ['assigned_to' => $technician->id]
        );

        $response
            ->assertRedirect(route('repair-requests.show', $repairRequest))
            ->assertSessionHas('status', '担当技術者に変更はありません。');

        $this->assertDatabaseHas('repair_requests', [
            'id' => $repairRequest->id,
            'assigned_to' => $technician->id,
        ]);

        $this->assertDatabaseMissing('repair_logs', [
            'repair_request_id' => $repairRequest->id,
            'action' => 'technician_assigned',
        ]);

        $this->assertDatabaseCount('repair_logs', 0);
    }

    /**
     * 完了済みの修理依頼は、別の技術者に再割り当てできないこと。
     */
    public function test_admin_cannot_reassign_technician_for_completed_repair_request(): void
    {
        $admin = User::factory()->admin()->create();
        $requester = User::factory()->requester()->create();
        $previousTechnician = User::factory()->technician()->create();
        $newTechnician = User::factory()->technician()->create();

        $repairRequest = RepairRequest::factory()
            ->for($requester, 'requester')
            ->assignedTo($previousTechnician)
            ->completed()
            ->create();

        $response = $this->actingAs($admin)->patch(
            route('repair-requests.assign-technician', $repairRequest),
            ['assigned_to' => $newTechnician->id]
        );

        $response->assertSessionHasErrors('assigned_to');

        $this->assertDatabaseHas('repair_requests', [
            'id' => $repairRequest->id,
            'assigned_to' => $previousTechnician->id,
            'status' => 'completed',
        ]);

        $this->assertDatabaseCount('repair_logs', 0);
    }

    /**
     * 完了済みの修理依頼は、同じ技術者を再選択した場合も拒否すること。
     */
    public function test_admin_cannot_reselect_technician_for_completed_repair_request(): void
    {
        $admin = User::factory()->admin()->create();
        $requester = User::factory()->requester()->create();
        $technician = User::factory()->technician()->create();

        $repairRequest = RepairRequest::factory()
            ->for($requester, 'requester')
            ->assignedTo($technician)
            ->completed()
            ->create();

        $response = $this->actingAs($admin)->patch(
            route('repair-requests.assign-technician', $repairRequest),
            ['assigned_to' => $technician->id]
        );

        $response->assertSessionHasErrors('assigned_to');

        $this->assertDatabaseHas('repair_requests', [
            'id' => $repairRequest->id,
            'assigned_to' => $technician->id,
            'status' => 'completed',
        ]);

        $this->assertDatabaseCount('repair_logs', 0);
    }

    /**
     * キャンセル済みの修理依頼は、別の技術者に再割り当てできないこと。
     */
    public function test_admin_cannot_reassign_technician_for_cancelled_repair_request(): void
    {
        $admin = User::factory()->admin()->create();
        $requester = User::factory()->requester()->create();
        $previousTechnician = User::factory()->technician()->create();
        $newTechnician = User::factory()->technician()->create();

        $repairRequest = RepairRequest::factory()
            ->for($requester, 'requester')
            ->assignedTo($previousTechnician)
            ->cancelled()
            ->create();

        $response = $this->actingAs($admin)->patch(
            route('repair-requests.assign-technician', $repairRequest),
            ['assigned_to' => $newTechnician->id]
        );

        $response->assertSessionHasErrors('assigned_to');

        $this->assertDatabaseHas('repair_requests', [
            'id' => $repairRequest->id,
            'assigned_to' => $previousTechnician->id,
            'status' => 'cancelled',
        ]);

        $this->assertDatabaseCount('repair_logs', 0);
    }
}
