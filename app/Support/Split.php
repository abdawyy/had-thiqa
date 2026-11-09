<?php

namespace App\Support;

class Split
{
    public static function percent(string $id): int
    {
        $row = collect(config('hadthiqa.splits'))->firstWhere('id', $id);

        return (int) ($row['pct'] ?? 0);
    }

    public static function amount(int $base, string $id): int
    {
        return (int) round($base * self::percent($id) / 100);
    }

    /**
     * @return array<int, array{id: string, label: string, pct: int, tone: string, amount: int}>
     */
    public static function of(int $base): array
    {
        return collect(config('hadthiqa.splits'))
            ->map(fn (array $row) => $row + ['amount' => self::amount($base, $row['id'])])
            ->all();
    }
}
