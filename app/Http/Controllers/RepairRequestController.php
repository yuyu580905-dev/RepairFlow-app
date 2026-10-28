<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssignRepairTechnicianRequest;
use App\Http\Requests\StoreRepairRequestRequest;
use App\Http\Requests\UpdateRepairRequestStatusRequest;
use App\Models\RepairLog;
use App\Models\RepairRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RepairRequestController extends Controller
{
    /**
     * 修理依頼の一覧を表示する。
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        abort_unless($user !== null, 403);

        $query = RepairRequest::query()
            ->with(['requester', 'assignedTechnician'])
            ->latest();

        if ($user->role === 'requester') {
            $query->where('requester_id', $user->id);
        } elseif ($user->role === 'technician') {
            $query->where('assigned_to', $user->id);
        } elseif ($user->role !== 'admin') {
            abort(403);
        }

        $repairRequests = $query->paginate(10);

        return view('repair-requests.index', [
            'repairRequests' => $repairRequests,
        ]);
    }

    /**
     * 修理依頼の詳細を表示する。
     */
    public function show(
        Request $request,
        RepairRequest $repairRequest
    ): View {
        $user = $request->user();

        abort_unless($user !== null, 403);

        $canView = match ($user->role) {
            'requester' => $repairRequest->requester_id === $user->id,
            'technician' => $repairRequest->assigned_to === $user->id,
            'admin' => true,
            default => false,
        };

        abort_unless($canView, 403);

        $repairRequest->load([
            'requester',
            'assignedTechnician',
            'repairLogs.user',
            'cancellationRequests.requester',
            'cancellationRequests.reviewer',
        ]);

        $technicians = User::query()
            ->where('role', 'technician')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('repair-requests.show', [
            'repairRequest' => $repairRequest,
            'technicians' => $technicians,
        ]);
    }

    /**
     * 修理依頼の登録画面を表示する。
     */
    public function create(): View
    {
        return view('repair-requests.create');
    }

    /**
     * 修理依頼を登録する。
     */
    public function store(
        StoreRepairRequestRequest $request
    ): RedirectResponse {
        $request->user()->repairRequests()->create([
            ...$request->validated(),
            'assigned_to' => null,
            'status' => 'received',
        ]);

        return redirect()
            ->route('dashboard')
            ->with('status', '修理依頼を登録しました。');
    }

    /**
     * 修理依頼の担当技術者を割り当てる。
     */
    public function assignTechnician(
        AssignRepairTechnicianRequest $request,
        RepairRequest $repairRequest
    ): RedirectResponse {
        $validated = $request->validated();

        DB::transaction(function () use (
            $validated,
            $repairRequest,
            $request
        ): void {
            $lockedRepairRequest = RepairRequest::query()
                ->whereKey($repairRequest->id)
                ->lockForUpdate()
                ->firstOrFail();

            $technician = User::query()
                ->whereKey($validated['assigned_to'])
                ->where('role', 'technician')
                ->where('is_active', true)
                ->lockForUpdate()
                ->first();

            if ($technician === null) {
                throw ValidationException::withMessages([
                    'assigned_to' => '有効な技術者を選択してください。',
                ]);
            }

            $lockedRepairRequest->update([
                'assigned_to' => $technician->id,
            ]);

            RepairLog::create([
                'repair_request_id' => $lockedRepairRequest->id,
                'user_id' => $request->user()->id,
                'action' => 'technician_assigned',
                'message' => "担当技術者を {$technician->name} に割り当てました。",
                'from_status' => null,
                'to_status' => null,
            ]);
        });

        return redirect()
            ->route('repair-requests.show', $repairRequest)
            ->with('status', '担当技術者を割り当てました。');
    }

    /**
     * 修理依頼のステータスを更新し、履歴を追加する。
     */
    public function updateStatus(
        UpdateRepairRequestStatusRequest $request,
        RepairRequest $repairRequest
    ): RedirectResponse {
        $validated = $request->validated();
        $user = $request->user();

        DB::transaction(function () use (
            $validated,
            $repairRequest,
            $user
        ): void {
            $lockedRepairRequest = RepairRequest::query()
                ->whereKey($repairRequest->id)
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless(
                $user !== null
                    && $user->role === 'technician'
                    && $user->is_active
                    && (int) $lockedRepairRequest->assigned_to === (int) $user->id,
                403
            );

            $fromStatus = $lockedRepairRequest->status;
            $toStatus = $validated['status'];

            $allowedTransitions = [
                'received' => ['in_progress', 'on_hold'],
                'in_progress' => ['on_hold', 'completed'],
                'on_hold' => ['in_progress'],
            ];

            if (! in_array(
                $toStatus,
                $allowedTransitions[$fromStatus] ?? [],
                true
            )) {
                throw ValidationException::withMessages([
                    'status' => '現在のステータスから指定された状態には変更できません。',
                ]);
            }

            $lockedRepairRequest->update([
                'status' => $toStatus,
            ]);

            $statusLabels = [
                'received' => '受付済み',
                'in_progress' => '対応中',
                'on_hold' => '保留中',
                'completed' => '完了',
                'cancelled' => 'キャンセル済み',
            ];

            RepairLog::create([
                'repair_request_id' => $lockedRepairRequest->id,
                'user_id' => $user->id,
                'action' => 'status_changed',
                'message' => $validated['message']
                    ?? sprintf(
                        'ステータスを「%s」から「%s」に変更しました。',
                        $statusLabels[$fromStatus] ?? $fromStatus,
                        $statusLabels[$toStatus] ?? $toStatus,
                    ),
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
            ]);
        });

        return redirect()
            ->route('repair-requests.show', $repairRequest)
            ->with('status', 'ステータスを更新しました。');
    }
}
