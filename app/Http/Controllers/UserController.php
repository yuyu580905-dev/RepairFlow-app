<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\RepairRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * ユーザー管理を行う。
 */
class UserController extends Controller
{
    /**
     * ユーザー一覧を表示する。
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $users = User::query()
            ->when($request->filled('keyword'), function ($query) use ($request) {
                $keyword = $request->string('keyword')->trim()->toString();

                $query->where(function ($query) use ($keyword) {
                    $query->where('name', 'like', "%{$keyword}%")
                        ->orWhere('email', 'like', "%{$keyword}%");
                });
            })
            ->when($request->filled('role'), function ($query) use ($request) {
                $query->where('role', $request->input('role'));
            })
            ->when($request->filled('is_active'), function ($query) use ($request) {
                $query->where('is_active', $request->boolean('is_active'));
            })
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('users.index', compact('users'));
    }

    /**
     * ユーザー登録画面を表示する。
     */
    public function create(): View
    {
        $this->authorize('create', User::class);

        return view('users.create');
    }

    /**
     * ユーザーを登録する。
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = new User;

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->password = $validated['password'];
        $user->role = $validated['role'];
        $user->is_active = true;

        $user->save();

        return redirect()
            ->route('users.index')
            ->with('success', 'ユーザーを登録しました。');
    }

    /**
     * ユーザー編集画面を表示する。
     */
    public function edit(User $user): View
    {
        $this->authorize('update', $user);

        return view('users.edit', compact('user'));
    }

    /**
     * ユーザー情報を更新する。
     */
    public function update(
        UpdateUserRequest $request,
        User $user
    ): RedirectResponse {
        $this->authorize('update', $user);

        $validated = $request->validated();

        DB::transaction(function () use ($user, $validated) {
            // 有効な管理者をロックして取得する。
            $activeAdminIds = User::query()
                ->where('role', 'admin')
                ->where('is_active', true)
                ->orderBy('id')
                ->lockForUpdate()
                ->pluck('id');

            // 更新対象ユーザーをロックして取得する。
            $lockedUser = User::query()
                ->lockForUpdate()
                ->findOrFail($user->id);

            $willRemainActiveAdmin = $validated['role'] === 'admin'
                && (bool) $validated['is_active'];

            $isCurrentlyActiveAdmin = $lockedUser->role === 'admin'
                && $lockedUser->is_active;

            // 最後の有効な管理者を失う更新は禁止する。
            if (
                $isCurrentlyActiveAdmin
                && ! $willRemainActiveAdmin
                && $activeAdminIds->count() <= 1
            ) {
                throw ValidationException::withMessages([
                    'role' => '最後の有効な管理者は無効化できません。',
                ]);
            }

            // 担当中の修理依頼を持つ技術者の無効化・ロール変更を禁止する。
            $isRoleChanging = $validated['role'] !== $lockedUser->role;

            $isBeingDeactivated = $lockedUser->is_active
                && ! (bool) $validated['is_active'];

            $isTechnicianBeingChanged = $lockedUser->role === 'technician'
                && ($isRoleChanging || $isBeingDeactivated);

            if ($isTechnicianBeingChanged) {
                $hasActiveAssignedRequests = RepairRequest::query()
                    ->where('assigned_to', $lockedUser->id)
                    ->whereIn('status', ['received', 'in_progress', 'on_hold'])
                    ->exists();

                if ($hasActiveAssignedRequests) {
                    $errors = [];

                    if ($isRoleChanging) {
                        $errors['role'] = '担当中の修理依頼がある技術者のユーザー種別は変更できません。';
                    }

                    if ($isBeingDeactivated) {
                        $errors['is_active'] = '担当中の修理依頼がある技術者は無効化できません。';
                    }

                    throw ValidationException::withMessages($errors);
                }
            }

            // ユーザー情報を更新する。
            $lockedUser->name = $validated['name'];
            $lockedUser->email = $validated['email'];
            $lockedUser->role = $validated['role'];
            $lockedUser->is_active = $validated['is_active'];

            $lockedUser->save();
        });

        return redirect()
            ->route('users.index')
            ->with('success', 'ユーザー情報を更新しました。');
    }
}
