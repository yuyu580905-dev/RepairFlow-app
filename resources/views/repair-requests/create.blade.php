<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>修理依頼の登録 | RepairFlow</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-100 text-slate-800">
    <main class="mx-auto max-w-3xl px-4 py-12">
        <section class="rounded-2xl bg-white p-8 shadow-sm sm:p-10">
            <header class="mb-8">
                <p class="text-sm font-semibold tracking-widest text-blue-600">
                    REPAIR MANAGEMENT SYSTEM
                </p>
                <h1 class="mt-3 text-3xl font-bold text-slate-900">
                    修理依頼の登録
                </h1>
                <p class="mt-3 text-sm text-slate-500">
                    修理が必要な設備の情報を入力してください。
                </p>
            </header>

            @if ($errors->any())
                <div class="mb-6 rounded-lg bg-red-50 p-4 text-sm text-red-700" role="alert">
                    <p class="font-semibold">入力内容を確認してください。</p>
                    <ul class="mt-2 list-inside list-disc">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('repair-requests.store') }}" class="space-y-6" novalidate>
                @csrf

                <div>
                    <label for="title" class="mb-2 block text-sm font-medium text-slate-700">
                        件名
                    </label>
                    <input id="title" name="title" type="text" value="{{ old('title') }}" required maxlength="255"
                        class="w-full rounded-lg border border-slate-300 px-4 py-3
                        outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                    @error('title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="description" class="mb-2 block text-sm font-medium text-slate-700">
                        修理内容
                    </label>
                    <textarea id="description" name="description" rows="5" required
                        class="w-full rounded-lg border border-slate-300 px-4 py-3
                        outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="location" class="mb-2 block text-sm font-medium text-slate-700">
                        発生場所
                    </label>
                    <input id="location" name="location" type="text" value="{{ old('location') }}" required
                        maxlength="255" class="w-full rounded-lg border border-slate-300 px-4 py-3
                        outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                    @error('location')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="asset_name" class="mb-2 block text-sm font-medium text-slate-700">
                        対象設備
                    </label>
                    <input id="asset_name" name="asset_name" type="text" value="{{ old('asset_name') }}" required
                        maxlength="255" class="w-full rounded-lg border border-slate-300 px-4 py-3
                        outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                    @error('asset_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="priority" class="mb-2 block text-sm font-medium text-slate-700">
                        優先度
                    </label>
                    <select id="priority" name="priority" required class="w-full rounded-lg border border-slate-300 px-4 py-3
                        outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
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
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-col gap-3 sm:flex-row">
                    <button type="submit" class="rounded-lg bg-blue-600 px-5 py-3 font-semibold
                        text-white transition hover:bg-blue-700">
                        修理依頼を登録する
                    </button>

                    <a href="{{ route('dashboard') }}" class="rounded-lg border border-slate-300 px-5 py-3
                        text-center font-semibold text-slate-700 hover:bg-slate-50">
                        戻る
                    </a>
                </div>
            </form>
        </section>
    </main>
</body>

</html>