@extends('layouts.app')

@section('title', '修理依頼詳細')

@section('content')
    <div class="mx-auto max-w-5xl">
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-8">
            <div class="border-b border-slate-200 pb-6">
                <p class="text-xs font-bold tracking-[0.2em] text-blue-600">
                    REPAIR MANAGEMENT SYSTEM
                </p>

                <div class="mt-3 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
                            修理依頼詳細
                        </h1>

                        <p class="mt-2 text-sm text-slate-500">
                            修理依頼の内容・対応状況・作業履歴を確認できます。
                        </p>
                    </div>

                    <div class="inline-flex w-fit items-center rounded-lg bg-slate-100 px-4 py-2">
                        <span class="text-xs font-semibold text-slate-500">依頼番号</span>
                        <span class="ml-3 font-bold tabular-nums text-slate-800">
                            #{{ $repairRequest->id }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- 登録成功メッセージ --}}
            @if (session('success'))
                <div class="mt-6 rounded-xl border border-green-200 bg-green-50 p-4" role="status">
                    <p class="font-semibold text-green-800">
                        {{ session('success') }}
                    </p>
                </div>
            @endif

            {{-- ステータス更新メッセージ --}}
            @if (session('status'))
                <div class="mt-6 rounded-xl border border-green-200 bg-green-50 p-4" role="status">
                    <p class="font-semibold text-green-800">
                        {{ session('status') }}
                    </p>
                </div>
            @endif

            {{-- バリデーションエラー --}}
            @if ($errors->any())
                <div class="mt-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800" role="alert">
                    <p class="font-bold">入力内容を確認してください。</p>

                    <ul class="mt-2 list-inside list-disc space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- 修理依頼の基本情報 --}}
            <section class="mt-8 rounded-xl border border-slate-200 bg-white">
                <div class="border-b border-slate-200 bg-slate-50 px-5 py-4 sm:px-6">
                    <h2 class="font-bold text-slate-900">
                        修理依頼の基本情報
                    </h2>
                    <p class="mt-1 text-xs text-slate-500">
                        登録された依頼内容と現在の状況
                    </p>
                </div>

                <div class="px-5 sm:px-6">
                    <dl class="divide-y divide-slate-100">

                        <div class="py-4 sm:flex sm:items-start">
                            <dt class="font-semibold text-slate-600 sm:w-40 sm:shrink-0">
                                件名
                            </dt>
                            <dd class="mt-1 font-semibold text-slate-900 sm:mt-0 sm:flex-1">
                                {{ $repairRequest->title }}
                            </dd>
                        </div>

                        <div class="py-4 sm:flex sm:items-start">
                            <dt class="font-semibold text-slate-600 sm:w-40 sm:shrink-0">
                                修理内容
                            </dt>
                            <dd class="mt-1 break-words text-slate-700 sm:mt-0 sm:flex-1">
                                {!! nl2br(e($repairRequest->description)) !!}
                            </dd>
                        </div>

                        <div class="py-4 sm:flex sm:items-start">
                            <dt class="font-semibold text-slate-600 sm:w-40 sm:shrink-0">
                                発生場所
                            </dt>
                            <dd class="mt-1 break-words text-slate-700 sm:mt-0 sm:flex-1">
                                {{ $repairRequest->location }}
                            </dd>
                        </div>

                        <div class="py-4 sm:flex sm:items-start">
                            <dt class="font-semibold text-slate-600 sm:w-40 sm:shrink-0">
                                対象設備
                            </dt>
                            <dd class="mt-1 text-slate-700 sm:mt-0 sm:flex-1">
                                {{ $repairRequest->asset_name }}
                            </dd>
                        </div>

                        <div class="py-4 sm:flex sm:items-center">
                            <dt class="font-semibold text-slate-600 sm:w-40 sm:shrink-0">
                                優先度
                            </dt>
                            <dd class="mt-2 sm:mt-0 sm:flex-1">
                                @php
                                    $priorityLabel = match ($repairRequest->priority) {
                                        'low' => '低',
                                        'normal' => '通常',
                                        'high' => '高',
                                        default => $repairRequest->priority,
                                    };

                                    $priorityClass = match ($repairRequest->priority) {
                                        'low' => 'bg-slate-100 text-slate-700',
                                        'normal' => 'bg-blue-100 text-blue-800',
                                        'high' => 'bg-red-100 text-red-800',
                                        default => 'bg-slate-100 text-slate-700',
                                    };
                                @endphp

                                <span class="inline-flex rounded-full px-3 py-1 text-sm font-bold {{ $priorityClass }}">
                                    {{ $priorityLabel }}
                                </span>
                            </dd>
                        </div>

                        <div class="py-4 sm:flex sm:items-center">
                            <dt class="font-semibold text-slate-600 sm:w-40 sm:shrink-0">
                                ステータス
                            </dt>
                            <dd class="mt-2 sm:mt-0 sm:flex-1">
                                @php
                                    $statusLabel = match ($repairRequest->status) {
                                        'received' => '受付済み',
                                        'in_progress' => '対応中',
                                        'on_hold' => '保留中',
                                        'completed' => '完了',
                                        'cancelled' => 'キャンセル済み',
                                        default => $repairRequest->status,
                                    };

                                    $statusClass = match ($repairRequest->status) {
                                        'received' => 'bg-blue-100 text-blue-800',
                                        'in_progress' => 'bg-indigo-100 text-indigo-800',
                                        'on_hold' => 'bg-amber-100 text-amber-800',
                                        'completed' => 'bg-green-100 text-green-800',
                                        'cancelled' => 'bg-slate-200 text-slate-700',
                                        default => 'bg-slate-100 text-slate-700',
                                    };
                                @endphp

                                <span class="inline-flex rounded-full px-3 py-1 text-sm font-bold {{ $statusClass }}">
                                    {{ $statusLabel }}
                                </span>
                            </dd>
                        </div>

                        <div class="py-4 sm:flex sm:items-center">
                            <dt class="font-semibold text-slate-600 sm:w-40 sm:shrink-0">
                                依頼者
                            </dt>
                            <dd class="mt-1 text-slate-700 sm:mt-0 sm:flex-1">
                                {{ $repairRequest->requester->name }}
                            </dd>
                        </div>

                        <div class="py-4 sm:flex sm:items-center">
                            <dt class="font-semibold text-slate-600 sm:w-40 sm:shrink-0">
                                担当技術者
                            </dt>
                            <dd class="mt-1 text-slate-700 sm:mt-0 sm:flex-1">
                                {{ $repairRequest->assignedTechnician?->name ?? '未割り当て' }}
                            </dd>
                        </div>

                        <div class="py-4 sm:flex sm:items-center">
                            <dt class="font-semibold text-slate-600 sm:w-40 sm:shrink-0">
                                登録日時
                            </dt>
                            <dd class="mt-1 text-sm tabular-nums text-slate-700 sm:mt-0 sm:flex-1">
                                {{ $repairRequest->created_at->copy()->setTimezone('Asia/Tokyo')->format('Y/m/d H:i') }}
                            </dd>
                        </div>

                        <div class="py-4 sm:flex sm:items-center">
                            <dt class="font-semibold text-slate-600 sm:w-40 sm:shrink-0">
                                最終更新日時
                            </dt>
                            <dd class="mt-1 text-sm tabular-nums text-slate-700 sm:mt-0 sm:flex-1">
                                {{ $repairRequest->updated_at->copy()->setTimezone('Asia/Tokyo')->format('Y/m/d H:i') }}
                            </dd>
                        </div>

                    </dl>
                </div>
            </section>

            @if (auth()->user()->role === 'admin')
                <section class="mt-8 rounded-xl border border-slate-200 bg-white">
                    <div class="border-b border-slate-200 bg-slate-50 px-5 py-4 sm:px-6">
                        <h2 class="font-bold text-slate-900">
                            担当技術者の割り当て
                        </h2>
                        <p class="mt-1 text-xs text-slate-500">
                            修理依頼を担当する技術者を選択できます。
                        </p>
                    </div>

                    <div class="p-5 sm:p-6">
                        <div class="mb-5 rounded-lg bg-slate-50 p-4">
                            <p class="text-sm font-semibold text-slate-500">
                                現在の担当技術者
                            </p>
                            <p class="mt-1 font-bold text-slate-900">
                                {{ $repairRequest->assignedTechnician?->name ?? '未割り当て' }}
                            </p>
                        </div>

                        @if ($repairRequest->status === 'completed')
                            <div class="rounded-lg border border-amber-200 bg-amber-50 p-4" role="status">
                                <p class="text-sm font-semibold text-amber-800">
                                    完了した修理依頼の担当技術者は変更できません。
                                </p>
                            </div>
                        @else
                            <form action="{{ route('repair-requests.assign-technician', $repairRequest) }}" method="POST"
                                class="space-y-5" novalidate>
                                @csrf
                                @method('PATCH')

                                <div>
                                    <label for="assigned_to" class="block text-sm font-semibold text-slate-700">
                                        新しい担当技術者
                                    </label>

                                    <select id="assigned_to" name="assigned_to" required
                                        aria-invalid="{{ $errors->has('assigned_to') ? 'true' : 'false' }}"
                                        aria-describedby="assigned_to-error"
                                        class="mt-2 block w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                                        <option value="">技術者を選択してください</option>

                                        @foreach ($technicians as $technician)
                                            <option value="{{ $technician->id }}" @selected(
                                                (string) old('assigned_to', $repairRequest->assigned_to)
                                                === (string) $technician->id
                                            )>
                                                {{ $technician->name }}
                                            </option>
                                        @endforeach
                                    </select>

                                    @error('assigned_to')
                                        <p id="assigned_to-error" class="mt-2 text-sm font-medium text-red-600">
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
                                    <button type="submit"
                                        class="w-full rounded-lg bg-blue-600 px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 sm:w-auto">
                                        担当技術者を割り当てる
                                    </button>
                                </div>
                            </form>
                        @endif
                    </div>
                </section>
            @endif

            @if (auth()->user()->role === 'admin')
                @php
                    $pendingCancellationRequests = $repairRequest->cancellationRequests
                        ->where('status', 'pending');
                @endphp

                <section class="mt-10 overflow-hidden rounded-xl border border-slate-200 bg-white">
                    <div class="border-b border-slate-200 bg-slate-50 px-5 py-4 sm:px-6">
                        <h2 class="font-bold text-slate-900">
                            キャンセル申請の審査
                        </h2>
                        <p class="mt-1 text-xs text-slate-500">
                            申請内容を確認し、承認または却下を選択してください。
                        </p>
                    </div>

                    <div class="p-5 sm:p-6">
                        @if ($pendingCancellationRequests->isEmpty())
                            <div class="rounded-lg bg-slate-50 p-4 text-sm text-slate-600" role="status">
                                現在、審査中のキャンセル申請はありません。
                            </div>
                        @else
                            <div class="space-y-6">
                                @foreach ($pendingCancellationRequests as $cancellationRequest)
                                    <article class="rounded-xl border border-slate-200 p-4 sm:p-5">
                                        <div class="mb-5 flex flex-wrap items-center justify-between gap-2">
                                            <h3 class="font-bold text-slate-900">
                                                キャンセル申請 #{{ $cancellationRequest->id }}
                                            </h3>

                                            <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800">
                                                審査中
                                            </span>
                                        </div>

                                        <dl class="space-y-4">
                                            <div class="sm:flex sm:gap-6">
                                                <dt class="text-sm font-semibold text-slate-600 sm:w-32 sm:shrink-0">
                                                    申請者
                                                </dt>
                                                <dd class="mt-1 break-words text-slate-800 sm:mt-0 sm:flex-1">
                                                    {{ $cancellationRequest->requester->name }}
                                                </dd>
                                            </div>

                                            <div class="sm:flex sm:gap-6">
                                                <dt class="text-sm font-semibold text-slate-600 sm:w-32 sm:shrink-0">
                                                    申請日時
                                                </dt>
                                                <dd class="mt-1 text-slate-800 sm:mt-0 sm:flex-1">
                                                    {{ $cancellationRequest->created_at->copy()->setTimezone('Asia/Tokyo')->format('Y/m/d H:i') }}
                                                </dd>
                                            </div>

                                            <div class="sm:flex sm:gap-6">
                                                <dt class="text-sm font-semibold text-slate-600 sm:w-32 sm:shrink-0">
                                                    キャンセル理由
                                                </dt>
                                                <dd class="mt-1 break-words text-slate-700 sm:mt-0 sm:flex-1">
                                                    {!! nl2br(e($cancellationRequest->reason)) !!}
                                                </dd>
                                            </div>
                                        </dl>

                                        <div class="mt-6 border-t border-slate-100 pt-5">
                                            <form action="{{ route('cancellation-requests.review', $cancellationRequest) }}"
                                                method="POST" class="space-y-5" novalidate>
                                                @csrf

                                                <div>
                                                    <label for="decision-{{ $cancellationRequest->id }}"
                                                        class="block text-sm font-semibold text-slate-700">
                                                        審査結果
                                                    </label>

                                                    <select id="decision-{{ $cancellationRequest->id }}" name="decision" required
                                                        class="mt-2 block w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                                                        <option value="">審査結果を選択してください</option>
                                                        <option value="approved">承認する</option>
                                                        <option value="rejected">却下する</option>
                                                    </select>
                                                </div>

                                                <div>
                                                    <label for="review_comment-{{ $cancellationRequest->id }}"
                                                        class="block text-sm font-semibold text-slate-700">
                                                        審査コメント（任意）
                                                    </label>

                                                    <textarea id="review_comment-{{ $cancellationRequest->id }}" name="review_comment"
                                                        rows="3" maxlength="2000"
                                                        class="mt-2 block w-full resize-y rounded-lg border border-slate-300 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                                        placeholder="審査理由や依頼者への補足を入力してください"></textarea>
                                                </div>

                                                <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
                                                    <button type="submit"
                                                        class="w-full rounded-lg bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 sm:w-auto">
                                                        審査結果を確定する
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </section>
            @endif


            @if (in_array(auth()->user()->role, ['requester', 'admin'], true))
                @php
                    $visibleCancellationRequests = auth()->user()->role === 'admin'
                        ? $repairRequest->cancellationRequests
                        : $repairRequest->cancellationRequests->where(
                            'requester_id',
                            auth()->id()
                        );
                @endphp

                <section class="mt-10 overflow-hidden rounded-xl border border-slate-200 bg-white">
                    <div class="border-b border-slate-200 bg-slate-50 px-5 py-4 sm:px-6">
                        <h2 class="font-bold text-slate-900">
                            キャンセル申請履歴
                        </h2>
                        <p class="mt-1 text-xs text-slate-500">
                            これまでの申請内容と審査結果を確認できます。
                        </p>
                    </div>

                    <div class="p-5 sm:p-6">
                        @if ($visibleCancellationRequests->isEmpty())
                            <div class="rounded-lg bg-slate-50 p-4 text-sm text-slate-600" role="status">
                                キャンセル申請履歴はありません。
                            </div>
                        @else
                            <ol class="space-y-5">
                                @foreach ($visibleCancellationRequests->sortByDesc('created_at') as $cancellationRequest)
                                            <li class="rounded-xl border border-slate-200 p-4 sm:p-5">
                                                <div class="flex flex-wrap items-center justify-between gap-3">
                                                    <h3 class="font-bold text-slate-900">
                                                        キャンセル申請 #{{ $cancellationRequest->id }}
                                                    </h3>

                                                    <span @class([
                                                        'rounded-full px-3 py-1 text-xs font-semibold',
                                                        'bg-amber-100 text-amber-800' => $cancellationRequest->status === 'pending',
                                                        'bg-green-100 text-green-800' => $cancellationRequest->status === 'approved',
                                                        'bg-red-100 text-red-800' => $cancellationRequest->status === 'rejected',
                                                    ])>
                                                        {{ match ($cancellationRequest->status) {
                                        'pending' => '審査中',
                                        'approved' => '承認',
                                        'rejected' => '却下',
                                        default => $cancellationRequest->status,
                                    } }}
                                                    </span>
                                                </div>

                                                <dl class="mt-5 space-y-4">
                                                    @if (auth()->user()->role === 'admin')
                                                        <div class="sm:flex sm:gap-6">
                                                            <dt class="text-sm font-semibold text-slate-600 sm:w-32 sm:shrink-0">
                                                                申請者
                                                            </dt>
                                                            <dd class="mt-1 break-words text-slate-800 sm:mt-0 sm:flex-1">
                                                                {{ $cancellationRequest->requester->name }}
                                                            </dd>
                                                        </div>
                                                    @endif

                                                    <div class="sm:flex sm:gap-6">
                                                        <dt class="text-sm font-semibold text-slate-600 sm:w-32 sm:shrink-0">
                                                            申請日時
                                                        </dt>
                                                        <dd class="mt-1 text-slate-800 sm:mt-0 sm:flex-1">
                                                            {{ $cancellationRequest->created_at
                                        ? $cancellationRequest->created_at->copy()
                                            ->setTimezone('Asia/Tokyo')
                                            ->format('Y/m/d H:i')
                                        : '日時不明' }}
                                                        </dd>
                                                    </div>

                                                    <div class="sm:flex sm:gap-6">
                                                        <dt class="text-sm font-semibold text-slate-600 sm:w-32 sm:shrink-0">
                                                            キャンセル理由
                                                        </dt>
                                                        <dd class="mt-1 break-words text-sm leading-6 text-slate-700 sm:mt-0 sm:flex-1">
                                                            {!! nl2br(e($cancellationRequest->reason)) !!}
                                                        </dd>
                                                    </div>
                                                </dl>

                                                @if ($cancellationRequest->reviewed_at)
                                                        <div class="mt-5 border-t border-slate-100 pt-5">
                                                            <h4 class="font-semibold text-slate-900">
                                                                審査情報
                                                            </h4>

                                                            <dl class="mt-4 space-y-3">
                                                                <div class="sm:flex sm:gap-6">
                                                                    <dt class="text-sm font-semibold text-slate-600 sm:w-32 sm:shrink-0">
                                                                        審査担当者
                                                                    </dt>
                                                                    <dd class="mt-1 break-words text-sm text-slate-700 sm:mt-0 sm:flex-1">
                                                                        {{ $cancellationRequest->reviewer?->name ?? '不明' }}
                                                                    </dd>
                                                                </div>

                                                                <div class="sm:flex sm:gap-6">
                                                                    <dt class="text-sm font-semibold text-slate-600 sm:w-32 sm:shrink-0">
                                                                        審査日時
                                                                    </dt>
                                                                    <dd class="mt-1 text-sm text-slate-700 sm:mt-0 sm:flex-1">
                                                                        {{ \Illuminate\Support\Carbon::parse($cancellationRequest->reviewed_at, 'UTC')
                                                    ->setTimezone('Asia/Tokyo')
                                                    ->format('Y/m/d H:i') }}
                                                                    </dd>
                                                                </div>
                                                            </dl>

                                                            <div class="mt-4 rounded-lg bg-slate-50 p-4">
                                                                <h4 class="text-sm font-semibold text-slate-700">
                                                                    審査コメント
                                                                </h4>

                                                                @if ($cancellationRequest->review_comment)
                                                                    <p class="mt-2 break-words text-sm leading-6 text-slate-700">
                                                                        {!! nl2br(e($cancellationRequest->review_comment)) !!}
                                                                    </p>
                                                                @else
                                                                    <p class="mt-2 text-sm text-slate-500">
                                                                        審査コメントはありません。
                                                                    </p>
                                                                @endif
                                                            </div>
                                                        </div>
                                                @endif
                                            </li>
                                @endforeach
                            </ol>
                        @endif
                    </div>
                </section>
            @endif

            @if (
                    auth()->user()->role === 'requester'
                    && auth()->user()->is_active
                    && (int) $repairRequest->requester_id === (int) auth()->id()
                    && $repairRequest->status === 'received'
                )
                <section class="mt-10 overflow-hidden rounded-xl border border-slate-200 bg-white">
                    <div class="border-b border-slate-200 bg-slate-50 px-5 py-4 sm:px-6">
                        <h2 class="font-bold text-slate-900">
                            キャンセル申請
                        </h2>
                        <p class="mt-1 text-xs text-slate-500">
                            この修理依頼のキャンセルを希望する場合に申請できます。
                        </p>
                    </div>

                    <div class="p-5 sm:p-6">
                        @if ($repairRequest->cancellationRequests->contains('status', 'pending'))
                            <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm leading-6 text-amber-800"
                                role="status">
                                この修理依頼には審査中のキャンセル申請があります。
                                管理者の審査をお待ちください。
                            </div>
                        @else
                            <p class="text-sm leading-6 text-slate-600">
                                キャンセルを希望する理由を入力してください。
                                申請後、管理者が内容を確認して承認または却下します。
                            </p>

                            <form action="{{ route('repair-requests.cancellation-requests.store', $repairRequest) }}" method="POST"
                                class="mt-5 space-y-5" novalidate>
                                @csrf

                                <div>
                                    <label for="reason" class="block text-sm font-semibold text-slate-700">
                                        キャンセル理由
                                        <span class="ml-1 text-xs font-semibold text-red-600">必須</span>
                                    </label>

                                    <textarea id="reason" name="reason" rows="4" maxlength="2000" required
                                        aria-invalid="{{ $errors->has('reason') ? 'true' : 'false' }}"
                                        aria-describedby="reason-error"
                                        class="mt-2 block w-full resize-y rounded-lg border border-slate-300 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                        placeholder="キャンセルを希望する理由を入力してください">{{ old('reason') }}</textarea>

                                    @error('reason')
                                        <p id="reason-error" class="mt-2 text-sm text-red-600" role="alert">
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
                                    <button type="submit"
                                        class="w-full rounded-lg bg-red-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 sm:w-auto">
                                        キャンセルを申請する
                                    </button>
                                </div>
                            </form>
                        @endif
                    </div>
                </section>
            @endif

            @if (
                    auth()->user()->role === 'technician'
                    && (int) $repairRequest->assigned_to === (int) auth()->id()
                    && in_array($repairRequest->status, ['received', 'in_progress', 'on_hold'], true)
                )
                @php
                    $allowedStatuses = match ($repairRequest->status) {
                        'received' => [
                            'in_progress' => '対応中',
                            'on_hold' => '保留中',
                        ],
                        'in_progress' => [
                            'on_hold' => '保留中',
                            'completed' => '完了',
                        ],
                        'on_hold' => [
                            'in_progress' => '対応中',
                        ],
                        default => [],
                    };
                @endphp

                <section class="mt-10 overflow-hidden rounded-xl border border-slate-200 bg-white">
                    <div class="border-b border-slate-200 bg-slate-50 px-5 py-4 sm:px-6">
                        <h2 class="font-bold text-slate-900">
                            ステータスを更新
                        </h2>
                        <p class="mt-1 text-xs text-slate-500">
                            作業状況に応じて修理依頼のステータスを変更できます。
                        </p>
                    </div>

                    <div class="p-5 sm:p-6">
                        <form action="{{ route('repair-requests.update-status', $repairRequest) }}" method="POST"
                            class="space-y-5" x-data="{ selectedStatus: @js(old('status', '')) }" novalidate>
                            @csrf
                            @method('PATCH')

                            <div>
                                <label for="status" class="block text-sm font-semibold text-slate-700">
                                    変更後のステータス
                                    <span class="ml-1 text-xs font-semibold text-red-600">必須</span>
                                </label>

                                <select id="status" name="status" required x-model="selectedStatus"
                                    aria-invalid="{{ $errors->has('status') ? 'true' : 'false' }}"
                                    aria-describedby="status-error"
                                    class="mt-2 block w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                                    <option value="">ステータスを選択してください</option>

                                    @foreach ($allowedStatuses as $value => $label)
                                        <option value="{{ $value }}" @selected(old('status') === $value)>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>

                                @error('status')
                                    <p id="status-error" class="mt-2 text-sm text-red-600" role="alert">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <div>
                                <label for="message" class="block text-sm font-semibold text-slate-700">
                                    <span x-show="selectedStatus === 'on_hold'" x-cloak>
                                        保留理由
                                        <span class="ml-1 text-xs font-semibold text-red-600">必須</span>
                                    </span>

                                    <span x-show="selectedStatus !== 'on_hold'" x-cloak>
                                        作業メッセージ（任意）
                                    </span>
                                </label>

                                <textarea id="message" name="message" rows="4" maxlength="2000"
                                    :required="selectedStatus === 'on_hold'"
                                    aria-invalid="{{ $errors->has('message') ? 'true' : 'false' }}"
                                    aria-describedby="message-error"
                                    class="mt-2 block w-full resize-y rounded-lg border border-slate-300 px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                    :placeholder="selectedStatus === 'on_hold'
                                                                            ? '例：交換部品の到着待ち、追加情報の確認中など'
                                                                            : '作業内容や引き継ぎ事項など'">{{ old('message') }}</textarea>

                                @error('message')
                                    <p id="message-error" class="mt-2 text-sm text-red-600" role="alert">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
                                <button type="submit"
                                    class="w-full rounded-lg bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 sm:w-auto">
                                    ステータスを更新する
                                </button>
                            </div>
                        </form>
                    </div>
                </section>
            @endif

            <section class="mt-10 overflow-hidden rounded-xl border border-slate-200 bg-white">
                <div class="border-b border-slate-200 bg-slate-50 px-5 py-4 sm:px-6">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <h2 class="font-bold text-slate-900">
                            修理履歴
                        </h2>

                        <span class="rounded-full bg-slate-200 px-3 py-1 text-xs font-semibold text-slate-600">
                            {{ $repairRequest->repairLogs->count() }} 件
                        </span>
                    </div>

                    <p class="mt-1 text-xs text-slate-500">
                        修理依頼に関する対応や変更の記録です。
                    </p>
                </div>

                <div class="p-5 sm:p-6">
                    @if ($repairRequest->repairLogs->isEmpty())
                        <div class="rounded-lg bg-slate-50 p-4 text-sm text-slate-600" role="status">
                            まだ修理履歴はありません。
                        </div>
                    @else
                        <ol class="space-y-5">
                            @foreach ($repairRequest->repairLogs->sortByDesc('created_at') as $log)
                                            <li class="relative pl-7">
                                                @if (!$loop->last)
                                                    <span aria-hidden="true" class="absolute bottom-0 left-[5px] top-3 w-px bg-slate-200"></span>
                                                @endif

                                                <span aria-hidden="true" @class([
                                                    'absolute left-0 top-1 h-3 w-3 rounded-full ring-4 ring-white',
                                                    'bg-blue-500' => $loop->first,
                                                    'bg-slate-300' => !$loop->first,
                                                ])></span>

                                                <div class="rounded-lg bg-slate-50 p-4">
                                                    <div class="flex flex-wrap items-start justify-between gap-2">
                                                        <h3 class="text-sm font-bold text-slate-900">
                                                            {{ match ($log->action) {
                                    'request_created' => '修理依頼を作成',
                                    'technician_assigned' => '担当技術者を割り当て',
                                    'status_changed' => 'ステータス変更',
                                    'cancellation_requested' => 'キャンセル申請',
                                    'cancellation_approved' => 'キャンセル申請を承認',
                                    'cancellation_rejected' => 'キャンセル申請を却下',
                                    default => $log->action,
                                } }}
                                                        </h3>

                                                        <time class="text-xs text-slate-500">
                                                            {{ $log->created_at
                                    ? $log->created_at->copy()
                                        ->setTimezone('Asia/Tokyo')
                                        ->format('Y/m/d H:i')
                                    : '日時不明' }}
                                                        </time>
                                                    </div>

                                                    @if ($log->from_status || $log->to_status)
                                                        <p class="mt-2 flex flex-wrap items-center gap-2 text-sm">
                                                            @if ($log->from_status)
                                                                                <span class="rounded-md bg-white px-2 py-1 text-slate-600">
                                                                                    {{ match ($log->from_status) {
                                                                    'received' => '受付済み',
                                                                    'in_progress' => '対応中',
                                                                    'on_hold' => '保留中',
                                                                    'completed' => '完了',
                                                                    'cancelled' => 'キャンセル済み',
                                                                    default => $log->from_status,
                                                                } }}
                                                                                </span>
                                                            @endif

                                                            @if ($log->from_status && $log->to_status)
                                                                <span class="text-slate-400" aria-label="から">
                                                                    →
                                                                </span>
                                                            @endif

                                                            @if ($log->to_status)
                                                                                <span class="rounded-md bg-blue-100 px-2 py-1 font-semibold text-blue-800">
                                                                                    {{ match ($log->to_status) {
                                                                    'received' => '受付済み',
                                                                    'in_progress' => '対応中',
                                                                    'on_hold' => '保留中',
                                                                    'completed' => '完了',
                                                                    'cancelled' => 'キャンセル済み',
                                                                    default => $log->to_status,
                                                                } }}
                                                                                </span>
                                                            @endif
                                                        </p>
                                                    @endif

                                                    @if ($log->message)
                                                        <p class="mt-3 break-words text-sm leading-6 text-slate-700">
                                                            {!! nl2br(e($log->message)) !!}
                                                        </p>
                                                    @endif

                                                    <p class="mt-3 border-t border-slate-200 pt-2 text-xs text-slate-500">
                                                        記録者：{{ $log->user?->name ?? '不明' }}
                                                    </p>
                                                </div>
                                            </li>
                            @endforeach
                        </ol>
                    @endif
                </div>
            </section>


            <div class="mt-8 flex flex-col gap-3 border-t border-slate-200 pt-6 sm:flex-row">
                <a href="{{ route('repair-requests.index') }}"
                    class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-5 py-3
                                                                                                                                                                                                                            font-semibold text-white transition hover:bg-blue-700">
                    一覧へ戻る
                </a>

                <a href="{{ route('dashboard') }}"
                    class="inline-flex items-center justify-center rounded-lg bg-slate-100 px-5 py-3
                                                                                                                                                                                                                            font-semibold text-slate-700 transition hover:bg-slate-200">
                    ダッシュボードへ戻る
                </a>
            </div>
        </section>
    </div>
@endsection