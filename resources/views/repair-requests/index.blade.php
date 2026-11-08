@extends('layouts.app')

@section('title', '修理依頼一覧')

@section('content')
    <div class="space-y-6">

        {{-- ページ見出し --}}
        <section class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold tracking-widest text-blue-600">
                    REPAIR MANAGEMENT SYSTEM
                </p>

                <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">
                    修理依頼一覧
                </h1>

                <p class="mt-2 text-sm text-slate-600">
                    修理依頼の内容と対応状況を確認できます。
                </p>
            </div>

            @if (auth()->user()->role === 'requester')
                <a href="{{ route('repair-requests.create') }}"
                    class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-5 py-3
                                                                text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700
                                                                focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    修理依頼を登録する
                </a>
            @endif
        </section>

        {{-- 修理依頼一覧 --}}
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

            <div class="border-b border-slate-200 px-5 py-4 sm:px-6">
                <h2 class="font-bold text-slate-900">
                    登録済みの修理依頼
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    詳細を見ると、依頼内容や対応履歴を確認できます。
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px] text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold text-slate-600">
                        <tr>
                            <th scope="col" class="min-w-[180px] whitespace-nowrap px-5 py-4">件名</th>
                            <th scope="col" class="whitespace-nowrap px-5 py-4">発生場所</th>
                            <th scope="col" class="whitespace-nowrap px-5 py-4">対象設備</th>
                            <th scope="col" class="whitespace-nowrap px-5 py-4">優先度</th>
                            <th scope="col" class="whitespace-nowrap px-5 py-4">ステータス</th>
                            <th scope="col" class="whitespace-nowrap px-5 py-4">依頼者</th>
                            <th scope="col" class="whitespace-nowrap px-5 py-4">担当技術者</th>
                            <th scope="col" class="whitespace-nowrap px-5 py-4">登録日</th>
                            <th scope="col" class="whitespace-nowrap px-5 py-4">詳細</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @forelse ($repairRequests as $repairRequest)
                            <tr class="transition hover:bg-slate-50">

                                <td class="min-w-[180px] max-w-xs break-words px-5 py-4 font-semibold text-slate-900">
                                    {{ $repairRequest->title }}
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-slate-600">
                                    {{ $repairRequest->location }}
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-slate-600">
                                    {{ $repairRequest->asset_name }}
                                </td>

                                <td class="whitespace-nowrap px-5 py-4">
                                    @php
                                        $priority = match ($repairRequest->priority) {
                                            'low' => ['低', 'bg-slate-100 text-slate-700'],
                                            'normal' => ['通常', 'bg-blue-50 text-blue-700'],
                                            'high' => ['高', 'bg-red-50 text-red-700'],
                                            default => [$repairRequest->priority, 'bg-slate-100 text-slate-700'],
                                        };
                                    @endphp

                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $priority[1] }}">
                                        {{ $priority[0] }}
                                    </span>
                                </td>

                                <td class="whitespace-nowrap px-5 py-4">
                                    @php
                                        $status = match ($repairRequest->status) {
                                            'received' => ['受付済み', 'bg-blue-50 text-blue-700'],
                                            'in_progress' => ['対応中', 'bg-amber-50 text-amber-700'],
                                            'on_hold' => ['保留中', 'bg-orange-50 text-orange-700'],
                                            'completed' => ['完了', 'bg-green-50 text-green-700'],
                                            'cancelled' => ['キャンセル済み', 'bg-slate-100 text-slate-700'],
                                            default => [$repairRequest->status, 'bg-slate-100 text-slate-700'],
                                        };
                                    @endphp

                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $status[1] }}">
                                        {{ $status[0] }}
                                    </span>
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-slate-600">
                                    {{ $repairRequest->requester->name }}
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-slate-600">
                                    {{ $repairRequest->assignedTechnician?->name ?? '未割り当て' }}
                                </td>

                                <td class="whitespace-nowrap px-5 py-4 text-slate-600">
                                    {{ $repairRequest->created_at->format('Y/m/d') }}
                                </td>

                                <td class="whitespace-nowrap px-5 py-4">
                                    <a href="{{ route('repair-requests.show', $repairRequest) }}"
                                        class="font-semibold text-blue-600 transition hover:text-blue-800
                                                                                                            focus:outline-none focus:underline">
                                        詳細を見る
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-5 py-16 text-center">
                                    <p class="font-semibold text-slate-700">
                                        表示できる修理依頼はありません。
                                    </p>

                                    <p class="mt-2 text-sm text-slate-500">
                                        修理依頼が登録されると、こちらに表示されます。
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- ページネーション --}}
            @if ($repairRequests->hasPages())
                <div class="border-t border-slate-200 px-5 py-4 sm:px-6">
                    {{ $repairRequests->links() }}
                </div>
            @endif

        </section>
    </div>
@endsection