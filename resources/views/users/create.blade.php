
@extends('layouts.app')

@section('title', 'ユーザー登録')

@section('content')
    <div class="mx-auto max-w-3xl">
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-8">

            {{-- ページ見出し --}}
            <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-semibold tracking-widest text-blue-600">
                        REPAIR MANAGEMENT SYSTEM
                    </p>

                    <h1 class="mt-3 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
                        ユーザー登録
                    </h1>

                    <p class="mt-2 text-sm text-slate-500">
                        新しいユーザーの情報を入力してください。
                    </p>
                </div>

                <a
                    href="{{ route('users.index') }}"
                    class="inline-flex w-full items-center justify-center rounded-lg border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-400 focus:ring-offset-2 sm:w-auto"
                >
                    ユーザー一覧へ戻る
                </a>
            </div>

            {{-- バリデーションエラー --}}
            @if ($errors->any())
                <div
                    class="mt-8 rounded-xl border border-red-200 bg-red-50 p-5"
                    role="alert"
                >
                    <p class="font-semibold text-red-700">
                        入力内容に問題があります。
                    </p>

                    <ul class="mt-2 list-inside list-disc space-y-1 text-sm text-red-700">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- ユーザー登録フォーム --}}
            <form
                method="POST"
                action="{{ route('users.store') }}"
                class="mt-8 space-y-6"
                novalidate
            >
                @csrf

                {{-- 名前 --}}
                <div>
                    <label for="name" class="mb-2 block text-sm font-semibold text-slate-700">
                        名前
                        <span class="ml-1 text-red-600">必須</span>
                    </label>

                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name') }}"
                        required
                        maxlength="255"
                        autocomplete="name"
                        aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}"
                        @error('name') aria-describedby="name-error" @enderror
                        class="w-full rounded-lg border border-slate-300 px-3 py-3 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100 @error('name') border-red-500 focus:border-red-500 focus:ring-red-100 @enderror"
                    >

                    @error('name')
                        <p id="name-error" class="mt-2 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- メールアドレス --}}
                <div>
                    <label for="email" class="mb-2 block text-sm font-semibold text-slate-700">
                        メールアドレス
                        <span class="ml-1 text-red-600">必須</span>
                    </label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        maxlength="255"
                        autocomplete="email"
                        aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                        @error('email') aria-describedby="email-error" @enderror
                        class="w-full rounded-lg border border-slate-300 px-3 py-3 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100 @error('email') border-red-500 focus:border-red-500 focus:ring-red-100 @enderror"
                    >

                    @error('email')
                        <p id="email-error" class="mt-2 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- パスワード --}}
                <div>
                    <label for="password" class="mb-2 block text-sm font-semibold text-slate-700">
                        パスワード
                        <span class="ml-1 text-red-600">必須</span>
                    </label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        minlength="8"
                        autocomplete="new-password"
                        aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                        aria-describedby="password-help"
                        class="w-full rounded-lg border border-slate-300 px-3 py-3 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100 @error('password') border-red-500 focus:border-red-500 focus:ring-red-100 @enderror"
                    >

                    <p id="password-help" class="mt-2 text-xs text-slate-500">
                        8文字以上で入力してください。
                    </p>

                    @error('password')
                        <p class="mt-2 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- パスワード確認 --}}
                <div>
                    <label
                        for="password_confirmation"
                        class="mb-2 block text-sm font-semibold text-slate-700"
                    >
                        パスワード（確認）
                        <span class="ml-1 text-red-600">必須</span>
                    </label>

                    <input
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        required
                        minlength="8"
                        autocomplete="new-password"
                        class="w-full rounded-lg border border-slate-300 px-3 py-3 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                    >
                </div>

                {{-- ユーザー種別 --}}
                <div>
                    <label for="role" class="mb-2 block text-sm font-semibold text-slate-700">
                        ユーザー種別
                        <span class="ml-1 text-red-600">必須</span>
                    </label>

                    <select
                        id="role"
                        name="role"
                        required
                        aria-invalid="{{ $errors->has('role') ? 'true' : 'false' }}"
                        @error('role') aria-describedby="role-error" @enderror
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-3 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100 @error('role') border-red-500 focus:border-red-500 focus:ring-red-100 @enderror"
                    >
                        <option value="">選択してください</option>

                        <option value="requester" @selected(old('role') === 'requester')>
                            依頼者
                        </option>

                        <option value="technician" @selected(old('role') === 'technician')>
                            技術者
                        </option>

                        <option value="admin" @selected(old('role') === 'admin')>
                            管理者
                        </option>
                    </select>

                    @error('role')
                        <p id="role-error" class="mt-2 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- アカウント状態 --}}
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-5">
                    <div class="flex flex-wrap items-center gap-3">
                        <p class="font-semibold text-slate-700">
                            アカウント状態
                        </p>

                        <span class="inline-flex rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                            有効
                        </span>
                    </div>

                    <p class="mt-2 text-sm leading-6 text-slate-600">
                        登録時は自動的に有効なアカウントとして作成されます。
                    </p>
                </div>

                {{-- 操作ボタン --}}
                <div class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-6 sm:flex-row sm:justify-end">
                    <a
                        href="{{ route('users.index') }}"
                        class="inline-flex w-full items-center justify-center rounded-lg border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-400 focus:ring-offset-2 sm:w-auto"
                    >
                        キャンセル
                    </a>

                    <button
                        type="submit"
                        class="inline-flex w-full items-center justify-center rounded-lg bg-blue-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 sm:w-auto"
                    >
                        ユーザーを登録
                    </button>
                </div>
            </form>
        </section>
    </div>
@endsection
