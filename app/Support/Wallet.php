<?php

namespace App\Support;

use App\Models\LedgerEntry;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Collection;

class Wallet
{
    public static function sync(): void
    {
        Order::query()
            ->with('service')
            ->where('payment_status', 'released')
            ->each(fn (Order $order) => static::release($order));
    }

    public static function pending(User $user): int
    {
        $orders = Order::query()->with('service')->where('payment_status', 'held');

        if ($user->role === 'seller') {
            $orders->whereHas('service', fn ($query) => $query->where('seller_id', $user->id));

            return (int) $orders->get()->sum(fn (Order $order) => Split::amount($order->distributable(), 'author'));
        }

        if ($user->role !== 'worker') {
            return 0;
        }

        return (int) $orders->where('worker_id', $user->id)->get()->sum(fn (Order $order) => $order->workerShare());
    }

    public static function collect(Order $order): void
    {
        if ($order->payment_status !== 'held') {
            return;
        }

        LedgerEntry::query()->firstOrCreate(
            ['order_id' => $order->id, 'type' => 'collect'],
            [
                'party' => 'platform',
                'amount' => $order->total,
                'status' => 'held',
            ]
        );
    }

    public static function release(Order $order): void
    {
        if (LedgerEntry::query()->where('order_id', $order->id)->where('type', 'share')->exists()) {
            return;
        }

        $order->loadMissing('service');

        foreach ($order->shares() as $share) {
            LedgerEntry::query()->create([
                'user_id' => match ($share['id']) {
                    'executor' => $order->worker_id,
                    'author' => $order->service?->seller_id,
                    default => null,
                },
                'order_id' => $order->id,
                'type' => 'share',
                'party' => $share['id'],
                'amount' => $share['amount'],
                'status' => 'available',
            ]);
        }
    }

    public static function earned(User $user): int
    {
        static::sync();

        return (int) static::sharesFor($user)->sum('amount');
    }

    public static function paidOut(User $user): int
    {
        return (int) LedgerEntry::query()
            ->where('user_id', $user->id)
            ->where('type', 'payout')
            ->sum('amount');
    }

    public static function available(User $user): int
    {
        return max(0, static::earned($user) - static::paidOut($user));
    }

    public static function payout(User $user, int $amount, string $handle): void
    {
        LedgerEntry::query()->create([
            'user_id' => $user->id,
            'type' => 'payout',
            'party' => $user->role === 'seller' ? 'author' : 'executor',
            'amount' => $amount,
            'status' => 'sent',
            'instapay_handle' => $handle,
        ]);
    }

    public static function sharesFor(User $user): Collection
    {
        $party = $user->role === 'seller' ? 'author' : 'executor';

        return LedgerEntry::query()
            ->where('user_id', $user->id)
            ->where('type', 'share')
            ->where('party', $party)
            ->where('status', 'available')
            ->get();
    }
}
