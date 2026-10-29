<?php

namespace Tests\Feature;

use App\Models\RepairRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RepairRequestStoreTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 依頼者は修理依頼を登録できる。
     */
    public function test_requester_can_store_repair_request(): void
    {
        $user = User::factory()->create([
            'role' => 'requester',
        ]);

        $response = $this->actingAs($user)->post(
            route('repair-requests.store'),
            [
                'title' => '空調設備の故障',
                'description' => '空調設備が動作しません。',
                'location' => '3階会議室',
                'asset_name' => '空調設備A',
                'priority' => 'high',
            ]
        );

        $response
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('status', '修理依頼を登録しました。');

        $this->assertDatabaseHas('repair_requests', [
            'requester_id' => $user->id,
            'title' => '空調設備の故障',
            'description' => '空調設備が動作しません。',
            'location' => '3階会議室',
            'asset_name' => '空調設備A',
            'priority' => 'high',
            'status' => 'received',
            'assigned_to' => null,
        ]);

        $this->assertDatabaseHas('repair_logs', [
            'repair_request_id' => RepairRequest::query()
                ->where('requester_id', $user->id)
                ->where('title', '空調設備の故障')
                ->value('id'),
            'user_id' => $user->id,
            'action' => 'request_created',
            'message' => '修理依頼を登録しました。',
            'from_status' => null,
            'to_status' => 'received',
        ]);
    }

    /**
     * 入力不備がある場合は登録できない。
     */
    public function test_invalid_input_is_rejected(): void
    {
        $user = User::factory()->create([
            'role' => 'requester',
        ]);

        $response = $this->actingAs($user)->post(
            route('repair-requests.store'),
            [
                'title' => '',
                'description' => '',
                'location' => '',
                'asset_name' => '',
                'priority' => 'urgent',
            ]
        );

        $response->assertSessionHasErrors([
            'title',
            'description',
            'location',
            'asset_name',
            'priority',
        ]);

        $this->assertDatabaseCount('repair_requests', 0);
        $this->assertDatabaseCount('repair_logs', 0);
    }

    /**
     * 依頼者以外は修理依頼を登録できない。
     */
    public function test_non_requester_cannot_store_repair_request(): void
    {
        $technician = User::factory()->create([
            'role' => 'technician',
        ]);

        $response = $this->actingAs($technician)->post(
            route('repair-requests.store'),
            [
                'title' => '空調設備の故障',
                'description' => '空調設備が動作しません。',
                'location' => '3階会議室',
                'asset_name' => '空調設備A',
                'priority' => 'normal',
            ]
        );

        $response->assertForbidden();

        $this->assertDatabaseCount('repair_requests', 0);
        $this->assertDatabaseCount('repair_logs', 0);
    }

    /**
     * 未ログインユーザーは登録画面にアクセスできない。
     */
    public function test_guest_cannot_access_create_page(): void
    {
        $response = $this->get(route('repair-requests.create'));

        $response->assertRedirect(route('login'));
    }

    /**
     * フォームから送信された管理項目は採用しない。
     */
    public function test_client_cannot_override_requester_status_or_assignee(): void
    {
        $user = User::factory()->create([
            'role' => 'requester',
        ]);

        $otherUser = User::factory()->create([
            'role' => 'requester',
        ]);

        $response = $this->actingAs($user)->post(
            route('repair-requests.store'),
            [
                'title' => 'プリンターの故障',
                'description' => '印刷できません。',
                'location' => '事務室',
                'asset_name' => 'プリンターB',
                'priority' => 'normal',
                'requester_id' => $otherUser->id,
                'assigned_to' => $otherUser->id,
                'status' => 'completed',
            ]
        );

        $response->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('repair_requests', [
            'requester_id' => $user->id,
            'title' => 'プリンターの故障',
            'assigned_to' => null,
            'status' => 'received',
        ]);

        $this->assertDatabaseMissing('repair_requests', [
            'requester_id' => $otherUser->id,
            'title' => 'プリンターの故障',
        ]);
    }
}
