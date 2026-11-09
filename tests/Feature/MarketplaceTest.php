<?php

namespace Tests\Feature;

use App\Models\Offer;
use App\Models\Order;
use App\Models\Service;
use App\Models\Skill;
use App\Models\User;
use App\Services\OrderFlow;
use App\Support\Split;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketplaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_chooser_and_worker_dashboard_load(): void
    {
        $this->seed();
        $this->actingAs($this->sara());

        $this->get('/')->assertOk()->assertSee('اختر مسارك', false)->assertDontSee('بائعة', false);
        $this->get('/worker')->assertOk()->assertSee('أهلاً سارة', false)->assertSee('405', false);
        $this->get(route('worker.analytics'))->assertOk()->assertSee('405', false);
    }

    public function test_worker_can_claim_a_task_and_finish_the_first_step(): void
    {
        $this->seed();
        $this->actingAs($this->sara());

        $offer = Offer::query()->where('active', true)->orderBy('id')->firstOrFail();

        $this->post(route('worker.claim', $offer))->assertRedirect();

        $order = Order::query()->where('title', $offer->title)->latest('id')->firstOrFail();
        $step = $order->steps()->orderBy('position')->firstOrFail();

        $this->post(route('worker.orders.step', [$order, $step]), ['note' => 'الإضاءة شمالية'])
            ->assertRedirect();

        $this->assertNotNull($step->fresh()->completed_at);
        $this->assertFalse($offer->fresh()->active);
    }

    public function test_level_three_worker_cannot_claim_a_level_four_offer(): void
    {
        $this->seed();
        $this->actingAs($this->sara());

        $offer = Offer::query()->where('min_level', 4)->firstOrFail();

        $this->from(route('worker.home'))
            ->post(route('worker.claim', $offer))
            ->assertRedirect(route('worker.home'))
            ->assertSessionHasErrors('offer');

        $this->assertTrue($offer->fresh()->active);
    }

    public function test_client_prices_follow_speed_discount_and_tracking(): void
    {
        $this->seed();
        $paint = Service::query()->where('slug', 'paint')->firstOrFail();

        $this->place($paint, 'normal', null, false);
        $this->assertSame(200, Order::query()->latest('id')->first()->total);

        $this->place($paint, 'rush', null, false);
        $this->assertSame(300, Order::query()->latest('id')->first()->total);

        $this->place($paint, 'normal', 'THIQA10', false);
        $discounted = Order::query()->latest('id')->first();
        $this->assertSame(180, $discounted->total);
        $this->assertSame(
            User::query()->where('email', 'sara@hadthiqa.test')->value('id'),
            $discounted->referrer_id
        );

        $this->place($paint, 'normal', 'THIQA10', true);
        $tracked = Order::query()->latest('id')->first();
        $this->assertSame(330, $tracked->total);
        $this->assertSame(150, $tracked->tracking_fee);
        $this->assertSame(180, $tracked->distributable());
        $this->assertSame(81, $tracked->workerShare());
        $this->assertSame(18, Split::amount($tracked->distributable(), 'platform'));
    }

    public function test_seeded_paint_share_stays_eighty_and_platform_thirty(): void
    {
        $this->seed();

        $order = Order::query()->where('number', 1038)->firstOrFail();

        $this->assertSame(90, $order->workerShare());
        $this->assertSame(20, collect($order->shares())->firstWhere('id', 'platform')['amount']);
    }

    public function test_skill_result_waits_for_confirmation(): void
    {
        $this->seed();
        $this->actingAs($this->sara());

        $offer = Offer::query()->where('active', true)->orderBy('id')->firstOrFail();
        $this->post(route('worker.claim', $offer));
        $order = Order::query()->latest('id')->firstOrFail();
        $human = $order->steps()->orderBy('position')->firstOrFail();
        $this->post(route('worker.orders.step', [$order, $human]));

        $skill = $order->steps()->orderBy('position')->skip(1)->firstOrFail();
        $this->post(route('worker.orders.skill', [$order, $skill]))->assertRedirect();
        $skill->refresh();
        $this->assertNull($skill->completed_at);
        $this->assertNotEmpty($skill->result);

        $this->post(route('worker.orders.skill.confirm', [$order, $skill]))->assertRedirect();
        $this->assertNotNull($skill->fresh()->completed_at);
    }

    public function test_admin_can_approve_a_pending_skill_after_switching_role(): void
    {
        $this->seed();
        $skill = Skill::query()->where('status', 'pending')->firstOrFail();

        $this->post(route('admin.skills.review', $skill), ['decision' => 'approved'])
            ->assertRedirect(route('login', ['as' => 'admin']));
        $this->assertSame('pending', $skill->fresh()->status);

        $this->actingAs(User::query()->where('email', 'admin@hadthiqa.test')->firstOrFail());
        $this->post(route('admin.skills.review', $skill), ['decision' => 'approved'])
            ->assertRedirect();
        $this->assertSame('approved', $skill->fresh()->status);
        $this->assertNotNull($skill->fresh()->reviewed_at);
    }

    public function test_seller_pages_stay_hidden_and_admin_sees_workers(): void
    {
        $this->seed();

        $this->get(route('login', ['as' => 'seller']))->assertRedirect(route('home'));
        $this->get(route('register', ['as' => 'seller']))->assertNotFound();
        $this->get(route('seller.home'))->assertRedirect(route('home'));

        $this->actingAs(User::query()->where('email', 'admin@hadthiqa.test')->firstOrFail());
        $this->get(route('admin.home'))
            ->assertOk()
            ->assertSee('سارة', false)
            ->assertDontSee('بائع', false);
        $this->get(route('admin.analytics'))
            ->assertOk()
            ->assertDontSee('بائع', false);
    }

    public function test_a_seller_account_cannot_open_the_client_or_seller_panels(): void
    {
        $this->seed();
        $this->actingAs(User::query()->where('email', 'seller@hadthiqa.test')->firstOrFail());

        $foreign = Order::query()->where('number', 1038)->firstOrFail();

        $this->get(route('seller.gigs.show', Service::query()->where('slug', 'paint')->firstOrFail()))->assertRedirect(route('home'));
        $this->get(route('seller.orders.show', $foreign))->assertRedirect(route('home'));
        $this->get(route('client.orders.show', $foreign))->assertRedirect(route('home'));
        $this->get(route('client.request'))->assertRedirect(route('home'));
    }

    public function test_late_delivery_does_not_grant_the_speed_bonus(): void
    {
        $this->seed();

        $order = Order::query()->where('number', 1040)->firstOrFail();
        $order->update([
            'pipeline_stage' => 4,
            'due_at' => now()->subHour(),
        ]);
        $before = (int) User::query()->where('email', 'sara@hadthiqa.test')->value('points');

        app(OrderFlow::class)->rate($order, [
            'quality' => 5,
            'price_score' => 5,
            'dealing' => 5,
        ], null, 'عميل');

        $order->refresh();

        $this->assertFalse($order->on_time);
        $this->assertSame($before + $order->xp, (int) User::query()->where('email', 'sara@hadthiqa.test')->value('points'));
    }

    private function sara(): User
    {
        return User::query()->where('email', 'sara@hadthiqa.test')->firstOrFail();
    }

    private function place(Service $service, string $speed, ?string $code, bool $tracking): void
    {
        $this->post(route('client.store'), [
            'service_id' => $service->id,
            'description' => 'صالة محتاجة ألوان',
            'speed' => $speed,
            'detail_level' => 'pro',
            'payment_method' => 'card',
            'referral_code' => $code,
            'tracking' => $tracking ? '1' : '0',
        ])->assertRedirect();
    }
}
