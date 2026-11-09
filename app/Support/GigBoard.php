<?php

namespace App\Support;

use App\Models\Service;

class GigBoard
{
    public static function sync(): void
    {
        Service::query()->orderBy('id')->get()->each(fn (Service $service) => self::open($service));
    }

    public static function open(Service $service, bool $reopen = false): void
    {
        if (! $service->available) {
            $service->offers()->where('active', true)->update(['active' => false]);

            return;
        }

        if ($service->offers()->where('active', true)->exists()) {
            return;
        }

        if (! $reopen && $service->offers()->exists()) {
            return;
        }

        $service->offers()->create([
            'title' => $service->name,
            'client_name' => 'طلب على المنصة',
            'meta' => self::levelName((int) $service->min_level),
            'duration' => $service->duration,
            'due_label' => 'اليوم · 6 م',
            'xp' => $service->xp ?: 100,
            'min_level' => $service->min_level,
            'active' => true,
        ]);
    }

    private static function levelName(int $level): string
    {
        return match (true) {
            $level <= 1 => 'مستوى ابتداء',
            $level <= 3 => 'مستوى احتراف',
            default => 'مستوى خبرة',
        };
    }
}
