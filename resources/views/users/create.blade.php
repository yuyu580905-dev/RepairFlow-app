<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ユーザー登録 | RepairFlow</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-100 text-slate-800">
    <main class="mx-auto max-w-3xl px-4 py-12">

        <section class="rounded-2xl bg-white p-8 shadow-sm">

            {{-- ページ見出し --}}
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold tracking-widest text-blue-600">
                        REPAIR MANAGEMENT SYSTEM
                    </p>

                    <h1 class="mt-3 text-3xl font-bold text-slate-900">
                        ユーザー登録
                    </h1>

                    <p class="mt-2 text-sm text-slate-500">
                        新しいユーザーの情報を入力してください。
                    </p>
                </div>

                <a href="{{ route('users.index') }}" class="rounded-lg border border-slate-300 px-5 py-3
                    font-semibold text-slate-700 transition hover:bg-slate-50">
                    ユーザー一覧へ戻る
                </a>
            </div>

            {{-- バリデーションエラー --}}
            @if ($errors->any())
                <div class="mt-8 rounded-xl border border-red-200 bg-red-50 p-5">
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
            <form method="POST" action="{{ route('users.store') }}" class="mt-8 space-y-6" novalidate>

                @csrf

                {{-- 名前 --}}
                <div>
                    <label for="name" class="mb-2 block text-sm font-semibold text-slate-700">
                        名前
                    </label>

                    <input type="text" id="name" name="name" value="{{ old('name') }}" required maxlength="255"
                        autocomplete="name" class="w-full rounded-lg border border-slate-300 px-3 py-3
                        focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100
                        @error('name') border-red-500 @enderror">

                    @error('name')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- メールアドレス --}}
                <div>
                    <label for="email" class="mb-2 block text-sm font-semibold text-slate-700">
                        メールアドレス
                    </label>

                    <input type="email" id="email" name="email" value="{{ old('email') }}" required maxlength="255"
                        autocomplete="email" class="w-full rounded-lg border border-slate-300 px-3 py-3
                        focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100
                        @error('email') border-red-500 @enderror">

                    @error('email')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- パスワード --}}
                <div>
                    <label for="password" class="mb-2 block text-sm font-semibold text-slate-700">
                        パスワード
                    </label>

                    <input type="password" id="password" name="password" required minlength="8"
                        autocomplete="new-password" class="w-full rounded-lg border border-slate-300 px-3 py-3
                        focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100
                        @error('password') border-red-500 @enderror">

                    <p class="mt-2 text-xs text-slate-500">
                        8文字以上で入力してください。
                    </p>

                    @error('password')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- パスワード確認 --}}
                <div>
                    <label for="password_confirmation" class="mb-2 block text-sm font-semibold text-slate-700">
                        パスワード（確認）
                    </label>

                    <input type="password" id="password_confirmation" name="password_confirmation" required
                        minlength="8" autocomplete="new-password" class="w-full rounded-lg border border-slate-300 px-3 py-3
                        focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                </div>

                {{-- ユーザー種別 --}}
                <div>
                    <label for="role" class="mb-2 block text-sm font-semibold text-slate-700">
                        ユーザー種別
                    </label>

                    <select id="role" name="role" required class="w-full rounded-lg border border-slate-300 px-3 py-3
                        focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100
                        @error('role') border-red-500 @enderror">

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
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- アカウント状態 --}}
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-5">
                    <p class="font-semibold text-slate-700">
                        アカウント状態
                    </p>

                    <p class="mt-2 text-sm text-slate-600">
                        登録時は自動的に「有効」として作成されます。
                    </p>
                </div>

                {{-- 操作ボタン --}}
                <div class="flex flex-wrap justify-end gap-3 border-t border-slate-200 pt-6">

                    <a href="{{ route('users.index') }}" class="rounded-lg border border-slate-300 px-5 py-3
                        font-semibold text-slate-700 transition hover:bg-slate-50">
                        キャンセル
                    </a>

                    <button type="submit" class="rounded-lg bg-blue-600 px-6 py-3
                        font-semibold text-white transition hover:bg-blue-700
                        focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        ユーザーを登録
                    </button>

                </div>
            </form>

        </section>
    </main>
</body>

</html>