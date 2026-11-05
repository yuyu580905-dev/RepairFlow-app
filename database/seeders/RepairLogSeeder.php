<?php

namespace Database\Seeders;

use App\Models\CancellationRequest;
use App\Models\RepairLog;
use App\Models\RepairRequest;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use RuntimeException;

class RepairLogSeeder extends Seeder
{
    /**
     * 開発・デモ用の修理履歴を作成する。
     */
    public function run(): void
    {
        /** @var User $admin */
        $admin = User::query()
            ->where('email', 'admin@example.com')
            ->firstOrFail();

        /** @var User $technician1 */
        $technician1 = User::query()
            ->where('email', 'technician@example.com')
            ->firstOrFail();

        $repairRequests = RepairRequest::query()
            ->with(['requester', 'assignedTechnician'])
            ->get()
            ->keyBy('title');

        $cancellationRequests = CancellationRequest::query()
            ->with(['repairRequest', 'requester'])
            ->get()
            ->groupBy('repair_request_id');

        foreach ($repairRequests as $repairRequest) {
            $requester = $repairRequest->requester;

            if ($requester === null) {
                throw new RuntimeException(
                    "修理依頼の依頼者が存在しません: {$repairRequest->title}"
                );
            }

            // 修理依頼の登録日時を履歴の起点にする。
            $time = CarbonImmutable::parse($repairRequest->created_at);

            // 修理依頼の登録
            $this->createLog(
                $repairRequest,
                $requester,
                'requestCreated',
                $time,
            );

            $time = $time->addMinutes(10);

            // 担当技術者の割り当て・再割り当て
            if ($repairRequest->assignedTechnician !== null) {
                if ($repairRequest->title === '会議室モニターの接続不良') {
                    // 最初は技術者1を割り当てる。
                    $this->createLog(
                        $repairRequest,
                        $admin,
                        'technicianAssigned',
                        $time,
                        message: "担当技術者を「{$technician1->name}」に割り当てました。",
                    );

                    $time = $time->addMinutes(10);

                    // 技術者1から技術者2へ再割り当てる。
                    $this->createLog(
                        $repairRequest,
                        $admin,
                        'technicianAssigned',
                        $time,
                        message: "担当技術者を「{$technician1->name}」から「{$repairRequest->assignedTechnician->name}」に再割り当てしました。",
                    );

                    $time = $time->addMinutes(10);
                } else {
                    $this->createLog(
                        $repairRequest,
                        $admin,
                        'technicianAssigned',
                        $time,
                    );

                    $time = $time->addMinutes(10);
                }
            }

            // ステータス変更履歴。更新後の時刻を受け取る。
            $time = $this->createStatusHistory(
                $repairRequest,
                $time,
            );

            // キャンセル申請履歴
            $requests = $cancellationRequests
                ->get($repairRequest->id, collect())
                ->sortBy('created_at');

            foreach ($requests as $cancellationRequest) {
                $cancellationRequester = $cancellationRequest->requester;

                if ($cancellationRequester === null) {
                    throw new RuntimeException(
                        "キャンセル申請の依頼者が存在しません: {$repairRequest->title}"
                    );
                }

                // 実際のキャンセル申請日時を使用する。
                $requestedAt = CarbonImmutable::parse(
                    $cancellationRequest->created_at
                );

                $this->createLog(
                    $repairRequest,
                    $cancellationRequester,
                    'cancellationRequested',
                    $requestedAt,
                    ['status' => 'received'],
                );

                // 審査済み申請は実際の審査日時で履歴を作成する。
                if (in_array($cancellationRequest->status, ['approved', 'rejected'], true)) {
                    if ($cancellationRequest->reviewed_at === null) {
                        throw new RuntimeException(
                            "審査日時が設定されていません: {$repairRequest->title}"
                        );
                    }

                    $reviewedAt = CarbonImmutable::parse(
                        $cancellationRequest->reviewed_at
                    );

                    $state = $cancellationRequest->status === 'approved'
                        ? 'cancellationApproved'
                        : 'cancellationRejected';

                    $this->createLog(
                        $repairRequest,
                        $admin,
                        $state,
                        $reviewedAt,
                        ['status' => 'received'],
                    );
                }
            }
        }
    }

    /**
     * 指定したアクションの修理履歴を作成する。
     *
     * @param  array<string, string>  $stateArguments
     */
    private function createLog(
        RepairRequest $repairRequest,
        User $user,
        string $state,
        CarbonImmutable $createdAt,
        array $stateArguments = [],
        ?string $message = null,
    ): void {
        $factory = RepairLog::factory()
            ->for($repairRequest, 'repairRequest')
            ->for($user, 'user');

        $factory = match ($state) {
            'requestCreated' => $factory->requestCreated(),
            'technicianAssigned' => $factory->technicianAssigned(),
            'statusChanged' => $factory->statusChanged(
                $stateArguments['from_status'],
                $stateArguments['to_status'],
            ),
            'cancellationRequested' => $factory->cancellationRequested(
                $stateArguments['status'],
            ),
            'cancellationApproved' => $factory->cancellationApproved(),
            'cancellationRejected' => $factory->cancellationRejected(
                $stateArguments['status'],
            ),
            default => throw new RuntimeException(
                "不正な履歴アクションです: {$state}"
            ),
        };

        $log = $factory->make();

        $attributes = [
            'created_at' => $createdAt,
        ];

        if ($message !== null) {
            $attributes['message'] = $message;
        }

        $log->forceFill($attributes);

        $log->save();
    }

    /**
     * 修理依頼の現在のステータスに応じた変更履歴を作成する。
     *
     * @return CarbonImmutable 次の履歴に使用する日時
     */
    private function createStatusHistory(
        RepairRequest $repairRequest,
        CarbonImmutable $createdAt,
    ): CarbonImmutable {
        $transitions = match ($repairRequest->status) {
            'received', 'cancelled' => [],
            'in_progress' => [
                ['received', 'in_progress'],
            ],
            'on_hold' => [
                ['received', 'in_progress'],
                ['in_progress', 'on_hold'],
            ],
            'completed' => [
                ['received', 'in_progress'],
                ['in_progress', 'completed'],
            ],
            default => throw new RuntimeException(
                "不正な修理依頼ステータスです: {$repairRequest->status}"
            ),
        };

        foreach ($transitions as [$fromStatus, $toStatus]) {
            $user = $repairRequest->assignedTechnician;

            if ($user === null) {
                throw new RuntimeException(
                    "担当技術者が存在しません: {$repairRequest->title}"
                );
            }

            $this->createLog(
                $repairRequest,
                $user,
                'statusChanged',
                $createdAt,
                [
                    'from_status' => $fromStatus,
                    'to_status' => $toStatus,
                ],
            );

            $createdAt = $createdAt->addMinutes(10);
        }

        return $createdAt;
    }
}
