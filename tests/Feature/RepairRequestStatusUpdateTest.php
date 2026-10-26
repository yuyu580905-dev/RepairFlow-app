<?php

namespace Tests\Feature;

use App\Models\RepairRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RepairRequestStatusUpdateTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 担当技術者が受付済みから対応中へ変更し、履歴を記録できること。
     */
    public function test_assigned_technician_can_start_repair_and_append_log(): void
    {
        [$technician, $repairRequest] = $this->createAssignedRepairRequest();

        $response = $this->actingAs($technician)->patch(
            route('repair-requests.update-status', $repairRequest),
            [
                'status' => 'in_progress',
                'message' => '修理作業を開始します。',
            ]
        );

        $response
            ->assertRedirect(route('repair-requests.show', $repairRequest))
            ->assertSessionHas('status', 'ステータスを更新しました。');

        $this->assertDatabaseHas('repair_requests', [
            'id' => $repairRequest->id,
            'status' => 'in_progress',
        ]);

        $this->assertDatabaseHas('repair_logs', [
            'repair_request_id' => $repairRequest->id,
            'user_id' => $technician->id,
            'action' => 'status_changed',
            'message' => '修理作業を開始します。',
            'from_status' => 'received',
            'to_status' => 'in_progress',
        ]);
    }

    /**
     * 保留にする場合、理由の入力が必須であること。
     */
    public function test_reason_is_required_when_putting_repair_on_hold(): void
    {
        [$technician, $repairRequest] = $this->createAssignedRepairRequest();

        $response = $this->actingAs($technician)->patch(
            route('repair-requests.update-status', $repairRequest),
            ['status' => 'on_hold']
        );

        $response->assertSessionHasErrors('message');

        $this->assertDatabaseHas('repair_requests', [
            'id' => $repairRequest->id,
            'status' => 'received',
        ]);

        $this->assertDatabaseMissing('repair_logs', [
            'repair_request_id' => $repairRequest->id,
            'action' => 'status_changed',
        ]);
    }

    /**
     * 保留理由を入力すると保留へ変更し、理由を履歴に残せること。
     */
    public function test_assigned_technician_can_put_repair_on_hold_with_reason(): void
    {
        [$technician, $repairRequest] = $this->createAssignedRepairRequest();

        $response = $this->actingAs($technician)->patch(
            route('repair-requests.update-status', $repairRequest),
            [
                'status' => 'on_hold',
                'message' => '交換部品の到着待ちです。',
            ]
        );

        $response->assertRedirect(
            route('repair-requests.show', $repairRequest)
        );

        $this->assertDatabaseHas('repair_requests', [
            'id' => $repairRequest->id,
            'status' => 'on_hold',
        ]);

        $this->assertDatabaseHas('repair_logs', [
            'repair_request_id' => $repairRequest->id,
            'user_id' => $technician->id,
            'action' => 'status_changed',
            'message' => '交換部品の到着待ちです。',
            'from_status' => 'received',
            'to_status' => 'on_hold',
        ]);
    }

    /**
     * 対応中から完了へ変更できること。
     */
    public function test_assigned_technician_can_complete_in_progress_repair(): void
    {
        [$technician, $repairRequest] = $this->createAssignedRepairRequest(
            'in_progress'
        );

        $response = $this->actingAs($technician)->patch(
            route('repair-requests.update-status', $repairRequest),
            ['status' => 'completed']
        );

        $response->assertRedirect(
            route('repair-requests.show', $repairRequest)
        );

        $this->assertDatabaseHas('repair_requests', [
            'id' => $repairRequest->id,
            'status' => 'completed',
        ]);

        $this->assertDatabaseHas('repair_logs', [
            'repair_request_id' => $repairRequest->id,
            'user_id' => $technician->id,
            'action' => 'status_changed',
            'from_status' => 'in_progress',
            'to_status' => 'completed',
        ]);
    }

    /**
     * 保留中から対応中へ再開できること。
     */
    public function test_assigned_technician_can_resume_on_hold_repair(): void
    {
        [$technician, $repairRequest] = $this->createAssignedRepairRequest(
            'on_hold'
        );

        $response = $this->actingAs($technician)->patch(
            route('repair-requests.update-status', $repairRequest),
            [
                'status' => 'in_progress',
                'message' => '部品が到着したため作業を再開します。',
            ]
        );

        $response->assertRedirect(
            route('repair-requests.show', $repairRequest)
        );

        $this->assertDatabaseHas('repair_requests', [
            'id' => $repairRequest->id,
            'status' => 'in_progress',
        ]);

        $this->assertDatabaseHas('repair_logs', [
            'repair_request_id' => $repairRequest->id,
            'user_id' => $technician->id,
            'action' => 'status_changed',
            'from_status' => 'on_hold',
            'to_status' => 'in_progress',
            'message' => '部品が到着したため作業を再開します。',
        ]);
    }

    /**
     * 完了済みの依頼を通常のステータス更新で変更できないこと。
     */
    public function test_completed_repair_cannot_transition_to_another_status(): void
    {
        [$technician, $repairRequest] = $this->createAssignedRepairRequest(
            'completed'
        );

        $response = $this->actingAs($technician)->patch(
            route('repair-requests.update-status', $repairRequest),
            ['status' => 'in_progress']
        );

        $response->assertSessionHasErrors('status');

        $this->assertDatabaseHas('repair_requests', [
            'id' => $repairRequest->id,
            'status' => 'completed',
        ]);

        $this->assertDatabaseMissing('repair_logs', [
            'repair_request_id' => $repairRequest->id,
            'action' => 'status_changed',
        ]);
    }

    /**
     * ステータス更新からキャンセル済みへ直接変更できないこと。
     */
    public function test_technician_cannot_set_cancelled_status_directly(): void
    {
        [$technician, $repairRequest] = $this->createAssignedRepairRequest();

        $response = $this->actingAs($technician)->patch(
            route('repair-requests.update-status', $repairRequest),
            ['status' => 'cancelled']
        );

        $response->assertSessionHasErrors('status');

        $this->assertDatabaseHas('repair_requests', [
            'id' => $repairRequest->id,
            'status' => 'received',
        ]);

        $this->assertDatabaseMissing('repair_logs', [
            'repair_request_id' => $repairRequest->id,
            'action' => 'status_changed',
        ]);
    }

    /**
     * 担当していない技術者はステータスを変更できないこと。
     */
    public function test_unassigned_technician_cannot_update_repair_status(): void
    {
        [$assignedTechnician, $repairRequest] = $this->createAssignedRepairRequest();

        $otherTechnician = User::factory()->create();
        $otherTechnician->forceFill([
            'role' => 'technician',
            'is_active' => true,
        ])->save();

        $response = $this->actingAs($otherTechnician)->patch(
            route('repair-requests.update-status', $repairRequest),
            ['status' => 'in_progress']
        );

        $response->assertForbidden();

        $this->assertDatabaseHas('repair_requests', [
            'id' => $repairRequest->id,
            'status' => 'received',
        ]);
    }

    /**
     * 担当技術者と修理依頼をテスト用に作成する。
     *
     * @return array{0: User, 1: RepairRequest}
     */
    private function createAssignedRepairRequest(
        string $status = 'received'
    ): array {
        $requester = User::factory()->create();

        $technician = User::factory()->create();
        $technician->forceFill([
            'role' => 'technician',
            'is_active' => true,
        ])->save();

        $repairRequest = $requester->repairRequests()->create([
            'title' => 'テスト修理依頼',
            'description' => 'テスト用の修理依頼です。',
            'location' => '本社1階',
            'asset_name' => 'テスト用設備',
            'priority' => 'normal',
            'status' => $status,
            'assigned_to' => $technician->id,
        ]);

        return [$technician, $repairRequest];
    }
}
