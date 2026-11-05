<?php

namespace Tests\Feature;

use App\Models\CancellationRequest;
use App\Models\RepairRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CancellationRequestTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 依頼者は自分の受付済み修理依頼にキャンセル申請できる。
     */
    public function test_requester_can_submit_cancellation_request(): void
    {
        $requester = User::factory()
            ->requester()
            ->create();

        $repairRequest = RepairRequest::factory()
            ->for($requester, 'requester')
            ->received()
            ->create();

        $response = $this->actingAs($requester)->post(
            route('repair-requests.cancellation-requests.store', $repairRequest),
            ['reason' => '誤った設備を選択してしまいました。']
        );

        $response
            ->assertRedirect(route('repair-requests.show', $repairRequest))
            ->assertSessionHas(
                'success',
                'キャンセル申請を提出しました。管理者の審査をお待ちください。'
            );

        $this->assertDatabaseHas('cancellation_requests', [
            'repair_request_id' => $repairRequest->id,
            'requester_id' => $requester->id,
            'reason' => '誤った設備を選択してしまいました。',
            'status' => 'pending',
            'reviewed_by' => null,
            'reviewed_at' => null,
        ]);

        $this->assertDatabaseHas('repair_logs', [
            'repair_request_id' => $repairRequest->id,
            'user_id' => $requester->id,
            'action' => 'cancellation_requested',
            'message' => 'キャンセル申請を提出しました。',
            'from_status' => 'received',
            'to_status' => 'received',
        ]);
    }

    /**
     * キャンセル理由が未入力の場合は申請できない。
     */
    public function test_cancellation_reason_is_required(): void
    {
        $requester = User::factory()
            ->requester()
            ->create();

        $repairRequest = RepairRequest::factory()
            ->for($requester, 'requester')
            ->received()
            ->create();

        $response = $this->actingAs($requester)->post(
            route('repair-requests.cancellation-requests.store', $repairRequest),
            ['reason' => '']
        );

        $response->assertSessionHasErrors('reason');

        $this->assertDatabaseCount('cancellation_requests', 0);
        $this->assertDatabaseCount('repair_logs', 0);
    }

    /**
     * 依頼者は他人の修理依頼にキャンセル申請できない。
     */
    public function test_requester_cannot_cancel_another_users_repair_request(): void
    {
        $requester = User::factory()
            ->requester()
            ->create();

        $repairRequest = RepairRequest::factory()
            ->for(User::factory()->requester(), 'requester')
            ->received()
            ->create();

        $response = $this->actingAs($requester)->post(
            route('repair-requests.cancellation-requests.store', $repairRequest),
            ['reason' => 'キャンセルしたい理由です。']
        );

        $response->assertForbidden();

        $this->assertDatabaseCount('cancellation_requests', 0);
    }

    /**
     * 受付済み以外の修理依頼にはキャンセル申請できない。
     */
    public function test_requester_cannot_cancel_repair_that_is_not_received(): void
    {
        $requester = User::factory()
            ->requester()
            ->create();

        $repairRequest = RepairRequest::factory()
            ->for($requester, 'requester')
            ->inProgress()
            ->create();

        $response = $this->actingAs($requester)->post(
            route('repair-requests.cancellation-requests.store', $repairRequest),
            ['reason' => 'キャンセルしたい理由です。']
        );

        $response->assertForbidden();

        $this->assertDatabaseCount('cancellation_requests', 0);
    }

    /**
     * 技術者はキャンセル申請を提出できない。
     */
    public function test_technician_cannot_submit_cancellation_request(): void
    {
        $technician = User::factory()
            ->technician()
            ->create();

        $repairRequest = RepairRequest::factory()
            ->for(User::factory()->requester(), 'requester')
            ->received()
            ->create();

        $response = $this->actingAs($technician)->post(
            route('repair-requests.cancellation-requests.store', $repairRequest),
            ['reason' => 'キャンセルしたい理由です。']
        );

        $response->assertForbidden();

        $this->assertDatabaseCount('cancellation_requests', 0);
    }

    /**
     * 未ログインユーザーはキャンセル申請できない。
     */
    public function test_guest_cannot_submit_cancellation_request(): void
    {
        $repairRequest = RepairRequest::factory()
            ->for(User::factory()->requester(), 'requester')
            ->received()
            ->create();

        $response = $this->post(
            route('repair-requests.cancellation-requests.store', $repairRequest),
            ['reason' => 'キャンセルしたい理由です。']
        );

        $response->assertRedirect(route('login'));

        $this->assertDatabaseCount('cancellation_requests', 0);
    }

    /**
     * 審査中の申請が存在する場合、重複申請できない。
     */
    public function test_requester_cannot_submit_duplicate_pending_cancellation_request(): void
    {
        $requester = User::factory()
            ->requester()
            ->create();

        $repairRequest = RepairRequest::factory()
            ->for($requester, 'requester')
            ->received()
            ->create();

        CancellationRequest::factory()
            ->for($repairRequest, 'repairRequest')
            ->pending()
            ->create([
                'reason' => '最初の申請です。',
            ]);

        $response = $this->actingAs($requester)->post(
            route('repair-requests.cancellation-requests.store', $repairRequest),
            ['reason' => '重複した申請です。']
        );

        $response->assertStatus(422);

        $this->assertDatabaseCount('cancellation_requests', 1);
        $this->assertDatabaseCount('repair_logs', 0);
    }

    /**
     * 管理者はキャンセル申請を承認し、修理依頼をキャンセルできる。
     */
    public function test_admin_can_approve_cancellation_request(): void
    {
        $repairRequest = RepairRequest::factory()
            ->for(User::factory()->requester(), 'requester')
            ->received()
            ->create();

        $requester = $repairRequest->requester;

        $cancellationRequest = CancellationRequest::factory()
            ->for($repairRequest, 'repairRequest')
            ->pending()
            ->create();

        $admin = User::factory()
            ->admin()
            ->create();

        $response = $this->actingAs($admin)->post(
            route('cancellation-requests.review', $cancellationRequest),
            [
                'decision' => 'approved',
                'review_comment' => 'キャンセルを承認します。',
            ]
        );

        $response
            ->assertRedirect(route('repair-requests.show', $repairRequest))
            ->assertSessionHas(
                'success',
                'キャンセル申請を承認しました。修理依頼をキャンセルしました。'
            );

        $this->assertDatabaseHas('cancellation_requests', [
            'id' => $cancellationRequest->id,
            'status' => 'approved',
            'reviewed_by' => $admin->id,
            'review_comment' => 'キャンセルを承認します。',
        ]);

        $this->assertNotNull(
            CancellationRequest::query()->findOrFail($cancellationRequest->id)->reviewed_at
        );

        $this->assertDatabaseHas('repair_requests', [
            'id' => $repairRequest->id,
            'status' => 'cancelled',
        ]);

        $this->assertDatabaseHas('repair_logs', [
            'repair_request_id' => $repairRequest->id,
            'user_id' => $admin->id,
            'action' => 'cancellation_approved',
            'message' => 'キャンセル申請を承認しました。',
            'from_status' => 'received',
            'to_status' => 'cancelled',
        ]);
    }

    /**
     * 管理者はキャンセル申請を却下でき、修理依頼は受付済みのまま維持される。
     */
    public function test_admin_can_reject_cancellation_request(): void
    {
        $repairRequest = RepairRequest::factory()
            ->for(User::factory()->requester(), 'requester')
            ->received()
            ->create();

        $cancellationRequest = CancellationRequest::factory()
            ->for($repairRequest, 'repairRequest')
            ->pending()
            ->create();

        $admin = User::factory()
            ->admin()
            ->create();

        $response = $this->actingAs($admin)->post(
            route('cancellation-requests.review', $cancellationRequest),
            [
                'decision' => 'rejected',
                'review_comment' => '申請理由の確認が必要です。',
            ]
        );

        $response
            ->assertRedirect(route('repair-requests.show', $repairRequest))
            ->assertSessionHas('success', 'キャンセル申請を却下しました。');

        $this->assertDatabaseHas('cancellation_requests', [
            'id' => $cancellationRequest->id,
            'status' => 'rejected',
            'reviewed_by' => $admin->id,
            'review_comment' => '申請理由の確認が必要です。',
        ]);

        $this->assertDatabaseHas('repair_requests', [
            'id' => $repairRequest->id,
            'status' => 'received',
        ]);

        $this->assertDatabaseHas('repair_logs', [
            'repair_request_id' => $repairRequest->id,
            'user_id' => $admin->id,
            'action' => 'cancellation_rejected',
            'message' => 'キャンセル申請を却下しました。',
            'from_status' => 'received',
            'to_status' => 'received',
        ]);
    }

    /**
     * 却下理由が未入力の場合はキャンセル申請を却下できない。
     */
    public function test_admin_cannot_reject_cancellation_request_without_review_comment(): void
    {
        $repairRequest = RepairRequest::factory()
            ->for(User::factory()->requester(), 'requester')
            ->received()
            ->create();

        $cancellationRequest = CancellationRequest::factory()
            ->for($repairRequest, 'repairRequest')
            ->pending()
            ->create();

        $admin = User::factory()
            ->admin()
            ->create();

        $response = $this->actingAs($admin)->post(
            route('cancellation-requests.review', $cancellationRequest),
            [
                'decision' => 'rejected',
                'review_comment' => '',
            ]
        );

        $response->assertSessionHasErrors('review_comment');

        $this->assertDatabaseHas('cancellation_requests', [
            'id' => $cancellationRequest->id,
            'status' => 'pending',
            'reviewed_by' => null,
            'review_comment' => null,
        ]);

        $this->assertDatabaseCount('repair_logs', 0);
    }

    /**
     * 管理者は審査コメントなしでもキャンセル申請を承認できる。
     */
    public function test_admin_can_approve_cancellation_request_without_review_comment(): void
    {
        $repairRequest = RepairRequest::factory()
            ->for(User::factory()->requester(), 'requester')
            ->received()
            ->create();

        $cancellationRequest = CancellationRequest::factory()
            ->for($repairRequest, 'repairRequest')
            ->pending()
            ->create();

        $admin = User::factory()
            ->admin()
            ->create();

        $response = $this->actingAs($admin)->post(
            route('cancellation-requests.review', $cancellationRequest),
            ['decision' => 'approved']
        );

        $response
            ->assertRedirect(route('repair-requests.show', $repairRequest))
            ->assertSessionHas('success', 'キャンセル申請を承認しました。修理依頼をキャンセルしました。');

        $this->assertDatabaseHas('cancellation_requests', [
            'id' => $cancellationRequest->id,
            'status' => 'approved',
            'reviewed_by' => $admin->id,
            'review_comment' => null,
        ]);

        $this->assertDatabaseHas('repair_requests', [
            'id' => $repairRequest->id,
            'status' => 'cancelled',
        ]);
    }

    /**
     * 管理者以外はキャンセル申請を審査できない。
     */
    public function test_non_admin_cannot_review_cancellation_request(): void
    {
        $repairRequest = RepairRequest::factory()
            ->for(User::factory()->requester(), 'requester')
            ->received()
            ->create();

        $cancellationRequest = CancellationRequest::factory()
            ->for($repairRequest, 'repairRequest')
            ->pending()
            ->create();

        foreach (['requester', 'technician'] as $role) {
            $user = match ($role) {
                'requester' => User::factory()->requester()->create(),
                'technician' => User::factory()->technician()->create(),
            };

            $response = $this->actingAs($user)->post(
                route('cancellation-requests.review', $cancellationRequest),
                [
                    'decision' => 'approved',
                    'review_comment' => 'テスト審査です。',
                ]
            );

            $response->assertForbidden();
        }

        $this->assertDatabaseHas('cancellation_requests', [
            'id' => $cancellationRequest->id,
            'status' => 'pending',
            'reviewed_by' => null,
        ]);

        $this->assertDatabaseCount('repair_logs', 0);
    }

    /**
     * 不正な審査結果は受け付けない。
     */
    public function test_invalid_review_decision_is_rejected(): void
    {
        $repairRequest = RepairRequest::factory()
            ->for(User::factory()->requester(), 'requester')
            ->received()
            ->create();

        $cancellationRequest = CancellationRequest::factory()
            ->for($repairRequest, 'repairRequest')
            ->pending()
            ->create();

        $admin = User::factory()
            ->admin()
            ->create();

        $response = $this->actingAs($admin)->post(
            route('cancellation-requests.review', $cancellationRequest),
            ['decision' => 'pending']
        );

        $response->assertSessionHasErrors('decision');

        $this->assertDatabaseHas('cancellation_requests', [
            'id' => $cancellationRequest->id,
            'status' => 'pending',
        ]);

        $this->assertDatabaseCount('repair_logs', 0);
    }

    /**
     * 審査済みのキャンセル申請を再審査できない。
     */
    public function test_reviewed_cancellation_request_cannot_be_reviewed_again(): void
    {
        $repairRequest = RepairRequest::factory()
            ->for(User::factory()->requester(), 'requester')
            ->received()
            ->create();

        $cancellationRequest = CancellationRequest::factory()
            ->for($repairRequest, 'repairRequest')
            ->pending()
            ->create();

        $admin = User::factory()
            ->admin()
            ->create();

        $cancellationRequest->update([
            'status' => 'rejected',
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
        ]);

        $response = $this->actingAs($admin)->post(
            route('cancellation-requests.review', $cancellationRequest),
            ['decision' => 'approved']
        );

        $response->assertStatus(422);

        $this->assertDatabaseHas('repair_requests', [
            'id' => $repairRequest->id,
            'status' => 'received',
        ]);

        $this->assertDatabaseCount('repair_logs', 0);
    }

    /**
     * 却下後は、受付済みの修理依頼に再申請できる。
     */
    public function test_requester_can_resubmit_after_cancellation_request_is_rejected(): void
    {
        $repairRequest = RepairRequest::factory()
            ->for(User::factory()->requester(), 'requester')
            ->received()
            ->create();

        $requester = $repairRequest->requester;

        $cancellationRequest = CancellationRequest::factory()
            ->for($repairRequest, 'repairRequest')
            ->pending()
            ->create();

        $admin = User::factory()
            ->admin()
            ->create();

        $cancellationRequest->update([
            'status' => 'rejected',
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
        ]);

        $response = $this->actingAs($requester)->post(
            route('repair-requests.cancellation-requests.store', $repairRequest),
            ['reason' => '事情が変わったため、再度キャンセルを申請します。']
        );

        $response->assertRedirect(route('repair-requests.show', $repairRequest));

        $this->assertDatabaseCount('cancellation_requests', 2);

        $this->assertDatabaseHas('cancellation_requests', [
            'repair_request_id' => $repairRequest->id,
            'requester_id' => $requester->id,
            'reason' => '事情が変わったため、再度キャンセルを申請します。',
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('repair_logs', [
            'repair_request_id' => $repairRequest->id,
            'action' => 'cancellation_requested',
        ]);
    }

    /**
     * 管理者はキャンセル申請を提出できない。
     */
    public function test_admin_cannot_submit_cancellation_request(): void
    {
        $admin = User::factory()
            ->admin()
            ->create();

        $repairRequest = RepairRequest::factory()
            ->for(User::factory()->requester(), 'requester')
            ->received()
            ->create();

        $response = $this->actingAs($admin)->post(
            route('repair-requests.cancellation-requests.store', $repairRequest),
            ['reason' => '管理者による申請です。']
        );

        $response->assertForbidden();

        $this->assertDatabaseCount('cancellation_requests', 0);
    }

    /**
     * 無効化された依頼者はキャンセル申請できない。
     */
    public function test_inactive_requester_cannot_submit_cancellation_request(): void
    {
        $requester = User::factory()
            ->requester()
            ->create();

        $repairRequest = RepairRequest::factory()
            ->for($requester, 'requester')
            ->received()
            ->create();

        $requester->forceFill(['is_active' => false])->save();

        $response = $this->actingAs($requester)->post(
            route('repair-requests.cancellation-requests.store', $repairRequest),
            ['reason' => 'キャンセルしたい理由です。']
        );

        $response->assertForbidden();

        $this->assertDatabaseCount('cancellation_requests', 0);
    }

    /**
     * キャンセル理由が2000文字を超える場合は申請できない。
     */
    public function test_cancellation_reason_cannot_exceed_2000_characters(): void
    {
        $requester = User::factory()
            ->requester()
            ->create();

        $repairRequest = RepairRequest::factory()
            ->for($requester, 'requester')
            ->received()
            ->create();

        $response = $this->actingAs($requester)->post(
            route('repair-requests.cancellation-requests.store', $repairRequest),
            ['reason' => str_repeat('あ', 2001)]
        );

        $response->assertSessionHasErrors('reason');

        $this->assertDatabaseCount('cancellation_requests', 0);
    }

    /**
     * 無効化された管理者はキャンセル申請を審査できない。
     */
    public function test_inactive_admin_cannot_review_cancellation_request(): void
    {
        $repairRequest = RepairRequest::factory()
            ->for(User::factory()->requester(), 'requester')
            ->received()
            ->create();

        $cancellationRequest = CancellationRequest::factory()
            ->for($repairRequest, 'repairRequest')
            ->pending()
            ->create();

        $admin = User::factory()
            ->admin()
            ->create();

        $admin->forceFill(['is_active' => false])->save();

        $response = $this->actingAs($admin)->post(
            route('cancellation-requests.review', $cancellationRequest),
            ['decision' => 'approved']
        );

        $response->assertForbidden();

        $this->assertDatabaseHas('cancellation_requests', [
            'id' => $cancellationRequest->id,
            'status' => 'pending',
            'reviewed_by' => null,
        ]);

        $this->assertDatabaseCount('repair_logs', 0);
    }

    /**
     * 審査コメントが2000文字を超える場合は審査できない。
     */
    public function test_review_comment_cannot_exceed_2000_characters(): void
    {
        $repairRequest = RepairRequest::factory()
            ->for(User::factory()->requester(), 'requester')
            ->received()
            ->create();

        $cancellationRequest = CancellationRequest::factory()
            ->for($repairRequest, 'repairRequest')
            ->pending()
            ->create();

        $admin = User::factory()
            ->admin()
            ->create();

        $response = $this->actingAs($admin)->post(
            route('cancellation-requests.review', $cancellationRequest),
            [
                'decision' => 'rejected',
                'review_comment' => str_repeat('あ', 2001),
            ]
        );

        $response->assertSessionHasErrors('review_comment');

        $this->assertDatabaseHas('cancellation_requests', [
            'id' => $cancellationRequest->id,
            'status' => 'pending',
            'reviewed_by' => null,
        ]);

        $this->assertDatabaseCount('repair_logs', 0);
    }

    /**
     * 受付済み以外の修理依頼はキャンセル承認できない。
     */
    public function test_admin_cannot_approve_cancellation_for_non_received_repair(): void
    {
        $repairRequest = RepairRequest::factory()
            ->for(User::factory()->requester(), 'requester')
            ->inProgress()
            ->create();

        $cancellationRequest = CancellationRequest::factory()
            ->for($repairRequest, 'repairRequest')
            ->pending()
            ->create([
                'reason' => 'テスト用のキャンセル理由です。',
            ]);

        $admin = User::factory()
            ->admin()
            ->create();

        $response = $this->actingAs($admin)->post(
            route('cancellation-requests.review', $cancellationRequest),
            [
                'decision' => 'approved',
                'review_comment' => '承認を試みます。',
            ]
        );

        $response->assertStatus(422);

        $this->assertDatabaseHas('repair_requests', [
            'id' => $repairRequest->id,
            'status' => 'in_progress',
        ]);

        $this->assertDatabaseHas('cancellation_requests', [
            'id' => $cancellationRequest->id,
            'status' => 'pending',
            'reviewed_by' => null,
        ]);

        $this->assertDatabaseCount('repair_logs', 0);
    }
}
