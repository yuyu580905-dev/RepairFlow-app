@extends('layouts.app')

@section('title', '修理依頼の登録')

@section('content')
    <div class="mx-auto max-w-3xl space-y-6">

        {{-- ページ見出し --}}
        <section>
            <p class="text-sm font-semibold tracking-widest text-blue-600">
                REPAIR MANAGEMENT SYSTEM
            </p>

            <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">
                修理依頼の登録
            </h1>

            <p class="mt-2 text-sm text-slate-600">
                修理が必要な設備の情報を入力してください。
            </p>
        </section>

        {{-- 登録フォーム --}}
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-8">

            <div class="mb-8 border-b border-slate-200 pb-5">
                <h2 class="text-lg font-bold text-slate-900">
                    依頼内容
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    すべての項目を入力してください。
                </p>
            </div>

            {{-- 入力エラー一覧 --}}
            @if ($errors->any())
                <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700" role="alert">
                    <p class="font-semibold">
                        入力内容を確認してください。
                    </p>

                    <ul class="mt-2 list-inside list-disc space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('repair-requests.store') }}" class="space-y-6" novalidate>
                @csrf

                {{-- 件名 --}}
                <div>
                    <label for="title" class="mb-2 block text-sm font-semibold text-slate-700">
                        件名
                        <span class="ml-1 text-xs font-medium text-red-600">必須</span>
                    </label>

                    <input id="title" name="title" type="text" value="{{ old('title') }}" required maxlength="255"
                        aria-invalid="{{ $errors->has('title') ? 'true' : 'false' }}" class="w-full rounded-lg border px-4 py-3 text-sm text-slate-800
                                    placeholder:text-slate-400 focus:outline-none focus:ring-2
                                    {{ $errors->has('title')
        ? 'border-red-400 focus:border-red-500 focus:ring-red-100'
        : 'border-slate-300 focus:border-blue-500 focus:ring-blue-100' }}">
                    @error('title')
                        <p class="mt-2 text-sm font-medium text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- 修理内容 --}}
                <div>
                    <label for="description" class="mb-2 block text-sm font-semibold text-slate-700">
                        修理内容
                        <span class="ml-1 text-xs font-medium text-red-600">必須</span>
                    </label>

                    <textarea id="description" name="description" rows="5" required maxlength="10000"
                        aria-invalid="{{ $errors->has('description') ? 'true' : 'false' }}" class="w-full resize-y rounded-lg border px-4 py-3 text-sm text-slate-800
                                    placeholder:text-slate-400 focus:outline-none focus:ring-2
                                    {{ $errors->has('description')
        ? 'border-red-400 focus:border-red-500 focus:ring-red-100'
        : 'border-slate-300 focus:border-blue-500 focus:ring-blue-100' }}">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-2 text-sm font-medium text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- 発生場所 --}}
                <div>
                    <label for="location" class="mb-2 block text-sm font-semibold text-slate-700">
                        発生場所
                        <span class="ml-1 text-xs font-medium text-red-600">必須</span>
                    </label>

                    <input id="location" name="location" type="text" value="{{ old('location') }}" required maxlength="255"
                        aria-invalid="{{ $errors->has('location') ? 'true' : 'false' }}" class="w-full rounded-lg border px-4 py-3 text-sm text-slate-800
                                    placeholder:text-slate-400 focus:outline-none focus:ring-2
                                    {{ $errors->has('location')
        ? 'border-red-400 focus:border-red-500 focus:ring-red-100'
        : 'border-slate-300 focus:border-blue-500 focus:ring-blue-100' }}">
                    @error('location')
                        <p class="mt-2 text-sm font-medium text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- 対象設備 --}}
                <div>
                    <label for="asset_name" class="mb-2 block text-sm font-semibold text-slate-700">
                        対象設備
                        <span class="ml-1 text-xs font-medium text-red-600">必須</span>
                    </label>

                    <input id="asset_name" name="asset_name" type="text" value="{{ old('asset_name') }}" required
                        maxlength="255" aria-invalid="{{ $errors->has('asset_name') ? 'true' : 'false' }}" class="w-full rounded-lg border px-4 py-3 text-sm text-slate-800
                                    placeholder:text-slate-400 focus:outline-none focus:ring-2
                                    {{ $errors->has('asset_name')
        ? 'border-red-400 focus:border-red-500 focus:ring-red-100'
        : 'border-slate-300 focus:border-blue-500 focus:ring-blue-100' }}">
                    @error('asset_name')
                        <p class="mt-2 text-sm font-medium text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- 優先度 --}}
                <div>
                    <label for="priority" class="mb-2 block text-sm font-semibold text-slate-700">
                        優先度
                        <span class="ml-1 text-xs font-medium text-red-600">必須</span>
                    </label>

                    <select id="priority" name="priority" required
                        aria-invalid="{{ $errors->has('priority') ? 'true' : 'false' }}" class="w-full rounded-lg border px-4 py-3 text-sm text-slate-800
                                    focus:outline-none focus:ring-2
                                    {{ $errors->has('priority')
        ? 'border-red-400 focus:border-red-500 focus:ring-red-100'
        : 'border-slate-300 focus:border-blue-500 focus:ring-blue-100' }}">
                        <option value="low" @selected(old('priority') === 'low')>
                            低
                        </option>

                        <option value="normal" @selected(old('priority', 'normal') === 'normal')>
                            通常
                        </option>

                        <option value="high" @selected(old('priority') === 'high')>
                            高
                        </option>
                    </select>

                    @error('priority')
                        <p class="mt-2 text-sm font-medium text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- 操作ボタン --}}
                <div class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-6 sm:flex-row sm:justify-end">
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300
                                    px-5 py-3 text-sm font-semibold text-slate-700 transition
                                    hover:bg-slate-50 focus:outline-none focus:ring-2
                                    focus:ring-slate-300 focus:ring-offset-2">
                        戻る
                    </a>

                    <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-blue-600
                                    px-5 py-3 text-sm font-semibold text-white shadow-sm transition
                                    hover:bg-blue-700 focus:outline-none focus:ring-2
                                    focus:ring-blue-500 focus:ring-offset-2">
                        修理依頼を登録する
                    </button>
                </div>
            </form>
        </section>
    </div>
@endsection