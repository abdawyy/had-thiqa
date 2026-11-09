<?php

namespace Tests\Feature;

use App\Models\Offer;
use App\Models\Order;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteWalkTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_pages_and_each_panel_load(): void
    {
        $this->seed();

        $this->get('/')->assertOk()->assertSee('اختر مسارك', false)->assertDontSee('بائعة', false);
        $this->get(route('login', ['as' => 'worker']))->assertOk();
        $this->get(route('login', ['as' => 'seller']))->assertRedirect(route('home'));
        $this->get(route('login', ['as' => 'admin']))->assertOk();
        $this->get(route('register', ['as' => 'worker']))->assertOk();
        $this->get(route('register', ['as' => 'seller']))->assertNotFound();
        $this->get(route('register', ['as' => 'admin']))->assertNotFound();
        $this->get(route('client.request'))->assertOk()->assertSee('طلب جديد', false);
        $this->get('/worker')->assertRedirect(route('login', ['as' => 'worker']));

        $this->actingAs($this->user('sara@hadthiqa.test'));
        foreach (['worker.home', 'worker.skills', 'worker.projects', 'worker.account', 'worker.analytics'] as $name) {
            $this->get(route($name))->assertOk();
        }
        $order = Order::query()->where('number', 1038)->firstOrFail();
        $this->get(route('worker.orders.show', $order))->assertOk()->assertSee('نصيبك', false);
        $this->get(route('worker.orders.split', $order))->assertOk();
        $this->get(route('seller.home'))->assertRedirect(route('home'));
        $this->post(route('client.store'), [
            'service_id' => Service::query()->where('slug', 'paint')->value('id'),
            'description' => 'تجربة',
            'speed' => 'normal',
            'detail_level' => 'pro',
            'payment_method' => 'instapay',
        ])->assertRedirect(route('worker.home'));

        $this->actingAs($this->user('seller@hadthiqa.test'));
        $this->get(route('seller.home'))->assertRedirect(route('home'));
        $this->get(route('admin.home'))->assertRedirect(route('home'));

        $this->actingAs($this->user('admin@hadthiqa.test'));
        $this->get(route('admin.home'))->assertOk()->assertSee('لوحة الإدارة', false)->assertDontSee('بائع', false);
        $this->get(route('admin.analytics'))->assertOk()->assertSee('الخدمات', false)->assertSee('المنفذات', false)->assertDontSee('بائع', false);
    }

    public function test_a_client_can_pay_move_through_the_demo_and_release_the_split(): void
    {
        $this->seed();
        $paint = Service::query()->where('slug', 'paint')->firstOrFail();

        $this->post(route('client.store'), [
            'service_id' => $paint->id,
            'client_name' => 'منى',
            'description' => 'صالة محتاجة ألوان هادية',
            'speed' => 'normal',
            'detail_level' => 'pro',
            'payment_method' => 'instapay',
            'referral_code' => 'THIQA10',
        ])->assertRedirect();

        $order = Order::query()->latest('id')->firstOrFail();
        $this->get(route('client.orders.show', $order))->assertOk()->assertSee('إنستاباي', false);
        $this->post(route('client.orders.pay', $order), ['payment_method' => 'instapay'])->assertRedirect();

        foreach (range(1, 6) as $ignored) {
            if ($order->fresh()->pipeline_stage >= 4 && $order->fresh()->payment_status === 'held') {
                break;
            }
            $this->post(route('client.orders.simulate', $order))->assertRedirect();
        }

        $order->refresh();
        $this->assertSame(4, $order->pipeline_stage);

        $this->post(route('client.orders.rate', $order), [
            'quality' => 5,
            'price_score' => 4,
            'dealing' => 5,
            'note' => 'شغل كويس',
        ])->assertRedirect();

        $order->refresh();
        $this->assertSame('released', $order->payment_status);
        $this->assertSame(5, $order->pipeline_stage);
        $this->get(route('client.orders.show', $order))
            ->assertOk()
            ->assertDontSee('بائعة', false);

        $this->actingAs($this->user('sara@hadthiqa.test'));
        $this->get(route('worker.orders.show', $order))->assertOk();
        $this->get(route('client.orders.show', $order))->assertRedirect(route('worker.home'));
    }

    public function test_worker_can_claim_and_finish_the_visible_steps(): void
    {
        $this->seed();
        $this->actingAs($this->user('sara@hadthiqa.test'));
        $offer = Offer::query()->where('active', true)->where('min_level', '<=', 3)->firstOrFail();

        $this->post(route('worker.claim', $offer))->assertRedirect();
        $order = Order::query()->where('title', $offer->title)->latest('id')->firstOrFail();
        $this->get(route('worker.orders.show', $order))->assertOk();

        while ($order->fresh()->pipeline_stage === 1) {
            $step = $order->fresh()->steps()->whereNull('completed_at')->orderBy('position')->first();
            $this->assertNotNull($step);
            if ($step->kind === 'ai') {
                $this->post(route('worker.orders.skill', [$order, $step]))->assertRedirect();
                $this->post(route('worker.orders.skill.confirm', [$order, $step]))->assertRedirect();
            } else {
                $this->post(route('worker.orders.step', [$order, $step]), ['note' => 'تم'])->assertRedirect();
            }
        }

        $this->assertSame(2, $order->fresh()->pipeline_stage);
        $this->post(route('worker.orders.deliver', $order), ['checks' => [0 => '1']])->assertRedirect();
        $this->post(route('worker.orders.approve', $order->fresh()))->assertRedirect();
        $this->assertSame(4, $order->fresh()->pipeline_stage);
    }

    public function test_a_new_worker_can_register(): void
    {
        $this->seed();

        $this->post(route('register.store'), [
            'name' => 'هدير',
            'email' => 'hadeer@hadthiqa.test',
            'password' => 'secret1',
            'password_confirmation' => 'secret1',
            'as' => 'worker',
        ])->assertRedirect(route('worker.home'));
        $this->get(route('worker.home'))->assertOk()->assertSee('هدير', false);
    }

    private function user(string $email): User
    {
        return User::query()->where('email', $email)->firstOrFail();
    }
}
