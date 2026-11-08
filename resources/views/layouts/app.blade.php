
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'RepairFlow')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="min-h-screen bg-slate-50 text-slate-800 antialiased">

    @auth
                    <header
                        class="border-b border-slate-200 bg-white shadow-sm"
                        x-data="{ menuOpen: false }"
                        @keydown.escape.window="menuOpen = false"
                        @resize.window="if (window.innerWidth >= 768) menuOpen = false"
                    >
                        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

                            {{-- ヘッダー上段 --}}
                            <div class="flex min-h-16 items-center justify-between gap-4">

                                {{-- ロゴとPC版ナビゲーション --}}
                                <div class="flex min-w-0 items-center gap-8">
                                    <a
                                        href="{{ route('dashboard') }}"
                                        class="shrink-0 text-xl font-bold tracking-tight text-slate-900"
                                    >
                                        RepairFlow
                                    </a>

                                    <nav class="hidden items-center gap-6 text-sm font-medium md:flex">
                                        <a
                                            href="{{ route('dashboard') }}"
                                            @if (request()->routeIs('dashboard')) aria-current="page" @endif
                                            class="{{ request()->routeIs('dashboard')
            ? 'text-blue-600'
            : 'text-slate-600 transition hover:text-blue-600' }}"
                                        >
                                            ダッシュボード
                                        </a>

                                        <a href="{{ route('repair-requests.index') }}" @if ((request()->routeIs('repair-requests.*') && !request()->routeIs('repair-requests.create')) || request()->routeIs('cancellation-requests.*')) aria-current="page"
                                        @endif class="{{ (request()->routeIs('repair-requests.*') && !request()->routeIs('repair-requests.create')) || request()->routeIs('cancellation-requests.*')
            ? 'text-blue-600'
            : 'text-slate-600 transition hover:text-blue-600' }}">
                                            修理依頼
                                        </a>

                                        @if (auth()->user()->role === 'requester')
                                            <a
                                                href="{{ route('repair-requests.create') }}"
                                                @if (request()->routeIs('repair-requests.create')) aria-current="page" @endif
                                                class="{{ request()->routeIs('repair-requests.create')
                ? 'text-blue-600'
                : 'text-slate-600 transition hover:text-blue-600' }}"
                                            >
                                                修理依頼を登録
                                            </a>
                                        @endif

                                        @if (auth()->user()->role === 'admin')
                                            <a
                                                href="{{ route('users.index') }}"
                                                @if (request()->routeIs('users.*')) aria-current="page" @endif
                                                class="{{ request()->routeIs('users.*')
                ? 'text-blue-600'
                : 'text-slate-600 transition hover:text-blue-600' }}"
                                            >
                                                ユーザー管理
                                            </a>
                                        @endif
                                    </nav>
                                </div>

                                {{-- PC版ユーザー情報・ログアウト --}}
                                <div class="hidden shrink-0 items-center gap-4 md:flex">
                                    <div class="text-right text-sm">
                                        <p class="font-semibold text-slate-800">
                                            {{ auth()->user()->name }}
                                        </p>

                                        <p class="text-xs text-slate-500">
                                            {{ match (auth()->user()->role) {
            'admin' => '管理者',
            'technician' => '技術者',
            'requester' => '依頼者',
            default => 'ユーザー',
        } }}
                                        </p>
                                    </div>

                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf

                                        <button
                                            type="submit"
                                            class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium
                                                text-slate-700 transition hover:bg-slate-100
                                                focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                                        >
                                            ログアウト
                                        </button>
                                    </form>
                                </div>

                                {{-- スマホ版ハンバーガーボタン --}}
                                <button
                                    type="button"
                                    class="inline-flex items-center justify-center rounded-lg border border-slate-300
                                        p-2 text-slate-700 transition hover:bg-slate-100
                                        focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 md:hidden"
                                    @click="menuOpen = !menuOpen"
                                    :aria-expanded="menuOpen.toString()"
                                    aria-controls="mobile-navigation"
                                    :aria-label="menuOpen ? 'メニューを閉じる' : 'メニューを開く'"
                                >
                                    <svg
                                        x-show="!menuOpen"
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-6 w-6"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M4 6h16M4 12h16M4 18h16" />
                                    </svg>

                                    <svg
                                        x-show="menuOpen"
                                        x-cloak
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-6 w-6"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>

                            </div>

                            {{-- スマホ版展開メニュー --}}
                            <div
                                id="mobile-navigation"
                                x-show="menuOpen"
                                x-cloak
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 -translate-y-2"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 translate-y-0"
                                x-transition:leave-end="opacity-0 -translate-y-2"
                                class="border-t border-slate-200 py-4 md:hidden"
                            >
                                <nav class="flex flex-col gap-1 text-sm font-medium">
                                    <a
                                        href="{{ route('dashboard') }}"
                                        @click="menuOpen = false"
                                        class="rounded-lg px-3 py-3 transition
                                            {{ request()->routeIs('dashboard')
            ? 'bg-blue-50 text-blue-700'
            : 'text-slate-700 hover:bg-slate-100' }}"
                                    >
                                        ダッシュボード
                                    </a>

                                    <a href="{{ route('repair-requests.index') }}" @click="menuOpen = false" class="rounded-lg px-3 py-3 transition
                                            {{ (request()->routeIs('repair-requests.*') && !request()->routeIs('repair-requests.create')) || request()->routeIs('cancellation-requests.*')
            ? 'bg-blue-50 text-blue-700'
            : 'text-slate-700 hover:bg-slate-100' }}">
                                        修理依頼
                                    </a>

                                    @if (auth()->user()->role === 'requester')
                                        <a
                                            href="{{ route('repair-requests.create') }}"
                                            @click="menuOpen = false"
                                            class="rounded-lg px-3 py-3 transition
                                                {{ request()->routeIs('repair-requests.create')
                ? 'bg-blue-50 text-blue-700'
                : 'text-slate-700 hover:bg-slate-100' }}"
                                        >
                                            修理依頼を登録
                                        </a>
                                    @endif

                                    @if (auth()->user()->role === 'admin')
                                        <a
                                            href="{{ route('users.index') }}"
                                            @click="menuOpen = false"
                                            class="rounded-lg px-3 py-3 transition
                                                {{ request()->routeIs('users.*')
                ? 'bg-blue-50 text-blue-700'
                : 'text-slate-700 hover:bg-slate-100' }}"
                                        >
                                            ユーザー管理
                                        </a>
                                    @endif
                                </nav>

                                {{-- スマホ版ユーザー情報・ログアウト --}}
                                <div class="mt-4 flex items-center justify-between gap-4 border-t border-slate-200 pt-4">
                                    <div class="min-w-0 text-sm">
                                        <p class="truncate font-semibold text-slate-800">
                                            {{ auth()->user()->name }}
                                        </p>

                                        <p class="text-xs text-slate-500">
                                            {{ match (auth()->user()->role) {
            'admin' => '管理者',
            'technician' => '技術者',
            'requester' => '依頼者',
            default => 'ユーザー',
        } }}
                                        </p>
                                    </div>

                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf

                                        <button
                                            type="submit"
                                            class="shrink-0 rounded-lg border border-slate-300 px-3 py-2 text-sm
                                                font-medium text-slate-700 transition hover:bg-slate-100
                                                focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                                        >
                                            ログアウト
                                        </button>
                                    </form>
                                </div>
                            </div>

                        </div>
                    </header>
    @endauth

    <main class="mx-auto w-full max-w-7xl px-4 py-6 sm:px-6 sm:py-8 lg:px-8">
        @yield('content')
    </main>

</body>

</html>
