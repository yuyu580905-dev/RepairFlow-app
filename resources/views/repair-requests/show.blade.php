<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>修理依頼詳細 | RepairFlow</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-100 text-slate-800">
    <main class="mx-auto max-w-4xl px-4 py-12">
        <section class="rounded-2xl bg-white p-8 shadow-sm">
            <p class="text-sm font-semibold tracking-widest text-blue-600">
                REPAIR MANAGEMENT SYSTEM
            </p>

            <h1 class="mt-3 text-3xl font-bold text-slate-900">
                修理依頼詳細
            </h1>

            <p class="mt-3 text-sm text-slate-500">
                依頼番号：{{ $repairRequest->id }}
            </p>

            <div class="mt-8 border-t border-slate-200">
                <dl class="divide-y divide-slate-100">
                    <div class="py-4 sm:flex">
                        <dt class="font-semibold text-slate-600 sm:w-40">件名</dt>
                        <dd class="mt-1 sm:mt-0 sm:flex-1">
                            {{ $repairRequest->title }}
                        </dd>
                    </div>

                    <div class="py-4 sm:flex">
                        <dt class="font-semibold text-slate-600 sm:w-40">修理内容</dt>
                        <dd class="mt-1 whitespace-pre-wrap sm:mt-0 sm:flex-1">{{ $repairRequest->description }}</dd>
                    </div>

                    <div class="py-4 sm:flex">
                        <dt class="font-semibold text-slate-600 sm:w-40">発生場所</dt>
                        <dd class="mt-1 sm:mt-0 sm:flex-1">
                            {{ $repairRequest->location }}
                        </dd>
                    </div>

                    <div class="py-4 sm:flex">
                        <dt class="font-semibold text-slate-600 sm:w-40">対象設備</dt>
                        <dd class="mt-1 sm:mt-0 sm:flex-1">
                            {{ $repairRequest->asset_name }}
                        </dd>
                    </div>

                    <div class="py-4 sm:flex">
                        <dt class="font-semibold text-slate-600 sm:w-40">優先度</dt>
                        <dd class="mt-1 sm:mt-0 sm:flex-1">
                            {{ match ($repairRequest->priority) {
    'low' => '低',
    'normal' => '通常',
    'high' => '高',
    default => $repairRequest->priority,
} }}
                        </dd>
                    </div>

                    <div class="py-4 sm:flex">
                        <dt class="font-semibold text-slate-600 sm:w-40">ステータス</dt>
                        <dd class="mt-1 sm:mt-0 sm:flex-1">
                            {{ match ($repairRequest->status) {
    'received' => '受付済み',
    'in_progress' => '対応中',
    default => $repairRequest->status,
} }}
                        </dd>
                    </div>

                    <div class="py-4 sm:flex">
                        <dt class="font-semibold text-slate-600 sm:w-40">依頼者</dt>
                        <dd class="mt-1 sm:mt-0 sm:flex-1">
                            {{ $repairRequest->requester->name }}
                        </dd>
                    </div>

                    <div class="py-4 sm:flex">
                        <dt class="font-semibold text-slate-600 sm:w-40">担当技術者</dt>
                        <dd class="mt-1 sm:mt-0 sm:flex-1">
                            {{ $repairRequest->assignedTechnician?->name ?? '未割り当て' }}
                        </dd>
                    </div>

                    <div class="py-4 sm:flex">
                        <dt class="font-semibold text-slate-600 sm:w-40">登録日時</dt>
                        <dd class="mt-1 sm:mt-0 sm:flex-1">
                            {{ $repairRequest->created_at->format('Y/m/d H:i') }}
                        </dd>
                    </div>

                    <div class="py-4 sm:flex">
                        <dt class="font-semibold text-slate-600 sm:w-40">最終更新日時</dt>
                        <dd class="mt-1 sm:mt-0 sm:flex-1">
                            {{ $repairRequest->updated_at->format('Y/m/d H:i') }}
                        </dd>
                    </div>
                </dl>
            </div>

            <div class="mt-8 flex flex-wrap gap-3">
                <a href="{{ route('repair-requests.index') }}" class="rounded-lg bg-blue-600 px-5 py-3 font-semibold
                    text-white hover:bg-blue-700">
                    一覧へ戻る
                </a>

                <a href="{{ route('dashboard') }}" class="rounded-lg bg-slate-200 px-5 py-3 font-semibold
                    text-slate-700 hover:bg-slate-300">
                    ダッシュボードへ戻る
                </a>
            </div>
        </section>
    </main>
</body>

</html>