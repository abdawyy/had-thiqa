<?php

namespace App\Support;

use App\Models\User;

class Levels
{
    public static function fromPoints(int $points): array
    {
        $current = config('hadthiqa.levels')[0];

        foreach (config('hadthiqa.levels') as $level) {
            if ($points >= $level['min']) {
                $current = $level;
            }
        }

        return $current;
    }

    public static function sync(User $user, int $points): void
    {
        $level = self::fromPoints($points);

        $user->forceFill([
            'points' => $points,
            'level' => $level['level'],
            'level_name' => $level['name'],
            'tier' => $level['tier'],
        ])->save();
    }

    /**
     * @return array{left: int, percent: int, name: string, next: int}
     */
    public static function progress(User $user): array
    {
        $current = self::fromPoints($user->points);
        $next = null;

        foreach (config('hadthiqa.levels') as $level) {
            if ($level['min'] > $user->points) {
                $next = $level['min'];
                break;
            }
        }

        if ($next === null) {
            return [
                'left' => 0,
                'percent' => 100,
                'name' => $current['name'],
                'next' => $user->points,
            ];
        }

        $span = max(1, $next - $current['min']);

        return [
            'left' => $next - $user->points,
            'percent' => (int) min(100, round((($user->points - $current['min']) / $span) * 100)),
            'name' => $current['name'],
            'next' => $next,
        ];
    }
}
