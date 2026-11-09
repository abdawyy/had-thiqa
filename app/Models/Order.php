<?php

namespace App\Models;

use App\Support\Split;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'photos' => 'array',
            'quality_checks' => 'array',
            'on_time' => 'boolean',
            'voice_note' => 'boolean',
            'due_at' => 'datetime',
            'partner_approved_at' => 'datetime',
        ];
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function worker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'worker_id');
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    public function steps(): HasMany
    {
        return $this->hasMany(OrderStep::class)->orderBy('position');
    }

    public function rating(): HasOne
    {
        return $this->hasOne(Rating::class);
    }

    public function distributable(): int
    {
        return max(0, (int) $this->total - (int) $this->tracking_fee);
    }

    public function shares(): array
    {
        return Split::of($this->distributable());
    }

    public function workerShare(): int
    {
        return Split::amount($this->distributable(), 'executor');
    }

    public function statusLabel(): string
    {
        return match (true) {
            $this->payment_status === 'unpaid' => 'بانتظار الدفع',
            $this->pipeline_stage <= 0 => 'لم تبدأ بعد',
            $this->pipeline_stage === 1 => 'جارٍ التنفيذ',
            $this->pipeline_stage === 2 => 'تسليم العمل',
            $this->pipeline_stage === 3 => $this->deliveredCleanly() ? 'بانتظار الاعتماد' : 'تسليم وفيه بنود مراجعة',
            $this->pipeline_stage === 4 => 'بانتظار التقييم',
            default => 'مكتمل',
        };
    }

    public function clientPhase(): string
    {
        if ($this->payment_status === 'unpaid') {
            return 'payment';
        }

        return match (true) {
            $this->pipeline_stage <= 0 => 'waiting',
            $this->pipeline_stage <= 2 => 'working',
            $this->pipeline_stage === 3 => 'review',
            $this->pipeline_stage === 4 => 'deliver',
            default => 'done',
        };
    }

    public function speedMeta(): array
    {
        return config('hadthiqa.speeds.'.$this->speed) ?? config('hadthiqa.speeds.normal');
    }

    public function tierMeta(): array
    {
        return config('hadthiqa.tiers.'.$this->detail_level) ?? config('hadthiqa.tiers.pro');
    }

    public function stepsDone(): int
    {
        return $this->steps->whereNotNull('completed_at')->count();
    }

    public function currentStep(): ?OrderStep
    {
        return $this->steps->first(fn (OrderStep $step) => $step->completed_at === null);
    }

    public function deliveredCleanly(): bool
    {
        $checks = $this->quality_checks ?? [];

        if ($checks === []) {
            return true;
        }

        return collect($checks)->every(fn (array $check) => ! empty($check['ok']));
    }
}
