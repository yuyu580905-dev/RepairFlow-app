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

            @if (session('success'))
                <div class="mt-6 rounded-lg bg-green-50 p-4 text-sm font-medium text-green-800" role="status">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('status'))
                <div class="mt-6 rounded-lg bg-green-50 p-4 text-sm font-medium text-green-800" role="status">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mt-6 rounded-lg bg-red-50 p-4 text-sm text-red-800" role="alert">
                    <p class="font-semibold">入力内容を確認してください。</p>

                    <ul class="mt-2 list-inside list-disc">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="mt-8 border-t border-slate-200">
                <dl class="divide-y divide-slate-100">
                    <div class="py-4 sm:flex">
                        <dt class="font-semibold text-slate-600 sm:w-40">
                            件名
                        </dt>
                        <dd class="mt-1 sm:mt-0 sm:flex-1">
                            {{ $repairRequest->title }}
                        </dd>
                    </div>

                    <div class="py-4 sm:flex">
                        <dt class="font-semibold text-slate-600 sm:w-40">修理内容</dt>
                        <dd class="mt-1 whitespace-pre-wrap sm:mt-0 sm:flex-1">{{ $repairRequest->description }}</dd>
                    </div>

                    <div class="py-4 sm:flex">
                        <dt class="font-semibold text-slate-600 sm:w-40">
                            発生場所
                        </dt>
                        <dd class="mt-1 sm:mt-0 sm:flex-1">
                            {{ $repairRequest->location }}
                        </dd>
                    </div>

                    <div class="py-4 sm:flex">
                        <dt class="font-semibold text-slate-600 sm:w-40">
                            対象設備
                        </dt>
                        <dd class="mt-1 sm:mt-0 sm:flex-1">
                            {{ $repairRequest->asset_name }}
                        </dd>
                    </div>

                    <div class="py-4 sm:flex">
                        <dt class="font-semibold text-slate-600 sm:w-40">
                            優先度
                        </dt>
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
                        <dt class="font-semibold text-slate-600 sm:w-40">
                            ステータス
                        </dt>
                        <dd class="mt-1 sm:mt-0 sm:flex-1">
                            {{ match ($repairRequest->status) {
    'received' => '受付済み',
    'in_progress' => '対応中',
    'on_hold' => '保留中',
    'completed' => '完了',
    'cancelled' => 'キャンセル済み',
    default => $repairRequest->status,
} }}
                        </dd>
                    </div>

                    <div class="py-4 sm:flex">
                        <dt class="font-semibold text-slate-600 sm:w-40">
                            依頼者
                        </dt>
                        <dd class="mt-1 sm:mt-0 sm:flex-1">
                            {{ $repairRequest->requester->name }}
                        </dd>
                    </div>

                    <div class="py-4 sm:flex">
                        <dt class="font-semibold text-slate-600 sm:w-40">
                            担当技術者
                        </dt>
                        <dd class="mt-1 sm:mt-0 sm:flex-1">
                            {{ $repairRequest->assignedTechnician?->name ?? '未割り当て' }}
                        </dd>
                    </div>

                    <div class="py-4 sm:flex">
                        <dt class="font-semibold text-slate-600 sm:w-40">
                            登録日時
                        </dt>
                        <dd class="mt-1 sm:mt-0 sm:flex-1">
                            {{ $repairRequest->created_at->copy()->setTimezone('Asia/Tokyo')->format('Y/m/d H:i') }}
                        </dd>
                    </div>

                    <div class="py-4 sm:flex">
                        <dt class="font-semibold text-slate-600 sm:w-40">
                            最終更新日時
                        </dt>
                        <dd class="mt-1 sm:mt-0 sm:flex-1">
                            {{ $repairRequest->updated_at->copy()->setTimezone('Asia/Tokyo')->format('Y/m/d H:i') }}
                        </dd>
                    </div>
                </dl>
            </div>

            @if (auth()->user()->role === 'admin')
                <section class="mt-10 border-t border-slate-200 pt-8">
                    <h2 class="text-xl font-bold text-slate-900">
                        担当技術者の割り当て
                    </h2>

                    <form action="{{ route('repair-requests.assign-technician', $repairRequest) }}" method="POST"
                        class="mt-5 space-y-4" novalidate>
                        @csrf
                        @method('PATCH')

                        <div>
                            <label for="assigned_to" class="block text-sm font-semibold text-slate-700">
                                担当技術者
                            </label>

                            <select id="assigned_to" name="assigned_to" required
                                class="mt-2 block w-full rounded-lg border border-slate-300 bg-white px-4 py-3">
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
                        </div>

                        <button type="submit"
                            class="rounded-lg bg-blue-600 px-5 py-3 font-semibold text-white hover:bg-blue-700">
                            担当技術者を割り当てる
                        </button>
                    </form>
                </section>
            @endif

