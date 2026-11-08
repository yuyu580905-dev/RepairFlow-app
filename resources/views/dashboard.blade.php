@extends('layouts.app')

@section('title', 'ダッシュボード')

@section('content')
    <div class="mx-auto max-w-5xl space-y-8">

        {{-- ページ見出し --}}
        <section>
            <p class="text-sm font-semibold tracking-widest text-blue-600">
                REPAIR MANAGEMENT SYSTEM
            </p>

            <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">
                ダッシュボード
            </h1>

            <p class="mt-3 text-sm text-slate-600">
                {{ auth()->user()->name }} さん、ログインしました。
            </p>
        </section>

        {{-- セッションメッセージ --}}
        @if (session('status'))
            <div class="rounded-xl border border-green-200 bg-green-50 p-4">
                <p class="font-semibold text-green-700">
                    {{ session('status') }}
                </p>
            </div>
        @endif

        {{-- メインコンテンツ --}}
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-xl font-bold text-slate-900">
                        ようこそ、RepairFlowへ
                    </h2>

                    <p class="mt-2 text-sm text-slate-600">
                        修理依頼や担当業務を確認できます。
                    </p>
                </div>

                <span
                    class="inline-flex w-fit items-center rounded-full bg-blue-50 px-3 py-1 text-sm font-semibold text-blue-700">
                    {{ match (auth()->user()->role) {
        'admin' => '管理者',
        'technician' => '技術者',
        'requester' => '依頼者',
        default => 'ユーザー',
    } }}
                </span>
            </div>

            <div class="mt-8 grid grid-cols-1 gap-4 sm:grid-cols-2">

                {{-- 修理依頼一覧 --}}
                <a href="{{ route('repair-requests.index') }}"
                    class="group rounded-xl border border-slate-200 p-5 transition hover:border-blue-300 hover:bg-blue-50">
                    <h3 class="font-bold text-slate-900 transition group-hover:text-blue-700">
                        修理依頼一覧
                    </h3>

                    <p class="mt-2 text-sm text-slate-600">
                        修理依頼の内容や対応状況を確認します。
                    </p>

                    <span class="mt-4 inline-block text-sm font-semibold text-blue-600">
                        一覧を確認する →
                    </span>
                </a>

                {{-- 管理者向け --}}
                @if (auth()->user()->role === 'admin')
                    <a href="{{ route('users.index') }}"
                        class="group rounded-xl border border-slate-200 p-5 transition hover:border-blue-300 hover:bg-blue-50">
                        <h3 class="font-bold text-slate-900 transition group-hover:text-blue-700">
                            ユーザー管理
                        </h3>

                        <p class="mt-2 text-sm text-slate-600">
                            ユーザーの登録・検索・情報編集を行います。
                        </p>

                        <span class="mt-4 inline-block text-sm font-semibold text-blue-600">
                            ユーザー管理へ →
                        </span>
                    </a>
                @endif

                {{-- 依頼者向け --}}
                @if (auth()->user()->role === 'requester')
                    <a href="{{ route('repair-requests.create') }}"
                        class="group rounded-xl border border-slate-200 p-5 transition hover:border-blue-300 hover:bg-blue-50">
                        <h3 class="font-bold text-slate-900 transition group-hover:text-blue-700">
                            修理依頼を登録
                        </h3>

                        <p class="mt-2 text-sm text-slate-600">
                            新しい修理依頼を申請します。
                        </p>

                        <span class="mt-4 inline-block text-sm font-semibold text-blue-600">
                            依頼を登録する →
                        </span>
                    </a>
                @endif

            </div>
        </section>

    </div>
@endsection