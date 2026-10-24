<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRepairRequestRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RepairRequestController extends Controller
{
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