@if (auth()->user()->role === 'admin')
    <section class="mt-10 border-t border-slate-200 pt-8">
        <h2 class="text-xl font-bold text-slate-900">
            キャンセル申請の審査
        </h2>

        @php
    $pendingCancellationRequests = $repairRequest->cancellationRequests
        ->where('status', 'pending');
        @endphp

        @if ($pendingCancellationRequests->isEmpty())
            <p class="mt-4 rounded-lg bg-slate-50 p-4 text-sm text-slate-600">
                現在、審査中のキャンセル申請はありません。
            </p>
        @else
            <div class="mt-5 space-y-6">
                @foreach ($pendingCancellationRequests as $cancellationRequest)
                    <article class="rounded-xl border border-slate-200 p-5">
                        <dl class="space-y-3">
                            <div>
                                <dt class="text-sm font-semibold text-slate-600">
                                    申請者
                                </dt>
                                <dd class="mt-1">
                                    {{ $cancellationRequest->requester->name }}
                                </dd>
                            </div>

                            <div>
                                <dt class="text-sm font-semibold text-slate-600">
                                    申請日時
                                </dt>
                                <dd class="mt-1">
                                    {{ $cancellationRequest->created_at->copy()->setTimezone('Asia/Tokyo')->format('Y/m/d H:i') }}
                                </dd>
                            </div>

                            <div>
                                <dt class="text-sm font-semibold text-slate-600">
                                    キャンセル理由
                                </dt>
                                <dd class="mt-1 whitespace-pre-wrap">{{ $cancellationRequest->reason }}</dd>
                            </div>
                        </dl>

                        <form action="{{ route('cancellation-requests.review', $cancellationRequest) }}" method="POST"
                            class="mt-6 space-y-4">
                            @csrf

                            <div>
                                <label for="decision-{{ $cancellationRequest->id }}"
                                    class="block text-sm font-semibold text-slate-700">
                                    審査結果
                                </label>

                                <select id="decision-{{ $cancellationRequest->id }}" name="decision" required
                                    class="mt-2 block w-full rounded-lg border border-slate-300 bg-white px-4 py-3">
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

                                <textarea id="review_comment-{{ $cancellationRequest->id }}" name="review_comment" rows="3"
                                    maxlength="2000" class="mt-2 block w-full rounded-lg border border-slate-300 px-4 py-3"
                                    placeholder="審査理由や依頼者への補足を入力してください"></textarea>
                            </div>

                            <button type="submit"
                                class="rounded-lg bg-blue-600 px-5 py-3 font-semibold text-white hover:bg-blue-700">
                                審査結果を確定する
                            </button>
                        </form>
                    </article>
                @endforeach
            </div>
        @endif
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

    <section class="mt-10 border-t border-slate-200 pt-8">
        <h2 class="text-xl font-bold text-slate-900">
            キャンセル申請履歴
        </h2>

        @if ($visibleCancellationRequests->isEmpty())
            <p class="mt-4 rounded-lg bg-slate-50 p-5 text-sm text-slate-500">
                キャンセル申請履歴はありません。
            </p>
        @else
            <ol class="mt-5 space-y-5">
                @foreach ($visibleCancellationRequests->sortByDesc('created_at') as $cancellationRequest)
                                    <li class="rounded-xl border border-slate-200 p-5">
                                        <div class="flex flex-wrap items-center justify-between gap-3">
                                            <h3 class="font-semibold text-slate-900">
                                                キャンセル申請 #{{ $cancellationRequest->id }}
                                            </h3>

                                            <span @class([
                'rounded-full px-3 py-1 text-sm font-semibold',
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

                                        @if (auth()->user()->role === 'admin')
                                            <p class="mt-3 text-sm text-slate-600">
                                                申請者：{{ $cancellationRequest->requester->name }}
                                            </p>
                                        @endif

                                        <p class="mt-2 text-sm text-slate-600">
                                            申請日時：
                                            {{ $cancellationRequest->created_at
                ? $cancellationRequest->created_at->copy()
                    ->setTimezone('Asia/Tokyo')
                    ->format('Y/m/d H:i')
                : '日時不明' }}
                                        </p>

                                        <div class="mt-4">
                                            <h4 class="text-sm font-semibold text-slate-700">
                                                キャンセル理由
                                            </h4>

                                            <p class="mt-1 whitespace-pre-wrap text-sm leading-6 text-slate-700">{{ $cancellationRequest->reason }}</p>
                                        </div>

                                        @if ($cancellationRequest->reviewed_at)
                                                            <div class="mt-4 border-t border-slate-100 pt-4">
                                                                <h4 class="text-sm font-semibold text-slate-700">
                                                                    審査情報
                                                                </h4>

                                                                <p class="mt-2 text-sm text-slate-600">
                                                                    審査担当者：{{ $cancellationRequest->reviewer?->name ?? '不明' }}
                                                                </p>

                                                                <p class="mt-1 text-sm text-slate-600">
                                                                    審査日時：
                                                                    {{ \Illuminate\Support\Carbon::parse($cancellationRequest->reviewed_at, 'UTC')
                    ->setTimezone('Asia/Tokyo')
                    ->format('Y/m/d H:i') }}
                                                                </p>

                                                                @if ($cancellationRequest->review_comment)
                                                                    <div class="mt-3">
                                                                        <h4 class="text-sm font-semibold text-slate-700">
                                                                            審査コメント
                                                                        </h4>

                                                                        <p class="mt-1 whitespace-pre-wrap text-sm leading-6 text-slate-700">{{ $cancellationRequest->review_comment }}</p>
                                                                    </div>
                                                                @else
                                                                    <p class="mt-3 text-sm text-slate-500">
                                                                        審査コメントはありません。
                                                                    </p>
                                                                @endif
                                                            </div>
                                        @endif
                                    </li>
                @endforeach
            </ol>
        @endif
    </section>
@endif

            @if (
    auth()->user()->role === 'requester'
    && auth()->user()->is_active
    && (int) $repairRequest->requester_id === (int) auth()->id()
    && $repairRequest->status === 'received'
)
                    <section class="mt-10 border-t border-slate-200 pt-8">
                        <h2 class="text-xl font-bold text-slate-900">
                            キャンセル申請
                        </h2>

                        @if ($repairRequest->cancellationRequests->contains('status', 'pending'))
                            <p class="mt-4 rounded-lg bg-amber-50 p-4 text-sm text-amber-800">
                                この修理依頼には審査中のキャンセル申請があります。
                                管理者の審査をお待ちください。
                            </p>
                        @else
                            <p class="mt-3 text-sm leading-6 text-slate-600">
                                この修理依頼のキャンセルを希望する場合は、理由を入力して申請してください。
                                申請後、管理者が内容を確認して承認または却下します。
                            </p>

                            <form action="{{ route('repair-requests.cancellation-requests.store', $repairRequest) }}" method="POST"
                                class="mt-5 space-y-4" novalidate>
                                @csrf

                                <div>
                                    <label for="reason" class="block text-sm font-semibold text-slate-700">
                                        キャンセル理由
                                        <span class="text-red-600">必須</span>
                                    </label>

                                    <textarea id="reason" name="reason" rows="4" maxlength="2000" required
                                        class="mt-2 block w-full rounded-lg border border-slate-300 px-4 py-3"
                                        placeholder="キャンセルを希望する理由を入力してください">{{ old('reason') }}</textarea>

                                    @error('reason')
                                        <p class="mt-1 text-sm text-red-600">
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                <button type="submit" class="rounded-lg bg-red-600 px-5 py-3 font-semibold text-white hover:bg-red-700">
                                    キャンセルを申請する
                                </button>
                            </form>
                        @endif
                    </section>
            @endif

            @if (
    auth()->user()->role === 'technician'
    && (int) $repairRequest->assigned_to === (int) auth()->id()
    && in_array($repairRequest->status, ['received', 'in_progress', 'on_hold'], true)
)
                <section class="mt-10 border-t border-slate-200 pt-8">
                    <h2 class="text-xl font-bold text-slate-900">
                        ステータスを更新
                    </h2>

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

                    <form action="{{ route('repair-requests.update-status', $repairRequest) }}" method="POST"
                        class="mt-5 space-y-4" x-data="{ selectedStatus: '{{ old('status', '') }}' }" novalidate>
                        @csrf
                        @method('PATCH')

                        <div>
                            <label for="status" class="block text-sm font-semibold text-slate-700">
                                変更後のステータス
                            </label>

                            <select id="status" name="status" required x-model="selectedStatus"
                                class="mt-2 block w-full rounded-lg border border-slate-300 bg-white px-4 py-3">
                                <option value="">ステータスを選択してください</option>

                                @foreach ($allowedStatuses as $value => $label)
                                    <option value="{{ $value }}" @selected(old('status') === $value)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="message" class="block text-sm font-semibold text-slate-700">
                                <span x-show="selectedStatus === 'on_hold'" x-cloak>
                                    保留理由 <span class="text-red-600">必須</span>
                                </span>

                                <span x-show="selectedStatus !== 'on_hold'" x-cloak>
                                    作業メッセージ（任意）
                                </span>
                            </label>

                            <textarea id="message" name="message" rows="4" maxlength="2000"
                                :required="selectedStatus === 'on_hold'"
                                class="mt-2 block w-full rounded-lg border border-slate-300 px-4 py-3" :placeholder="selectedStatus === 'on_hold'
                                                        ? '例：交換部品の到着待ち、追加情報の確認中など'
                                                        : '作業内容や引き継ぎ事項など'">{{ old('message') }}</textarea>

                            @error('message')
                                <p class="mt-1 text-sm text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <button type="submit"
                            class="rounded-lg bg-blue-600 px-5 py-3 font-semibold text-white hover:bg-blue-700">
                            ステータスを更新する
                        </button>
                    </form>
                </section>
            @endif

            <section class="mt-10 border-t border-slate-200 pt-8">
                <h2 class="text-xl font-bold text-slate-900">
                    修理履歴
                </h2>

                @if ($repairRequest->repairLogs->isEmpty())
                    <p class="mt-4 rounded-lg bg-slate-50 p-5 text-sm text-slate-500">
                        まだ修理履歴はありません。
                    </p>
                @else
                    <ol class="mt-6 space-y-5">
                        @foreach ($repairRequest->repairLogs->sortByDesc('created_at') as $log)
                                                                                <li class="border-l-2 border-blue-200 pl-4">
                                                                                    <p class="font-semibold text-slate-900">
                                                                                        {{ match ($log->action) {
                                'request_created' => '修理依頼を作成',
                                'technician_assigned' => '担当技術者を割り当て',
                                'status_changed' => 'ステータス変更',
                                'cancellation_requested' => 'キャンセル申請',
                                'cancellation_approved' => 'キャンセル申請を承認',
                                'cancellation_rejected' => 'キャンセル申請を却下',
                                default => $log->action,
                            } }}
                                                                                    </p>

                                                                                    @if ($log->from_status || $log->to_status)
                                                                                                    <p class="mt-1 text-sm text-slate-600">
                                                                                                        {{ match ($log->from_status) {
                                    'received' => '受付済み',
                                    'in_progress' => '対応中',
                                    'on_hold' => '保留中',
                                    'completed' => '完了',
                                    'cancelled' => 'キャンセル済み',
                                    default => $log->from_status,
                                } }}
                                                                                                        →
                                                                                                        {{ match ($log->to_status) {
                                    'received' => '受付済み',
                                    'in_progress' => '対応中',
                                    'on_hold' => '保留中',
                                    'completed' => '完了',
                                    'cancelled' => 'キャンセル済み',
                                    default => $log->to_status,
                                } }}
                                                                                                    </p>
                                                                                    @endif

                                                                                    <p class="mt-2 whitespace-pre-wrap text-sm text-slate-700">
                                                                                        {{ $log->message }}
                                                                                    </p>

                                                                                    <p class="mt-2 text-xs text-slate-500">
                                                                                        記録者：{{ $log->user?->name ?? '不明' }}
                                                                                        ／
                                                                                        {{ $log->created_at
                                ? $log->created_at->copy()
                                    ->setTimezone('Asia/Tokyo')
                                    ->format('Y/m/d H:i')
                                : '日時不明' }}
                                                                                    </p>
                                                                                </li>
                        @endforeach
                    </ol>
                @endif
            </section>

            <div class="mt-8 flex flex-wrap gap-3">
                <a href="{{ route('repair-requests.index') }}"
                    class="rounded-lg bg-blue-600 px-5 py-3 font-semibold text-white hover:bg-blue-700">
                    一覧へ戻る
                </a>

                <a href="{{ route('dashboard') }}"
                    class="rounded-lg bg-slate-200 px-5 py-3 font-semibold text-slate-700 hover:bg-slate-300">
                    ダッシュボードへ戻る
                </a>
            </div>
        </section>
    </main>
</body>

</html>