<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>修理依頼一覧 | RepairFlow</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-100 text-slate-800">
    <main class="mx-auto max-w-6xl px-4 py-12">
        <section class="rounded-2xl bg-white p-8 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold tracking-widest text-blue-600">
                        REPAIR MANAGEMENT SYSTEM
                    </p>

                    <h1 class="mt-3 text-3xl font-bold text-slate-900">
                        修理依頼一覧
                    </h1>

                    <p class="mt-3 text-sm text-slate-600">
                        ログイン中：{{ auth()->user()->name }} さん
                    </p>
                </div>

                <a href="{{ route('dashboard') }}" class="rounded-lg bg-slate-200 px-4 py-2 font-semibold
                    text-slate-700 hover:bg-slate-300">
                    ダッシュボードへ戻る
                </a>
            </div>

            @if (auth()->user()->role === 'requester')
                <a href="{{ route('repair-requests.create') }}" class="mt-6 inline-block rounded-lg bg-blue-600 px-5 py-3
                                font-semibold text-white hover:bg-blue-700">
                    修理依頼を登録する
                </a>
            @endif

            <div class="mt-8 overflow-x-auto">
                <table class="w-full min-w-[800px] text-left text-sm">
                    <thead class="border-b bg-slate-50 text-slate-600">
                        <tr>
                            <th class="px-4 py-3">件名</th>
                            <th class="px-4 py-3">発生場所</th>
                            <th class="px-4 py-3">対象設備</th>
                            <th class="px-4 py-3">優先度</th>
                            <th class="px-4 py-3">ステータス</th>
                            <th class="px-4 py-3">依頼者</th>
                            <th class="px-4 py-3">担当技術者</th>
                            <th class="px-4 py-3">登録日</th>
                            <th class="px-4 py-3">詳細</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @forelse ($repairRequests as $repairRequest)
                                                <tr class="hover:bg-slate-50">
                                                    <td class="px-4 py-4 font-semibold">
                                                        {{ $repairRequest->title }}
                                                    </td>

                                                    <td class="px-4 py-4">
                                                        {{ $repairRequest->location }}
                                                    </td>

                                                    <td class="px-4 py-4">
                                                        {{ $repairRequest->asset_name }}
                                                    </td>

                                                    <td class="px-4 py-4">
                                                        {{ match ($repairRequest->priority) {
                                'low' => '低',
                                'normal' => '通常',
                                'high' => '高',
                                default => $repairRequest->priority,
                            } }}
                                                    </td>

                                                    <td class="px-4 py-4">
                                                        {{ match ($repairRequest->status) {
                                'received' => '受付済み',
                                'in_progress' => '対応中',
                                'on_hold' => '保留中',
                                'completed' => '完了',
                                'cancelled' => 'キャンセル済み',
                                default => $repairRequest->status,
                            } }}
                                                    </td>

                                                    <td class="px-4 py-4">
                                                        {{ $repairRequest->requester->name }}
                                                    </td>

                                                    <td class="px-4 py-4">
                                                        {{ $repairRequest->assignedTechnician?->name ?? '未割り当て' }}
                                                    </td>

                                                    <td class="whitespace-nowrap px-4 py-4">
                                                        {{ $repairRequest->created_at->format('Y/m/d') }}
                                                    </td>

                                                    <td class="px-4 py-4">
                                                        <a href="{{ route('repair-requests.show', $repairRequest) }}"
                                                            class="font-semibold text-blue-600 hover:text-blue-800">
                                                            詳細を見る
                                                        </a>
                                                    </td>
                                                </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-12 text-center text-slate-500">
                                    表示できる修理依頼はありません。
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $repairRequests->links() }}
            </div>
        </section>
    </main>
</body>

</html>