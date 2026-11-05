<?php

namespace Database\Factories;

use App\Models\CancellationRequest;
use App\Models\RepairRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CancellationRequest>
 */
class CancellationRequestFactory extends Factory
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
            'reviewed_by' => null,
            'reason' => fake()->sentence(),
            'status' => 'pending',
            'review_comment' => null,
            'reviewed_at' => null,
        ];
    }

    /**
     * Ensure the requester matches the related repair request.
     */
    public function configure(): static
    {
        return $this->afterMaking(
            function (CancellationRequest $cancellationRequest): void {
                $cancellationRequest->requester_id =
                    $cancellationRequest->repairRequest->requester_id;
            }
        );
    }

    /**
     * Set the cancellation request status to pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'reviewed_by' => null,
            'review_comment' => null,
            'reviewed_at' => null,
        ]);
    }

    /**
     * Set the cancellation request status to approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'reviewed_by' => User::factory()->admin(),
            'review_comment' => 'キャンセル申請を承認しました。',
            'reviewed_at' => now(),
        ]);
    }

    /**
     * Set the cancellation request status to rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'reviewed_by' => User::factory()->admin(),
            'review_comment' => 'キャンセル申請を却下しました。',
            'reviewed_at' => now(),
        ]);
    }

    /**
     * Set the reviewer of the cancellation request.
     */
    public function reviewedBy(User $reviewer): static
    {
        return $this->state(fn (array $attributes) => [
            'reviewed_by' => $reviewer->getKey(),
        ]);
    }
}
