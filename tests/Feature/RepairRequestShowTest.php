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
        $requester = User::factory()->requester()->create();

        $repairRequest = RepairRequest::factory()
            ->for($requester, 'requester')
            ->received()
            ->normalPriority()
            ->create([
                'title' => '自分の修理依頼',
                'description' => 'テスト用の修理内容です。',
                'location' => 'テスト事務室',
                'asset_name' => 'テスト設備',
            ]);

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
        $owner = User::factory()->requester()->create();
        $otherRequester = User::factory()->requester()->create();

        $repairRequest = RepairRequest::factory()
            ->for($owner, 'requester')
            ->received()
            ->create([
                'title' => '他人の修理依頼',
            ]);

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
        $requester = User::factory()->requester()->create();
        $technician = User::factory()->technician()->create();

        $repairRequest = RepairRequest::factory()
            ->for($requester, 'requester')
            ->assignedTo($technician)
            ->received()
            ->create([
                'title' => '担当中の修理依頼',
            ]);

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
        $requester = User::factory()->requester()->create();
        $technician = User::factory()->technician()->create();

        $repairRequest = RepairRequest::factory()
            ->for($requester, 'requester')
            ->received()
            ->create([
                'title' => '未割り当ての修理依頼',
            ]);

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
        $requester = User::factory()->requester()->create();
        $admin = User::factory()->admin()->create();

        $repairRequest = RepairRequest::factory()
            ->for($requester, 'requester')
            ->received()
            ->create([
                'title' => '管理者が確認する修理依頼',
            ]);

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
        $requester = User::factory()->requester()->create();

        $repairRequest = RepairRequest::factory()
            ->for($requester, 'requester')
            ->received()
            ->create([
                'title' => 'ログイン必須の修理依頼',
            ]);

        $response = $this->get(
            route('repair-requests.show', $repairRequest)
        );

        $response->assertRedirect(route('login'));
    }
}
