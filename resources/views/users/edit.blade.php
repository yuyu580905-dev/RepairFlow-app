
@extends('layouts.app')

@section('title', 'ユーザー編集')

@section('content')
    <div class="mx-auto max-w-3xl">
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-8">

        {{-- ページ見出し --}}
        <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-semibold tracking-widest text-blue-600">
                    USER MANAGEMENT
                </p>

                <h1 class="mt-2 text-2xl font-bold text-slate-900 sm:text-3xl">
                    ユーザー編集
                </h1>

                <p class="mt-2 text-sm text-slate-500">
                    {{ $user->name }} さんのユーザー情報を編集します。
                </p>
            </div>

            <a href="{{ route('users.index') }}"
                class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-5 py-3
                    text-sm font-semibold text-slate-700 transition hover:bg-slate-50
                    focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                ユーザー一覧へ戻る
            </a>
        </div>

        {{-- 編集フォーム --}}
            {{-- バリデーションエラー --}}
            @if ($errors->any())
                <div role="alert" class="mb-8 rounded-xl border border-red-200 bg-red-50 p-5">
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

            <form method="POST" action="{{ route('users.update', $user) }}" class="space-y-6" novalidate>
                @csrf
                @method('PUT')

                {{-- 名前 --}}
                <div>
                    <label for="name" class="mb-2 block text-sm font-semibold text-slate-700">
                        名前 <span class="text-red-600">必須</span>
                    </label>

                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name', $user->name) }}"
                        required
                        maxlength="255"
                        autocomplete="name"
                        aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}"
                        @error('name') aria-describedby="name-error" @enderror
                        class="w-full rounded-lg border px-3 py-3 text-sm text-slate-800
                            focus:outline-none focus:ring-2 focus:ring-blue-100
                            {{ $errors->has('name')
                                ? 'border-red-500 focus:border-red-500'
                                : 'border-slate-300 focus:border-blue-500' }}"
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
                        メールアドレス <span class="text-red-600">必須</span>
                    </label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email', $user->email) }}"
                        required
                        maxlength="255"
                        autocomplete="email"
                        aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                        @error('email') aria-describedby="email-error" @enderror
                        class="w-full rounded-lg border px-3 py-3 text-sm text-slate-800
                            focus:outline-none focus:ring-2 focus:ring-blue-100
                            {{ $errors->has('email')
                                ? 'border-red-500 focus:border-red-500'
                                : 'border-slate-300 focus:border-blue-500' }}"
                    >

                    @error('email')
                        <p id="email-error" class="mt-2 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- ユーザー種別 --}}
                <div>
                    <label for="role" class="mb-2 block text-sm font-semibold text-slate-700">
                        ユーザー種別 <span class="text-red-600">必須</span>
                    </label>

                    <select
                        id="role"
                        name="role"
                        required
                        aria-invalid="{{ $errors->has('role') ? 'true' : 'false' }}"
                        @error('role') aria-describedby="role-error" @enderror
                        class="w-full rounded-lg border bg-white px-3 py-3 text-sm text-slate-800
                            focus:outline-none focus:ring-2 focus:ring-blue-100
                            {{ $errors->has('role')
                                ? 'border-red-500 focus:border-red-500'
                                : 'border-slate-300 focus:border-blue-500' }}"
                    >
                        <option value="requester" @selected(old('role', $user->role) === 'requester')>
                            依頼者
                        </option>

                        <option value="technician" @selected(old('role', $user->role) === 'technician')>
                            技術者
                        </option>

                        <option value="admin" @selected(old('role', $user->role) === 'admin')>
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
                <div>
                    <label for="is_active" class="mb-2 block text-sm font-semibold text-slate-700">
                        アカウント状態 <span class="text-red-600">必須</span>
                    </label>

                    <select
                        id="is_active"
                        name="is_active"
                        required
                        aria-invalid="{{ $errors->has('is_active') ? 'true' : 'false' }}"
                        @error('is_active') aria-describedby="is-active-error" @enderror
                        class="w-full rounded-lg border bg-white px-3 py-3 text-sm text-slate-800
                            focus:outline-none focus:ring-2 focus:ring-blue-100
                            {{ $errors->has('is_active')
                                ? 'border-red-500 focus:border-red-500'
                                : 'border-slate-300 focus:border-blue-500' }}"
                    >
                        <option value="1" @selected((string) old('is_active', (int) $user->is_active) === '1')>
                            有効
                        </option>

                        <option value="0" @selected((string) old('is_active', (int) $user->is_active) === '0')>
                            無効
                        </option>
                    </select>

                    @error('is_active')
                        <p id="is-active-error" class="mt-2 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror

                    <p class="mt-2 text-xs leading-relaxed text-slate-500">
                        無効にすると、そのユーザーはログインできなくなります。
                    </p>
                </div>

                {{-- 操作ボタン --}}
                <div class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-6 sm:flex-row sm:justify-end">
                    <a
                        href="{{ route('users.index') }}"
                        class="inline-flex items-center justify-center rounded-lg border border-slate-300
                            px-6 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50
                            focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                    >
                        キャンセル
                    </a>

                    <button
                        type="submit"
                        class="inline-flex items-center justify-center rounded-lg bg-blue-600
                            px-6 py-3 text-sm font-semibold text-white transition hover:bg-blue-700
                            focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                    >
                        ユーザー情報を更新
                    </button>
                </div>
            </form>
        </section>
    </div>
@endsection
