<?php

namespace App\Services;

use App\Models\Offer;
use App\Models\Order;
use App\Models\OrderStep;
use App\Models\Rating;
use App\Models\Service;
use App\Models\User;
use App\Support\Levels;
use App\Support\SopLibrary;
use App\Support\Wallet;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderFlow
{
    public function claim(Offer $offer, User $worker): Order
    {
        if (! $worker->is_available) {
            throw ValidationException::withMessages([
                'offer' => 'فعّلي الإتاحة أولاً حتى تستلمي مهمة.',
            ]);
        }

        if ($offer->lockedFor($worker)) {
            throw ValidationException::withMessages([
                'offer' => 'هذه المهمة تُفتح عند المستوى الرابع.',
            ]);
        }

        if (! $offer->active) {
            throw ValidationException::withMessages([
                'offer' => 'هذه المهمة لم تعد متاحة.',
            ]);
        }

        return DB::transaction(function () use ($offer, $worker) {
            $offer->refresh();
            $service = $offer->service;
            $base = (int) $service->price;

            $order = Order::create([
                'number' => $this->nextNumber(),
                'service_id' => $service->id,
                'worker_id' => $worker->id,
                'client_name' => $offer->client_name,
                'title' => $offer->title,
                'description' => $service->sample,
                'speed' => 'normal',
                'detail_level' => $worker->tier,
                'base_price' => $base,
                'discount_percent' => 0,
                'tracking_fee' => 0,
                'total' => $base,
                'duration' => $offer->duration,
                'due_label' => $offer->due_label,
                'due_at' => $this->dueAt('normal', $offer->duration),
                'xp' => $offer->xp,
                'pipeline_stage' => 1,
                'payment_method' => 'instapay',
                'payment_status' => 'held',
                'source' => 'offer',
                'partner_name' => config('hadthiqa.partner_name'),
                'on_time' => true,
            ]);

            $offer->update(['active' => false]);
            SopLibrary::attach($order);

            return $order;
        });
    }

    public function begin(Order $order): void
    {
        if ($order->pipeline_stage < 1 && $order->payment_status === 'held') {
            $order->update(['pipeline_stage' => 1]);
        }
    }

    public function completeStep(OrderStep $step, ?string $note = null): void
    {
        if ($step->done()) {
            return;
        }

        $step->update([
            'completed_at' => now(),
            'note' => $note,
        ]);

        $this->advanceIfStepsDone($step->order);
    }

    public function runSkill(OrderStep $step): void
    {
        if ($step->kind !== 'ai' || $step->done() || ! empty($step->result)) {
            return;
        }

        $step->update([
            'result' => $step->preview ?? [],
        ]);
    }

    public function confirmSkill(OrderStep $step): void
    {
        if ($step->kind !== 'ai' || $step->done() || empty($step->result)) {
            return;
        }

        $step->update(['completed_at' => now()]);
        $this->advanceIfStepsDone($step->order);
    }

    public function deliver(Order $order, array $checks): void
    {
        $order->update([
            'pipeline_stage' => 3,
            'quality_checks' => $checks,
        ]);
    }

    public function approve(Order $order): void
    {
        if ($order->pipeline_stage === 3) {
            $order->update([
                'pipeline_stage' => 4,
                'partner_approved_at' => now(),
                'partner_note' => 'اعتماد تجريبي من لوحة المنفذة، وليس من حساب بارتنر منفصل.',
                'on_time' => $this->stillOnTime($order),
            ]);
        }
    }

    public function pay(Order $order, string $method): void
    {
        if ($order->payment_status === 'unpaid') {
            $order->update([
                'payment_status' => 'held',
                'payment_method' => $method,
            ]);
            Wallet::collect($order->fresh());
        }
    }

    /**
     * @param  array{quality: int, price_score: int, dealing: int}  $scores
     */
    public function rate(Order $order, array $scores, ?string $note, string $author): void
    {
        if ($order->pipeline_stage < 4 || $order->rating) {
            throw ValidationException::withMessages([
                'rating' => 'التقييم يتفتح بعد اعتماد البارتنر وتسليم العمل.',
            ]);
        }

        DB::transaction(function () use ($order, $scores, $note, $author) {
            Rating::create([
                'order_id' => $order->id,
                'quality' => $scores['quality'],
                'price_score' => $scores['price_score'],
                'dealing' => $scores['dealing'],
                'note' => $note,
                'author_name' => $author,
            ]);

            $onTime = $this->stillOnTime($order);
            $order->update([
                'pipeline_stage' => 5,
                'payment_status' => 'released',
                'on_time' => $onTime,
            ]);
            Wallet::release($order->fresh());

            $worker = $order->worker;
            if (! $worker) {
                return;
            }

            $gain = (int) $order->xp + ($onTime ? 30 : 0);
            $clean = $order->deliveredCleanly();

            // Sprint 3 replaces balance, withdrawable, and pending with a ledger.
            // Pending stays the seeded figure. Do not write it here.
            $worker->forceFill([
                'balance' => $worker->balance + $order->workerShare(),
                'withdrawable' => $worker->withdrawable + $order->workerShare(),
                'jobs_done' => $worker->jobs_done + 1,
                'jobs_clean' => $worker->jobs_clean + ($clean ? 1 : 0),
                'streak' => $clean ? $worker->streak + 1 : 0,
                'speed_badge' => $worker->speed_badge || $onTime,
            ])->save();

            Levels::sync($worker, $worker->points + $gain);
        });
    }

    public function createClientOrder(array $input, array $photos = []): Order
    {
        return DB::transaction(function () use ($input, $photos) {
            $service = Service::query()->where('available', true)->findOrFail($input['service_id']);
            $quote = $this->quote($service, $input['speed'], $input['referral_code'] ?? null, ! empty($input['tracking']));
            $referrer = $quote['code'] !== ''
                ? User::query()->where('referral_code', $quote['code'])->first()
                : null;
            $worker = $this->matchWorker($service, $input['detail_level'], $input['worker_id'] ?? null);

            $order = Order::create([
                'number' => $this->nextNumber(),
                'service_id' => $service->id,
                'worker_id' => $worker?->id,
                'client_name' => ($input['client_name'] ?? '') !== '' ? $input['client_name'] : 'عميل جديد',
                'title' => $service->name,
                'description' => $input['description'],
                'speed' => $input['speed'],
                'detail_level' => $input['detail_level'],
                'base_price' => $quote['base'],
                'discount_percent' => $quote['discount_percent'],
                'tracking_fee' => $quote['tracking_fee'],
                'total' => $quote['total'],
                'duration' => $service->duration,
                'due_label' => $input['speed'] === 'rush' ? 'اليوم' : 'غداً',
                'due_at' => $this->dueAt($input['speed'], $service->duration),
                'xp' => $service->xp,
                'pipeline_stage' => 0,
                'payment_method' => $input['payment_method'],
                'payment_status' => 'unpaid',
                'source' => 'client',
                'partner_name' => config('hadthiqa.partner_name'),
                'voice_note' => (bool) ($input['voice_note'] ?? false),
                'photos' => $photos,
                'referral_code' => $quote['code'] !== '' ? $quote['code'] : null,
                'referrer_id' => $referrer?->id,
                'on_time' => true,
            ]);

            SopLibrary::attach($order);

            return $order;
        });
    }

    public function simulate(Order $order): string
    {
        if ($order->payment_status === 'unpaid') {
            $this->pay($order, $order->payment_method ?: 'instapay');

            return 'اتحجز المبلغ لدى المنصة. لسه محدش بدأ التنفيذ.';
        }

        if ($order->pipeline_stage <= 0) {
            $this->begin($order->fresh());

            return 'المنفذة بدأت خطوات العمل.';
        }

        if ($order->pipeline_stage === 1) {
            $order->load('steps');
            foreach ($order->steps as $step) {
                if ($step->done()) {
                    continue;
                }
                if ($step->kind === 'ai') {
                    $this->runSkill($step);
                    $this->confirmSkill($step->fresh());
                } else {
                    $this->completeStep($step, 'أُغلقت في المعاينة التجريبية.');
                }
            }

            return 'خطوات الـ SOP اتقفلت، والعمل جاهز للتسليم.';
        }

        if ($order->pipeline_stage === 2) {
            $order->loadMissing('service');
            $checks = collect(SopLibrary::checksFor($order->service->slug))
                ->map(fn (string $label) => ['label' => $label, 'ok' => true])
                ->all();
            $this->deliver($order, $checks);

            return 'قائمة الجودة اتقفلت، وبانتظار اعتماد البارتنر.';
        }

        if ($order->pipeline_stage === 3) {
            $this->approve($order);

            return 'البارتنر اعتمد التسليم. قيّمي العمل دلوقتي.';
        }

        return 'العمل جاهز للتقييم. قيّمي الجودة والسعر والتعامل.';
    }

    private function advanceIfStepsDone(Order $order): void
    {
        $order->load('steps');

        if ($order->pipeline_stage === 1 && $order->steps->every(fn (OrderStep $step) => $step->done())) {
            $order->update(['pipeline_stage' => 2]);
        }
    }

    /**
     * @return array{base: int, discount_percent: int, tracking_fee: int, total: int, code: string}
     */
    public function quote(Service $service, string $speedKey, ?string $code, bool $withTracking): array
    {
        $speed = config('hadthiqa.speeds.'.$speedKey) ?? config('hadthiqa.speeds.normal');
        $base = (int) round($service->price * $speed['mult']);
        $normalized = strtoupper(trim((string) $code));
        $discountPct = (int) (config('hadthiqa.discount_codes')[$normalized] ?? 0);
        $discount = (int) round($base * $discountPct / 100);
        $fee = $withTracking ? (int) config('hadthiqa.tracking_fee') : 0;

        return [
            'base' => $base,
            'discount_percent' => $discountPct,
            'tracking_fee' => $fee,
            'total' => $base - $discount + $fee,
            'code' => $normalized,
        ];
    }

    private function matchWorker(Service $service, string $tier, mixed $chosenId): ?User
    {
        if ($chosenId) {
            $chosen = User::query()
                ->where('role', 'worker')
                ->where('is_available', true)
                ->find($chosenId);

            if ($chosen) {
                return $chosen;
            }
        }

        return User::query()
            ->where('role', 'worker')
            ->where('is_available', true)
            ->where('tier', $tier)
            ->with('skills')
            ->get()
            ->sortBy([
                fn (User $user) => $this->skillMatches($user, $service) ? 0 : 1,
                fn (User $user) => -1 * (float) ($user->rating ?? 0),
            ])
            ->first();
    }

    private function skillMatches(User $user, Service $service): bool
    {
        return $user->skills
            ->where('status', 'approved')
            ->contains(function ($skill) use ($service) {
                return str_contains($skill->name, $service->name)
                    || str_contains($service->name, $skill->name);
            });
    }

    private function dueAt(string $speed, string $duration): Carbon
    {
        if ($speed === 'rush' && preg_match('/(\d+)/', $duration, $matches) === 1) {
            return now()->addMinutes((int) $matches[1]);
        }

        return now()->addDay();
    }

    private function stillOnTime(Order $order): bool
    {
        if ($order->due_at === null) {
            return true;
        }

        return now()->lte($order->due_at);
    }

    private function nextNumber(): int
    {
        return ((int) Order::query()->max('number')) + 1;
    }
}
