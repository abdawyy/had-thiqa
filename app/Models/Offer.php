<?php

namespace App\Models;

use App\Support\Split;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Offer extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function workerShare(): int
    {
        return Split::amount((int) $this->service->price, 'executor');
    }

    public function lockedFor(User $worker): bool
    {
        return $worker->level < $this->min_level;
    }
}
