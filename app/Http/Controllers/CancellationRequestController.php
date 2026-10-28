<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReviewCancellationRequestRequest;
use App\Http\Requests\StoreCancellationRequestRequest;
use App\Models\CancellationRequest;
use App\Models\RepairLog;
use App\Models\RepairRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class CancellationRequestController extends Controller
{
    /**
     * キャンセル申請を作成する。
     */
    public function store(
        StoreCancellationRequestRequest $request,
        RepairRequest $repairRequest
    ): RedirectResponse {
        $user = $request->user();

        abort_unless($user !== null, 403);

        DB::transaction(function () use ($repairRequest, $user, $request): void {
            $lockedRepairRequest = RepairRequest::query()
                ->whereKey($repairRequest->id)
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless(
                $user->is_active
                    && $user->role === 'requester'
                    && (int) $lockedRepairRequest->requester_id === (int) $user->id
                    && $lockedRepairRequest->status === 'received',
                403
            );

            $hasPendingRequest = CancellationRequest::query()
                ->where('repair_request_id', $lockedRepairRequest->id)
                ->where('status', 'pending')
                ->exists();

            if ($hasPendingRequest) {
                abort(422, 'この修理依頼には、すでに審査中のキャンセル申請があります。');
            }

            CancellationRequest::query()->create([
                'repair_request_id' => $lockedRepairRequest->id,
                'requester_id' => $user->id,
                'reason' => $request->validated('reason'),
                'status' => 'pending',
            ]);

            RepairLog::query()->create([
                'repair_request_id' => $lockedRepairRequest->id,
                'user_id' => $user->id,
                'action' => 'cancellation_requested',
                'message' => 'キャンセル申請を提出しました。',
                'from_status' => $lockedRepairRequest->status,
                'to_status' => $lockedRepairRequest->status,
            ]);
        });

        return redirect()
            ->route('repair-requests.show', $repairRequest)
            ->with('success', 'キャンセル申請を提出しました。管理者の審査をお待ちください。');
    }

    /**
     * キャンセル申請を審査する。
     */
    public function review(
        ReviewCancellationRequestRequest $request,
        CancellationRequest $cancellationRequest
    ): RedirectResponse {
        $user = $request->user();

        abort_unless($user !== null, 403);

        $repairRequestId = $cancellationRequest->repair_request_id;

        $result = DB::transaction(function () use (
            $cancellationRequest,
            $repairRequestId,
            $request,
            $user
        ): array {
            // 申請登録処理とロック順をそろえ、修理依頼を先にロックする。
            $repairRequest = RepairRequest::query()
                ->whereKey($repairRequestId)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedCancellationRequest = CancellationRequest::query()
                ->whereKey($cancellationRequest->id)
                ->lockForUpdate()
                ->firstOrFail();

            // 審査中の申請だけを処理する。
            abort_unless(
                $lockedCancellationRequest->status === 'pending',
                422,
                'このキャンセル申請は審査済みです。'
            );

            $decision = $request->validated('decision');
            $previousStatus = $repairRequest->status;

            // 承認する場合、修理依頼が受付状態であることを再確認する。
            if ($decision === 'approved') {
                abort_unless(
                    $repairRequest->status === 'received',
                    422,
                    '修理依頼が受付状態ではないため、キャンセルを承認できません。'
                );

                $repairRequest->update([
                    'status' => 'cancelled',
                ]);
            }

            $lockedCancellationRequest->update([
                'status' => $decision,
                'reviewed_by' => $user->id,
                'review_comment' => $request->validated('review_comment'),
                'reviewed_at' => now(),
            ]);

            $approved = $decision === 'approved';
            $newStatus = $approved ? 'cancelled' : $previousStatus;

            RepairLog::query()->create([
                'repair_request_id' => $repairRequest->id,
                'user_id' => $user->id,
                'action' => $approved
                    ? 'cancellation_approved'
                    : 'cancellation_rejected',
                'message' => $approved
                    ? 'キャンセル申請を承認しました。'
                    : 'キャンセル申請を却下しました。',
                'from_status' => $previousStatus,
                'to_status' => $newStatus,
            ]);

            return [
                'repair_request_id' => $repairRequest->id,
                'decision' => $decision,
            ];
        });

        $message = $result['decision'] === 'approved'
            ? 'キャンセル申請を承認しました。修理依頼をキャンセルしました。'
            : 'キャンセル申請を却下しました。';

        return redirect()
            ->route('repair-requests.show', $result['repair_request_id'])
            ->with('success', $message);
    }
}
