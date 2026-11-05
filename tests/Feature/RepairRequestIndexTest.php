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
        $requester = User::factory()->requester()->create();
        $otherRequester = User::factory()->requester()->create();

        RepairRequest::factory()
            ->for($requester, 'requester')
            ->received()
            ->normalPriority()
            ->create([
                'title' => '自分の修理依頼',
            ]);

        RepairRequest::factory()
            ->for($otherRequester, 'requester')
            ->received()
            ->normalPriority()
            ->create([
                'title' => '他人の修理依頼',
            ]);

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
        $requester = User::factory()->requester()->create();
        $technician = User::factory()->technician()->create();
        $otherTechnician = User::factory()->technician()->create();

        RepairRequest::factory()
            ->for($requester, 'requester')
            ->assignedTo($technician)
            ->received()
            ->normalPriority()
            ->create([
                'title' => '自分の担当案件',
            ]);

        RepairRequest::factory()
            ->for($requester, 'requester')
            ->assignedTo($otherTechnician)
            ->received()
            ->normalPriority()
            ->create([
                'title' => '他人の担当案件',
            ]);

        RepairRequest::factory()
            ->for($requester, 'requester')
            ->received()
            ->normalPriority()
            ->create([
                'title' => '未割り当ての案件',
            ]);

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
        $requester = User::factory()->requester()->create();
        $technician = User::factory()->technician()->create();

        RepairRequest::factory()
            ->for($requester, 'requester')
            ->received()
            ->normalPriority()
            ->create([
                'title' => '依頼A',
            ]);

        RepairRequest::factory()
            ->for($requester, 'requester')
            ->assignedTo($technician)
            ->received()
            ->normalPriority()
            ->create([
                'title' => '依頼B',
            ]);

        $admin = User::factory()->admin()->create();

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
}
