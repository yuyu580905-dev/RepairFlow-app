<?php

namespace Tests\Feature;

use App\Models\RepairRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RepairRequestShowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 依頼者は自分の修理依頼を閲覧できる。
     */
    public function test_requester_can_view_own_repair_request(): void
    {
        $requester = User::factory()->create([
            'role' => 'requester',
        ]);

        $repairRequest = $this->createRepairRequest(
            $requester,
            '自分の修理依頼'
        );

        $response = $this->actingAs($requester)->get(
            route('repair-requests.show', $repairRequest)
        );

        $response
            ->assertOk()
            ->assertSee('自分の修理依頼')
            ->assertSee('テスト用の修理内容です。')
            ->assertSee($requester->name);
    }

    /**
     * 依頼者は他人の修理依頼を閲覧できない。
     */
    public function test_requester_cannot_view_others_repair_request(): void
    {
        $owner = User::factory()->create([
            'role' => 'requester',
        ]);

        $otherRequester = User::factory()->create([
            'role' => 'requester',
        ]);

        $repairRequest = $this->createRepairRequest(
            $owner,
            '他人の修理依頼'
        );

        $response = $this->actingAs($otherRequester)->get(
            route('repair-requests.show', $repairRequest)
        );

        $response->assertForbidden();
    }

    /**
     * 技術者は担当案件を閲覧できる。
     */
    public function test_technician_can_view_assigned_repair_request(): void
    {
        $requester = User::factory()->create([
            'role' => 'requester',
        ]);

        $technician = User::factory()->create([
            'role' => 'technician',
        ]);

        $repairRequest = $this->createRepairRequest(
            $requester,
            '担当中の修理依頼',
            $technician
        );

        $response = $this->actingAs($technician)->get(
            route('repair-requests.show', $repairRequest)
        );

        $response
            ->assertOk()
            ->assertSee('担当中の修理依頼')
            ->assertSee($technician->name);
    }

    /**
     * 技術者は他人の担当案件を閲覧できない。
     */
    public function test_technician_cannot_view_unassigned_repair_request(): void
    {
        $requester = User::factory()->create([
            'role' => 'requester',
        ]);

        $technician = User::factory()->create([
            'role' => 'technician',
        ]);

        $repairRequest = $this->createRepairRequest(
            $requester,
            '未割り当ての修理依頼'
        );

        $response = $this->actingAs($technician)->get(
            route('repair-requests.show', $repairRequest)
        );

        $response->assertForbidden();
    }

    /**
     * 管理者はすべての修理依頼を閲覧できる。
     */
    public function test_admin_can_view_any_repair_request(): void
    {
        $requester = User::factory()->create([
            'role' => 'requester',
        ]);

        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $repairRequest = $this->createRepairRequest(
            $requester,
            '管理者が確認する修理依頼'
        );

        $response = $this->actingAs($admin)->get(
            route('repair-requests.show', $repairRequest)
        );

        $response
            ->assertOk()
            ->assertSee('管理者が確認する修理依頼');
    }

    /**
     * 未ログインユーザーは詳細を閲覧できない。
     */
    public function test_guest_cannot_access_repair_request_detail(): void
    {
        $requester = User::factory()->create([
            'role' => 'requester',
        ]);

        $repairRequest = $this->createRepairRequest(
            $requester,
            'ログイン必須の修理依頼'
        );

        $response = $this->get(
            route('repair-requests.show', $repairRequest)
        );

        $response->assertRedirect(route('login'));
    }

    /**
     * 修理依頼をテスト用に作成する。
     */
    private function createRepairRequest(
        User $requester,
        string $title,
        ?User $technician = null
    ): RepairRequest {
        return $requester->repairRequests()->create([
            'title' => $title,
            'description' => 'テスト用の修理内容です。',
            'location' => 'テスト事務室',
            'asset_name' => 'テスト設備',
            'priority' => 'normal',
            'assigned_to' => $technician?->id,
            'status' => 'received',
        ]);
    }
}
