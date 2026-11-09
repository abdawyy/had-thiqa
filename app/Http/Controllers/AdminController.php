<?php

namespace App\Http\Controllers;

use App\Models\LedgerEntry;
use App\Models\Order;
use App\Models\Rating;
use App\Models\Service;
use App\Models\Skill;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    public function index()
    {
        $orders = Order::query()->with('service', 'worker', 'rating')->orderByDesc('number')->get();
        $released = $orders->where('payment_status', 'released');
        $collected = $orders->whereIn('payment_status', ['held', 'released'])->sum('total');
        $platform = $released->sum(fn (Order $order) => collect($order->shares())->firstWhere('id', 'platform')['amount'] ?? 0);
        $ratings = Rating::query()->with('order')->latest()->get();
        $average = $ratings->isEmpty()
            ? 0
            : round($ratings->avg(fn (Rating $rating) => $rating->average()), 1);

        return view('admin.index', [
            'orders' => $orders,
            'services' => Service::query()->with('seller')->orderBy('id')->get(),
            'ratings' => $ratings,
            'collected' => $collected,
            'platform' => $platform,
            'average' => $average,
            'active' => $orders->where('payment_status', '!=', 'unpaid')->where('pipeline_stage', '<', 5)->count(),
            'trackingIncome' => $orders->whereIn('payment_status', ['held', 'released'])->sum('tracking_fee'),
            'skills' => Skill::query()->with('user')->latest()->get(),
            'tickets' => Ticket::query()->with('user')->latest()->get(),
            'workers' => \App\Models\User::query()->where('role', 'worker')->orderBy('name')->get(),
            'sellers' => \App\Models\User::query()->where('role', 'seller')->withCount('gigs')->orderBy('name')->get(),
            'isAdmin' => true,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:80',
            'sample' => 'required|string|max:400',
            'price' => 'required|integer|min:50|max:100000',
            'xp' => 'required|integer|min:10|max:2000',
            'duration' => 'required|string|max:40',
            'min_level' => 'required|integer|min:1|max:5',
        ]);

        Service::create($data + [
            'slug' => 'svc-'.Str::lower(Str::random(6)),
            'available' => true,
        ]);

        return back()->with('status', 'اتضافت الخدمة وظهرت للعملاء.');
    }

    public function update(Request $request, Service $service)
    {
        $service->update(['available' => $request->boolean('available')]);

        return back()->with('status', $service->available ? 'الخدمة ظاهرة للعملاء.' : 'الخدمة اختفت من العملاء.');
    }

    public function destroy(Service $service)
    {
        if ($service->orders()->exists()) {
            return back()->with('error', 'الخدمة مرتبطة بطلبات. أخفيها بدل الحذف.');
        }

        $service->offers()->delete();
        $service->delete();

        return back()->with('status', 'اتحذفت الخدمة.');
    }

    public function reviewSkill(Request $request, Skill $skill)
    {
        if ($redirect = $this->ensureAdmin()) {
            return $redirect;
        }

        $data = $request->validate([
            'decision' => 'required|in:approved,rejected',
        ]);

        $skill->update([
            'status' => $data['decision'],
            'reviewed_at' => now(),
            'level_label' => $data['decision'] === 'approved' ? 'معتمدة' : 'مرفوضة',
        ]);

        return back()->with('status', $data['decision'] === 'approved' ? 'اتعتمدت المهارة.' : 'اترفضت المهارة.');
    }

    public function closeTicket(Ticket $ticket)
    {
        if ($redirect = $this->ensureAdmin()) {
            return $redirect;
        }

        $ticket->update(['status' => 'closed']);

        return back()->with('status', 'اتقفل طلب المساعدة.');
    }

    public function analytics()
    {
        $orders = Order::query()->with('service.seller', 'worker')->get();
        $paid = $orders->whereIn('payment_status', ['held', 'released']);
        $done = $orders->where('pipeline_stage', 5);
        $base = $paid->sum(fn (Order $order) => $order->distributable());

        return view('admin.analytics', [
            'orders' => $orders->count(),
            'open' => $orders->where('pipeline_stage', '<', 5)->where('payment_status', 'held')->count(),
            'done' => $done->count(),
            'collected' => $paid->sum('total'),
            'platform' => (int) round($base * \App\Support\Split::percent('platform') / 100),
            'workers' => User::query()->where('role', 'worker')->count(),
            'sellers' => User::query()->where('role', 'seller')->count(),
            'payouts' => LedgerEntry::query()->where('type', 'payout')->sum('amount'),
            'top' => $orders->groupBy('service_id')->map(fn ($group) => [
                'name' => $group->first()->service?->name ?? $group->first()->title,
                'count' => $group->count(),
                'total' => $group->sum('total'),
            ])->sortByDesc('total')->take(5)->values(),
        ]);
    }

    private function ensureAdmin(): ?RedirectResponse
    {
        return null;
    }
}
