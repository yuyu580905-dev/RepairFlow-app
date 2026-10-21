<?php

namespace Tests\Feature;

use App\Models\RepairRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RepairRequestRelationshipTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 依頼者と担当技術者をそれぞれ取得できることを確認する。
     */
    public function test_repair_request_can_retrieve_requester_and_assigned_technician(): void
    {
        $requester = User::factory()->create();
        $technician = User::factory()->create();

        $repairRequest = RepairRequest::create([
            'requester_id' => $requester->id,
            'assigned_to' => $technician->id,
            'title' => 'テスト修理依頼',
            'description' => '動作確認のための修理依頼です。',
            'location' => '3階事務室',
            'asset_name' => 'プリンター',
        ]);

        $this->assertSame(
            $requester->id,
            $repairRequest->requester->id
        );

        $this->assertSame(
            $technician->id,
            $repairRequest->assignedTechnician->id
        );

        $this->assertSame(
            $repairRequest->id,
            $requester->repairRequests()->firstOrFail()->id
        );

        $this->assertSame(
            $repairRequest->id,
            $technician->assignedRepairRequests()->firstOrFail()->id
        );
    }

    /**
     * 1つの修理依頼に複数の修理履歴を登録・取得できることを確認する。
     */
    public function test_repair_request_can_have_multiple_repair_logs(): void
    {
        $user = User::factory()->create();

        $repairRequest = RepairRequest::create([
            'requester_id' => $user->id,
            'title' => 'テスト修理依頼',
            'description' => '修理履歴の確認です。',
            'location' => '2階会議室',
            'asset_name' => 'モニター',
        ]);

        $repairRequest->repairLogs()->create([
            'user_id' => $user->id,
            'action' => 'status_changed',
            'message' => '受付から対応中に変更しました。',
            'from_status' => 'received',
            'to_status' => 'in_progress',
        ]);

        $repairRequest->repairLogs()->create([
            'user_id' => $user->id,
            'action' => 'work_note',
            'message' => 'ケーブルの接続を確認しました。',
        ]);

        $this->assertCount(2, $repairRequest->repairLogs);

        $this->assertSame(
            $repairRequest->id,
            $repairRequest->repairLogs->first()->repairRequest->id
        );

        $this->assertSame(
            $user->id,
            $repairRequest->repairLogs->first()->user->id
        );

        $this->assertCount(2, $user->repairLogs);
    }

    /**
     * 修理依頼に複数のキャンセル申請を関連付けられることを確認する。
     */
    public function test_repair_request_can_have_multiple_cancellation_requests(): void
    {
        $requester = User::factory()->create();
        $reviewer = User::factory()->create();

        $repairRequest = RepairRequest::create([
            'requester_id' => $requester->id,
            'title' => 'テスト修理依頼',
            'description' => 'キャンセル申請の確認です。',
            'location' => '1階受付',
            'asset_name' => 'タブレット',
        ]);

        $firstCancellation = $repairRequest->cancellationRequests()->create([
            'requester_id' => $requester->id,
            'reason' => '依頼内容を誤って登録しました。',
        ]);

        $secondCancellation = $repairRequest->cancellationRequests()->create([
            'requester_id' => $requester->id,
            'reason' => '修理が不要になりました。',
            'reviewed_by' => $reviewer->id,
            'status' => 'rejected',
            'review_comment' => '修理対応を開始しているため。',
            'reviewed_at' => now(),
        ]);

        $this->assertCount(2, $repairRequest->cancellationRequests);

        $this->assertSame(
            $requester->id,
            $firstCancellation->requester->id
        );

        $this->assertNull($firstCancellation->reviewer);

        $this->assertSame(
            $reviewer->id,
            $secondCancellation->reviewer->id
        );

        $this->assertSame(
            $repairRequest->id,
            $firstCancellation->repairRequest->id
        );

        $this->assertCount(2, $requester->cancellationRequests);

        $this->assertSame(
            $secondCancellation->id,
            $reviewer->reviewedCancellationRequests()->firstOrFail()->id
        );
    }
}
