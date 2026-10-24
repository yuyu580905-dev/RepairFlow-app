<?php

namespace Tests\Feature;

use App\Models\RepairRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RepairRequestIndexTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 依頼者には自分の修理依頼だけ表示する。
     */
    public function test_requester_sees_only_own_repair_requests(): void
    {
        $requester = User::factory()->create([
            'role' => 'requester',
        ]);

        $otherRequester = User::factory()->create([
            'role' => 'requester',
        ]);

        $this->createRepairRequest($requester, '自分の修理依頼');
        $this->createRepairRequest($otherRequester, '他人の修理依頼');

        $response = $this->actingAs($requester)->get(
            route('repair-requests.index')
        );

        $response
            ->assertOk()
            ->assertSee('自分の修理依頼')
            ->assertDontSee('他人の修理依頼');
    }

    /**
     * 技術者には自分の担当案件だけ表示する。
     */
    public function test_technician_sees_only_assigned_repair_requests(): void
    {
        $requester = User::factory()->create([
            'role' => 'requester',
        ]);

        $technician = User::factory()->create([
            'role' => 'technician',
        ]);

        $otherTechnician = User::factory()->create([
            'role' => 'technician',
        ]);

        $this->createRepairRequest(
            $requester,
            '自分の担当案件',
            $technician
        );

        $this->createRepairRequest(
            $requester,
            '他人の担当案件',
            $otherTechnician
        );

        $this->createRepairRequest(
            $requester,
            '未割り当ての案件'
        );

        $response = $this->actingAs($technician)->get(
            route('repair-requests.index')
        );

        $response
            ->assertOk()
            ->assertSee('自分の担当案件')
            ->assertDontSee('他人の担当案件')
            ->assertDontSee('未割り当ての案件');
    }

    /**
     * 管理者にはすべての修理依頼を表示する。
     */
    public function test_admin_sees_all_repair_requests(): void
    {
        $requester = User::factory()->create([
            'role' => 'requester',
        ]);

        $technician = User::factory()->create([
            'role' => 'technician',
        ]);

        $this->createRepairRequest($requester, '依頼A');
        $this->createRepairRequest($requester, '依頼B', $technician);

        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->get(
            route('repair-requests.index')
        );

        $response
            ->assertOk()
            ->assertSee('依頼A')
            ->assertSee('依頼B');
    }

    /**
     * 未ログインユーザーは一覧を閲覧できない。
     */
    public function test_guest_cannot_access_repair_request_index(): void
    {
        $response = $this->get(route('repair-requests.index'));

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
