<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ダッシュボード | RepairFlow</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-100 text-slate-800">
    <main class="mx-auto max-w-5xl px-4 py-12">
        <section class="rounded-2xl bg-white p-8 shadow-sm">
            <p class="text-sm font-semibold tracking-widest text-blue-600">
                REPAIR MANAGEMENT SYSTEM
            </p>

            <h1 class="mt-3 text-3xl font-bold text-slate-900">
                RepairFlow
            </h1>

            <h2 class="mt-8 text-xl font-semibold">
                ダッシュボード
            </h2>

            <p class="mt-3">
                {{ auth()->user()->name }} さん、ログインしました。
            </p>

            <p class="mt-2 text-sm text-slate-500">
                現在のロール：{{ auth()->user()->role }}
            </p>

            <p class="mt-6 text-sm text-slate-600">
                ここに修理依頼や担当業務の情報を表示していきます。
            </p>

            <a href="{{ route('repair-requests.index') }}" class="mt-6 inline-block rounded-lg bg-slate-800 px-5 py-3
                font-semibold text-white transition hover:bg-slate-700">
                修理依頼一覧を見る
            </a>

            @if (session('status'))
                <div class="mt-6 rounded-lg bg-green-50 p-4 text-sm text-green-700">
                    {{ session('status') }}
                </div>
            @endif

            @if (auth()->user()->role === 'requester')
                <a href="{{ route('repair-requests.create') }}" class="mt-6 inline-block rounded-lg bg-blue-600 px-5 py-3
                                        font-semibold text-white transition hover:bg-blue-700">
                    修理依頼を登録する
                </a>
            @endif

            <form method="POST" action="{{ route('logout') }}" class="mt-8">
                @csrf
                <button type="submit" class="rounded-lg bg-slate-800 px-5 py-3
                           font-semibold text-white hover:bg-slate-700">
                    ログアウト
                </button>
            </form>
        </section>
    </main>
</body>

</html>