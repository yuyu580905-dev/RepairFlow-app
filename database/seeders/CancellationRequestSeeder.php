<?php

namespace Database\Seeders;

use App\Models\CancellationRequest;
use App\Models\RepairRequest;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

class CancellationRequestSeeder extends Seeder
{
    /**
     * キャンセル申請のデモデータを登録する。
     */
    public function run(): void
    {
        $admin = User::query()
            ->where('email', 'admin@example.com')
            ->firstOrFail();

        $createdAt = CarbonImmutable::parse('2026-11-02 09:00:00');

        // キャンセル済みの修理依頼
        $cancelledRequests = RepairRequest::query()
            ->where('status', 'cancelled')
            ->orderBy('id')
            ->get();

        // 受付済みの修理依頼
        $receivedRequests = RepairRequest::query()
            ->where('status', 'received')
            ->orderBy('id')
            ->get();

        // 今回のデモデータに必要な修理依頼が存在することを確認する。
        if ($cancelledRequests->count() < 2 || $receivedRequests->count() < 2) {
            throw new \RuntimeException(
                'キャンセル申請の登録に必要な修理依頼が不足しています。'
            );
        }

        // 承認済み申請①
        $cancellationRequest = CancellationRequest::factory()
            ->for($cancelledRequests[0], 'repairRequest')
            ->approved()
            ->reviewedBy($admin)
            ->make([
                'reason' => '修理対応を依頼する必要がなくなったため。',
            ]);

        $reviewedAt = $createdAt->addMinutes(15);

        $cancellationRequest->forceFill([
            'created_at' => $createdAt,
            'updated_at' => $reviewedAt,
            'reviewed_at' => $reviewedAt,
        ]);

        $cancellationRequest->save();

        $createdAt = $createdAt->addHour();

        // 承認済み申請②
        $cancellationRequest = CancellationRequest::factory()
            ->for($cancelledRequests[1], 'repairRequest')
            ->approved()
            ->reviewedBy($admin)
            ->make([
                'reason' => '設備の交換が不要になったため。',
            ]);

        $reviewedAt = $createdAt->addMinutes(15);

        $cancellationRequest->forceFill([
            'created_at' => $createdAt,
            'updated_at' => $reviewedAt,
            'reviewed_at' => $reviewedAt,
        ]);

        $cancellationRequest->save();

        $createdAt = $createdAt->addHour();

        // 却下済み申請
        $cancellationRequest = CancellationRequest::factory()
            ->for($receivedRequests[0], 'repairRequest')
            ->rejected()
            ->reviewedBy($admin)
            ->make([
                'reason' => '修理依頼を取り消したいため。',
                'review_comment' => 'すでに修理対応の準備が進んでいるため、今回は却下しました。',
            ]);

        $reviewedAt = $createdAt->addMinutes(15);

        $cancellationRequest->forceFill([
            'created_at' => $createdAt,
            'updated_at' => $reviewedAt,
            'reviewed_at' => $reviewedAt,
        ]);

        $cancellationRequest->save();

        $createdAt = $createdAt->addHour();

        // 再申請中の申請
        $cancellationRequest = CancellationRequest::factory()
            ->for($receivedRequests[0], 'repairRequest')
            ->pending()
            ->make([
                'reason' => '事情が変わったため、改めてキャンセルを申請します。',
            ]);

        $cancellationRequest->forceFill([
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
            'reviewed_at' => null,
        ]);

        $cancellationRequest->save();

        $createdAt = $createdAt->addHour();

        // 未審査申請
        $cancellationRequest = CancellationRequest::factory()
            ->for($receivedRequests[1], 'repairRequest')
            ->pending()
            ->make([
                'reason' => '修理依頼を取り消す必要が生じたため。',
            ]);

        $cancellationRequest->forceFill([
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
            'reviewed_at' => null,
        ]);

        $cancellationRequest->save();
    }
}
