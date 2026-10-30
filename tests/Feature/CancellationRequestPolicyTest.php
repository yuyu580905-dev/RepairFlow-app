<?php

namespace Tests\Feature;

use App\Models\CancellationRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CancellationRequestPolicyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 有効な管理者はキャンセル申請を審査できる。
     */
    public function test_active_admin_can_review_cancellation_request(): void
    {
        $admin = User::factory()->admin()->create();
        $cancellationRequest = $this->createCancellationRequest();

        $this->assertTrue($admin->can('review', $cancellationRequest));
    }

    /**
     * 無効化された管理者はキャンセル申請を審査できない。
     */
    public function test_inactive_admin_cannot_review_cancellation_request(): void
    {
        $admin = User::factory()->admin()->create([
            'is_active' => false,
        ]);
        $cancellationRequest = $this->createCancellationRequest();

        $this->assertFalse($admin->can('review', $cancellationRequest));
    }

    /**
     * 依頼者と技術者はキャンセル申請を審査できない。
     */
    public function test_non_admin_cannot_review_cancellation_request(): void
    {
        $requester = User::factory()->requester()->create();
        $technician = User::factory()->technician()->create();
        $cancellationRequest = $this->createCancellationRequest();

        $this->assertFalse($requester->can('review', $cancellationRequest));
        $this->assertFalse($technician->can('review', $cancellationRequest));
    }

    /**
     * テスト用のキャンセル申請を作成する。
     */
    private function createCancellationRequest(): CancellationRequest
    {
        $requester = User::factory()->requester()->create();

        $repairRequest = $requester->repairRequests()->create([
            'title' => 'Policyテスト用の修理依頼',
            'description' => 'Policyテスト用の修理内容です。',
            'location' => 'テスト事務室',
            'asset_name' => 'テスト設備',
            'priority' => 'normal',
            'status' => 'received',
        ]);

        return CancellationRequest::query()->create([
            'repair_request_id' => $repairRequest->id,
            'requester_id' => $requester->id,
            'reason' => 'Policyテスト用のキャンセル理由です。',
            'status' => 'pending',
        ]);
    }
}
