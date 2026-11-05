<?php

namespace Database\Factories;

use App\Models\RepairLog;
use App\Models\RepairRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RepairLog>
 */
class RepairLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'repair_request_id' => RepairRequest::factory(),
            'user_id' => User::factory()->admin(),
            'action' => 'request_created',
            'message' => '修理依頼を登録しました。',
            'from_status' => null,
            'to_status' => 'received',
        ];
    }

    /**
     * Set the log action to request created.
     */
    public function requestCreated(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'request_created',
            'message' => '修理依頼を登録しました。',
            'from_status' => null,
            'to_status' => 'received',
        ]);
    }

    /**
     * Set the log action to technician assigned.
     */
    public function technicianAssigned(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'technician_assigned',
            'message' => '担当技術者を割り当てました。',
            'from_status' => null,
            'to_status' => null,
        ]);
    }

    /**
     * Set the log action to status changed.
     */
    public function statusChanged(
        string $fromStatus,
        string $toStatus
    ): static {
        return $this->state(fn (array $attributes) => [
            'action' => 'status_changed',
            'message' => sprintf(
                'ステータスを「%s」から「%s」に変更しました。',
                $this->statusLabel($fromStatus),
                $this->statusLabel($toStatus),
            ),
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
        ]);
    }

    /**
     * Set the log action to cancellation requested.
     */
    public function cancellationRequested(
        string $status = 'received'
    ): static {
        return $this->state(fn (array $attributes) => [
            'action' => 'cancellation_requested',
            'message' => 'キャンセル申請を提出しました。',
            'from_status' => $status,
            'to_status' => $status,
        ]);
    }

    /**
     * Set the log action to cancellation approved.
     */
    public function cancellationApproved(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'cancellation_approved',
            'message' => 'キャンセル申請を承認しました。',
            'from_status' => 'received',
            'to_status' => 'cancelled',
        ]);
    }

    /**
     * Set the log action to cancellation rejected.
     */
    public function cancellationRejected(
        string $status = 'received'
    ): static {
        return $this->state(fn (array $attributes) => [
            'action' => 'cancellation_rejected',
            'message' => 'キャンセル申請を却下しました。',
            'from_status' => $status,
            'to_status' => $status,
        ]);
    }

    /**
     * Return the Japanese label for a repair request status.
     */
    private function statusLabel(string $status): string
    {
        return match ($status) {
            'received' => '受付済み',
            'in_progress' => '対応中',
            'on_hold' => '保留中',
            'completed' => '完了',
            'cancelled' => 'キャンセル済み',
            default => $status,
        };
    }
}
