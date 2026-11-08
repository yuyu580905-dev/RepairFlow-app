
@extends('layouts.app')

@section('title', 'ユーザー管理')

@section('content')
    <div class="mx-auto max-w-7xl">
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-8">

            {{-- ページ見出し --}}
            <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-semibold tracking-widest text-blue-600">
                        REPAIR MANAGEMENT SYSTEM
                    </p>

                    <h1 class="mt-3 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
                        ユーザー管理
                    </h1>

                    <p class="mt-2 text-sm text-slate-500">
                        登録されているユーザーの確認・検索を行えます。
                    </p>
                </div>

                <a
                    href="{{ route('users.create') }}"
                    class="inline-flex w-full items-center justify-center rounded-lg bg-blue-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 sm:w-auto"
                >
                    ユーザーを登録する
                </a>
            </div>

            {{-- 登録成功メッセージ --}}
            @if (session('success'))
                <div
                    class="mt-6 rounded-xl border border-green-200 bg-green-50 p-4"
                    role="status"
                >
                    <p class="font-semibold text-green-700">
                        {{ session('success') }}
                    </p>
                </div>
            @endif

            {{-- 検索・絞り込み --}}
            <section class="mt-8 rounded-xl border border-slate-200 bg-slate-50 p-4 sm:p-5">
                <div class="mb-4">
                    <h2 class="text-base font-bold text-slate-900">
                        ユーザー検索
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        条件を指定してユーザーを絞り込めます。
                    </p>
                </div>

                <form
                    method="GET"
                    action="{{ route('users.index') }}"
                    class="space-y-4"
                    novalidate
                >
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

                        {{-- キーワード --}}
                        <div>
                            <label
                                for="keyword"
                                class="mb-2 block text-sm font-semibold text-slate-700"
                            >
                                名前・メールアドレス
                            </label>

                            <input
                                type="text"
                                id="keyword"
                                name="keyword"
                                value="{{ request('keyword') }}"
                                placeholder="キーワードを入力"
                                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                            >
                        </div>

                        {{-- ユーザー種別 --}}
                        <div>
                            <label
                                for="role"
                                class="mb-2 block text-sm font-semibold text-slate-700"
                            >
                                ユーザー種別
                            </label>

                            <select
                                id="role"
                                name="role"
                                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                            >
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

                        {{-- アカウント状態 --}}
                        <div>
                            <label
                                for="is_active"
                                class="mb-2 block text-sm font-semibold text-slate-700"
                            >
                                アカウント状態
                            </label>

                            <select
                                id="is_active"
                                name="is_active"
                                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                            >
                                <option value="">すべて</option>

                                <option value="1" @selected(request('is_active') === '1')>
                                    有効
                                </option>

                                <option value="0" @selected(request('is_active') === '0')>
                                    無効
                                </option>
                            </select>
                        </div>

                        {{-- 検索ボタン --}}
                        <div class="flex items-end gap-2">
                            <button
                                type="submit"
                                class="flex-1 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                            >
                                検索
                            </button>

                            <a
                                href="{{ route('users.index') }}"
                                class="flex-1 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-center text-sm font-semibold text-slate-700 transition hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-400 focus:ring-offset-2"
                            >
                                リセット
                            </a>
                        </div>
                    </div>
                </form>
            </section>

            {{-- ユーザー一覧 --}}
            <section class="mt-8">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex flex-wrap items-center gap-3">
                        <h2 class="text-lg font-bold text-slate-900">
                            ユーザー一覧
                        </h2>

                        <span class="rounded-full bg-slate-100 px-3 py-1 text-sm font-semibold text-slate-600">
                            全 {{ $users->total() }} 件
                        </span>
                    </div>
                </div>

                {{-- テーブル --}}
                <div class="mt-4 overflow-x-auto rounded-xl border border-slate-200">
                    <table class="w-full min-w-[900px] text-left text-sm">
                        <thead class="bg-slate-100 text-slate-600">
                            <tr>
                                <th class="whitespace-nowrap px-4 py-4 font-semibold">
                                    ID
                                </th>

                                <th class="whitespace-nowrap px-4 py-4 font-semibold">
                                    名前
                                </th>

                                <th class="whitespace-nowrap px-4 py-4 font-semibold">
                                    メールアドレス
                                </th>

                                <th class="whitespace-nowrap px-4 py-4 font-semibold">
                                    ユーザー種別
                                </th>

                                <th class="whitespace-nowrap px-4 py-4 font-semibold">
                                    状態
                                </th>

                                <th class="whitespace-nowrap px-4 py-4 font-semibold">
                                    登録日時
                                </th>

                                <th class="whitespace-nowrap px-4 py-4 font-semibold">
                                    操作
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-200">
                            @forelse ($users as $user)
                                <tr class="transition hover:bg-slate-50">

                                    <td class="whitespace-nowrap px-4 py-4 text-slate-500">
                                        {{ $user->id }}
                                    </td>

                                    <td class="whitespace-nowrap px-4 py-4 font-semibold text-slate-900">
                                        {{ $user->name }}
                                    </td>

                                    <td class="whitespace-nowrap px-4 py-4 text-slate-700">
                                        {{ $user->email }}
                                    </td>

                                    <td class="whitespace-nowrap px-4 py-4">
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

                                    <td class="whitespace-nowrap px-4 py-4">
                                        @if ($user->is_active)
                                            <span class="inline-flex rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                                                有効
                                            </span>
                                        @else
                                            <span class="inline-flex rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">
                                                無効
                                            </span>
                                        @endif
                                    </td>

                                    <td class="whitespace-nowrap px-4 py-4 text-slate-500">
                                        {{ $user->created_at->copy()->timezone('Asia/Tokyo')->format('Y/m/d H:i') }}
                                    </td>

                                    <td class="whitespace-nowrap px-4 py-4">
                                        <a
                                            href="{{ route('users.edit', $user) }}"
                                            class="font-semibold text-blue-600 transition hover:text-blue-800 focus:outline-none focus:underline"
                                        >
                                            編集
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td
                                        colspan="7"
                                        class="px-4 py-12 text-center text-slate-500"
                                    >
                                        該当するユーザーが見つかりませんでした。
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- ページネーション --}}
                @if ($users->hasPages())
                    <div class="mt-6">
                        {{ $users->links() }}
                    </div>
                @endif
            </section>
        </section>
    </div>
@endsection
