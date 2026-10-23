<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ログイン | RepairFlow</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-100 text-slate-800">
    <main class="flex min-h-screen items-center justify-center px-4 py-12">
        <section class="w-full max-w-md rounded-2xl bg-white p-8 shadow-lg sm:p-10">
            <header class="mb-8 text-center">
                <p class="mb-3 text-sm font-semibold tracking-widest text-blue-600">
                    REPAIR MANAGEMENT SYSTEM
                </p>
                <h1 class="text-3xl font-bold tracking-tight text-slate-900">
                    RepairFlow
                </h1>
                <p class="mt-3 text-sm text-slate-500">
                    アカウントにログインしてください
                </p>
            </header>

            @if (session('status'))
                <div class="mb-5 rounded-lg bg-green-50 p-3 text-sm text-green-700">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-5 rounded-lg bg-red-50 p-4 text-sm text-red-700" role="alert">
                    <p class="font-semibold">ログインできませんでした。</p>
                    <ul class="mt-2 list-inside list-disc">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-5" novalidate>
                @csrf

                <div>
                    <label for="email" class="mb-2 block text-sm font-medium text-slate-700">
                        メールアドレス
                    </label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                        autocomplete="username" class="w-full rounded-lg border border-slate-300 px-4 py-3
                               outline-none transition focus:border-blue-500
                               focus:ring-2 focus:ring-blue-200" placeholder="example@example.com">
                </div>

                <div>
                    <label for="password" class="mb-2 block text-sm font-medium text-slate-700">
                        パスワード
                    </label>
                    <input id="password" name="password" type="password" required autocomplete="current-password" class="w-full rounded-lg border border-slate-300 px-4 py-3
                               outline-none transition focus:border-blue-500
                               focus:ring-2 focus:ring-blue-200" placeholder="パスワードを入力">
                </div>

                <div class="flex items-center">
                    <input id="remember" name="remember" type="checkbox" value="1" class="h-4 w-4 rounded border-slate-300 text-blue-600
                               focus:ring-blue-500">
                    <label for="remember" class="ml-2 text-sm text-slate-600">
                        ログイン状態を保持する
                    </label>
                </div>

                <button type="submit" class="w-full rounded-lg bg-blue-600 px-4 py-3
                           font-semibold text-white transition
                           hover:bg-blue-700 focus:outline-none
                           focus:ring-2 focus:ring-blue-500
                           focus:ring-offset-2">
                    ログイン
                </button>
            </form>

            <footer class="mt-8 border-t border-slate-200 pt-5 text-center">
                <p class="text-xs leading-5 text-slate-500">
                    アカウントの発行については管理者にお問い合わせください。
                </p>
            </footer>
        </section>
    </main>
</body>

</html>