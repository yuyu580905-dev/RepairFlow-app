<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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
}
