<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ユーザー管理 | RepairFlow</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-100 text-slate-800">
    <main class="mx-auto max-w-7xl px-4 py-12">

        <section class="rounded-2xl bg-white p-8 shadow-sm">

            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold tracking-widest text-blue-600">
                        REPAIR MANAGEMENT SYSTEM
                    </p>

                    <h1 class="mt-3 text-3xl font-bold text-slate-900">
                        ユーザー管理
                    </h1>

                    <p class="mt-2 text-sm text-slate-500">
                        登録されているユーザーの確認・検索を行えます。
                    </p>
                </div>

                <a href="{{ route('dashboard') }}"
                    class="rounded-lg border border-slate-300 px-5 py-3
                    font-semibold text-slate-700 transition hover:bg-slate-50">
                    ダッシュボードへ戻る
                </a>
            </div>

            {{-- 検索・絞り込み --}}
            <form method="GET" action="{{ route('users.index') }}"
                class="mt-8 rounded-xl bg-slate-50 p-5">

                <div class="grid grid-cols-1 gap-4 md:grid-cols-4">

                    <div>
                        <label for="keyword"
                            class="mb-2 block text-sm font-semibold text-slate-700">
                            名前・メールアドレス
                        </label>

                        <input type="text"
                            id="keyword"
                            name="keyword"
                            value="{{ request('keyword') }}"
                            placeholder="キーワードを入力"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2
                            focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                    </div>

                    <div>
                        <label for="role"
                            class="mb-2 block text-sm font-semibold text-slate-700">
                            ロール
                        </label>

                        <select id="role" name="role"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2
                            focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">

                            <option value="">すべて</option>
                            <option value="requester" @selected(request('role') === 'requester')>
                                依頼者
                            </option>
                            <option value="technician" @selected(request('role') === 'technician')>
                                技術者
                            </option>
                            <option value="admin" @selected(request('role') === 'admin')>
                                管理者
                            </option>
                        </select>
                    </div>

                    <div>
                        <label for="is_active"
                            class="mb-2 block text-sm font-semibold text-slate-700">
                            アカウント状態
                        </label>

                        <select id="is_active" name="is_active"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2
                            focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">

                            <option value="">すべて</option>
                            <option value="1" @selected(request('is_active') === '1')>
                                有効
                            </option>
                            <option value="0" @selected(request('is_active') === '0')>
                                無効
                            </option>
                        </select>
                    </div>

                    <div class="flex items-end gap-2">
                        <button type="submit"
                            class="flex-1 rounded-lg bg-blue-600 px-4 py-2
                            font-semibold text-white transition hover:bg-blue-700">
                            検索
                        </button>

                        <a href="{{ route('users.index') }}"
                            class="rounded-lg border border-slate-300 px-4 py-2
                            font-semibold text-slate-700 transition hover:bg-white">
                            リセット
                        </a>
                    </div>

                </div>
            </form>

            {{-- ユーザー件数 --}}
            <div class="mt-8 flex flex-wrap items-center justify-between gap-2">
                <h2 class="text-lg font-bold text-slate-900">
                    ユーザー一覧
                </h2>

                <p class="text-sm text-slate-500">
                    全 {{ $users->total() }} 件
                </p>
            </div>

            {{-- ユーザー一覧テーブル --}}
            <div class="mt-4 overflow-x-auto">
                <table class="w-full min-w-[750px] text-left text-sm">

                    <thead class="bg-slate-100 text-slate-600">
                        <tr>
                            <th class="px-4 py-3 font-semibold">ID</th>
                            <th class="px-4 py-3 font-semibold">名前</th>
                            <th class="px-4 py-3 font-semibold">メールアドレス</th>
                            <th class="px-4 py-3 font-semibold">ロール</th>
                            <th class="px-4 py-3 font-semibold">状態</th>
                            <th class="px-4 py-3 font-semibold">登録日時</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-200">

                        @forelse ($users as $user)
                            <tr class="transition hover:bg-slate-50">

                                <td class="px-4 py-4 text-slate-500">
                                    {{ $user->id }}
                                </td>

                                <td class="px-4 py-4 font-semibold text-slate-900">
                                    {{ $user->name }}
                                </td>

                                <td class="px-4 py-4">
                                    {{ $user->email }}
                                </td>

                                <td class="px-4 py-4">
                                    @switch($user->role)
                                        @case('requester')
                                            依頼者
                                            @break
                                        @case('technician')
                                            技術者
                                            @break
                                        @case('admin')
                                            管理者
                                            @break
                                        @default
                                            不明
                                    @endswitch
                                </td>

                                <td class="px-4 py-4">
                                    @if ($user->is_active)
                                        <span class="rounded-full bg-green-100 px-3 py-1
                                            text-xs font-semibold text-green-700">
                                            有効
                                        </span>
                                    @else
                                        <span class="rounded-full bg-red-100 px-3 py-1
                                            text-xs font-semibold text-red-700">
                                            無効
                                        </span>
                                    @endif
                                </td>

                                <td class="px-4 py-4 text-slate-500">
                                    {{ $user->created_at->format('Y/m/d H:i') }}
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="6"
                                    class="px-4 py-12 text-center text-slate-500">
                                    該当するユーザーが見つかりませんでした。
                                </td>
                            </tr>
                        @endforelse

                    </tbody>
                </table>
            </div>

            {{-- ページネーション --}}
            <div class="mt-6">
                {{ $users->links() }}
            </div>

        </section>
    </main>
</body>

</html>
