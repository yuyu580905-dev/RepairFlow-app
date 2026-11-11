<?php

namespace Database\Seeders;

use App\Models\RepairRequest;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use InvalidArgumentException;

class RepairRequestSeeder extends Seeder
{
    /**
     * 開発・デモ用の修理依頼を作成する。
     */
    public function run(): void
    {
        /** @var User $requester1 */
        $requester1 = User::query()
            ->where('email', 'requester@example.com')
            ->firstOrFail();

        /** @var User $requester2 */
        $requester2 = User::query()
            ->where('email', 'requester2@example.com')
            ->firstOrFail();

        /** @var User $requester3 */
        $requester3 = User::query()
            ->where('email', 'requester3@example.com')
            ->firstOrFail();

        /** @var User $technician1 */
        $technician1 = User::query()
            ->where('email', 'technician@example.com')
            ->firstOrFail();

        /** @var User $technician2 */
        $technician2 = User::query()
            ->where('email', 'technician2@example.com')
            ->firstOrFail();

        $createdAt = CarbonImmutable::parse('2026-11-01 09:00:00');

        // 受付済み
        $this->createRepairRequest(
            $requester1,
            null,
            'received',
            'high',
            [
                'title' => '会議室の空調設備が動作しない',
                'description' => '会議室のエアコンが起動せず、室内の温度調整ができません。',
                'location' => '本社3階',
                'asset_name' => '業務用エアコン',
            ],
            $createdAt,
        );

        $createdAt = $createdAt->addHour();

        $this->createRepairRequest(
            $requester2,
            null,
            'received',
            'normal',
            [
                'title' => '事務室の照明が点灯しない',
                'description' => '事務室の天井照明が一部点灯しなくなりました。',
                'location' => '本社2階',
                'asset_name' => 'LED照明',
            ],
            $createdAt,
        );

        $createdAt = $createdAt->addHour();

        $this->createRepairRequest(
            $requester3,
            null,
            'received',
            'normal',
            [
                'title' => '会議室のプロジェクターが映らない',
                'description' => '会議室のプロジェクターに電源は入りますが、映像が表示されません。',
                'location' => '本社4階',
                'asset_name' => 'プロジェクター',
            ],
            $createdAt,
        );

        $createdAt = $createdAt->addHour();

        // 対応中
        $this->createRepairRequest(
            $requester3,
            $technician1,
            'in_progress',
            'high',
            [
                'title' => '業務用プリンターの印刷不良',
                'description' => '印刷すると用紙に黒い線が入る症状が発生しています。',
                'location' => '本社1階',
                'asset_name' => '業務用プリンター',
            ],
            $createdAt,
        );

        $createdAt = $createdAt->addHour();

        $this->createRepairRequest(
            $requester1,
            $technician2,
            'in_progress',
            'low',
            [
                'title' => '会議室モニターの接続不良',
                'description' => 'パソコンと会議室モニターの接続が不安定です。',
                'location' => '本社4階',
                'asset_name' => '会議室モニター',
            ],
            $createdAt,
        );

        $createdAt = $createdAt->addHour();

        // 保留中
        $this->createRepairRequest(
            $requester2,
            $technician1,
            'on_hold',
            'normal',
            [
                'title' => 'サーバーラックの冷却ファン異常',
                'description' => '冷却ファンから異音が発生しており、交換部品を確認中です。',
                'location' => '本社サーバー室',
                'asset_name' => '冷却ファン',
            ],
            $createdAt,
        );

        $createdAt = $createdAt->addHour();

        $this->createRepairRequest(
            $requester3,
            $technician2,
            'on_hold',
            'low',
            [
                'title' => '事務室の複合機にエラー表示',
                'description' => '複合機にエラーコードが表示され、原因を調査しています。',
                'location' => '本社2階',
                'asset_name' => '複合機',
            ],
            $createdAt,
        );

        $createdAt = $createdAt->addHour();

        // 完了
        $this->createRepairRequest(
            $requester1,
            $technician2,
            'completed',
            'normal',
            [
                'title' => '応接室の照明スイッチ故障',
                'description' => '照明スイッチを押しても反応しない状態でした。',
                'location' => '本社5階',
                'asset_name' => '照明スイッチ',
            ],
            $createdAt,
        );

        $createdAt = $createdAt->addHour();

        $this->createRepairRequest(
            $requester2,
            $technician1,
            'completed',
            'low',
            [
                'title' => 'ネットワーク機器の通信障害',
                'description' => 'ネットワーク機器の通信が不安定になる問題が発生していました。',
                'location' => '本社サーバー室',
                'asset_name' => 'ネットワークスイッチ',
            ],
            $createdAt,
        );

        $createdAt = $createdAt->addHour();

        // キャンセル済み
        $this->createRepairRequest(
            $requester3,
            $technician1,
            'cancelled',
            'high',
            [
                'title' => '倉庫の空調設備修理',
                'description' => '倉庫の空調設備に不具合が発生していました。',
                'location' => '本社倉庫',
                'asset_name' => '空調設備',
            ],
            $createdAt,
        );

        $createdAt = $createdAt->addHour();

        $this->createRepairRequest(
            $requester1,
            null,
            'cancelled',
            'normal',
            [
                'title' => '事務室のデスクライト故障',
                'description' => 'デスクライトが点灯しなくなりました。',
                'location' => '本社3階',
                'asset_name' => 'デスクライト',
            ],
            $createdAt,
        );

        $createdAt = $createdAt->addHour();
    }

    /**
     * Factoryを使用して修理依頼を作成する。
     *
     * @param  array<string, string>  $attributes
     */
    private function createRepairRequest(
        User $requester,
        ?User $technician,
        string $status,
        string $priority,
        array $attributes,
        CarbonImmutable $createdAt,
    ): RepairRequest {
        $factory = RepairRequest::factory()
            ->for($requester, 'requester');

        $factory = match ($status) {
            'received' => $factory->received(),
            'in_progress' => $factory->inProgress(),
            'on_hold' => $factory->onHold(),
            'completed' => $factory->completed(),
            'cancelled' => $factory->cancelled(),
            default => throw new InvalidArgumentException(
                "不正な修理依頼ステータスです: {$status}"
            ),
        };

        $factory = match ($priority) {
            'low' => $factory->lowPriority(),
            'normal' => $factory->normalPriority(),
            'high' => $factory->highPriority(),
            default => throw new InvalidArgumentException(
                "不正な優先度です: {$priority}"
            ),
        };

        $factory = $technician !== null
            ? $factory->assignedTo($technician)
            : $factory->unassigned();

        $repairRequest = $factory->make($attributes);

        $repairRequest->forceFill([
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);

        $repairRequest->save();

        return $repairRequest;
    }
}
