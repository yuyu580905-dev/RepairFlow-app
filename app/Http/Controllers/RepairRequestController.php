<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRepairRequestRequest;
use App\Models\RepairRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        ]);

        return view('repair-requests.show', [
            'repairRequest' => $repairRequest,
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
}
