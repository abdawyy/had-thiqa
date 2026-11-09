<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use App\Models\Order;
use App\Support\Demo;
use App\Support\Wallet;
use Illuminate\Http\Request;

class WorkerController extends Controller
{
    public function home()
    {
        $worker = Demo::worker();

        return view('worker.dashboard', [
            'worker' => $worker,
            'progress' => $worker->progress(),
            'offers' => Offer::query()->with('service')->where('active', true)->orderBy('id')->get(),
            'money' => [
                'available' => Wallet::available($worker),
                'pending' => Wallet::pending($worker),
            ],
        ]);
    }

    public function availability(Request $request)
    {
        $worker = Demo::worker();
        $worker->update(['is_available' => $request->boolean('available')]);

        return back()->with('status', $worker->is_available
            ? 'تم تفعيل الإتاحة — الطلبات ظاهرة أدناه'
            : 'تم إيقاف الإتاحة لليوم');
    }

    public function help(Request $request)
    {
        $data = $request->validate([
            'topic' => 'required|string|max:120',
            'body' => 'required|string|max:2000',
        ], [
            'body.required' => 'اكتبي تفاصيل المشكلة أو السؤال.',
        ]);

        Demo::worker()->tickets()->create($data);

        return back()->with('status', 'وصل طلب المساعدة لفريق الدعم.');
    }

    public function skills()
    {
        $worker = Demo::worker();

        return view('worker.skills', [
            'worker' => $worker,
            'skills' => $worker->skills()->latest()->get(),
        ]);
    }

    public function storeSkill(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:80',
        ], [
            'name.required' => 'اكتبي اسم المهارة أولاً.',
        ]);

        Demo::worker()->skills()->create([
            'name' => $data['name'],
            'level_label' => 'قيد الاعتماد',
            'progress' => 0,
            'status' => 'pending',
        ]);

        return back()->with('status', 'تمت إضافة المهارة — بانتظار الاعتماد');
    }

    public function projects()
    {
        $worker = Demo::worker();

        return view('worker.projects', [
            'worker' => $worker,
            'orders' => Order::query()->with('service')->where('worker_id', $worker->id)->latest('number')->get(),
        ]);
    }

    public function account()
    {
        $worker = Demo::worker();
        $last = Order::query()
            ->with('rating')
            ->where('worker_id', $worker->id)
            ->where('pipeline_stage', 5)
            ->orderByDesc('number')
            ->first();

        return view('worker.account', [
            'worker' => $worker,
            'last' => $last,
            'available' => Wallet::available($worker),
            'pending' => Wallet::pending($worker),
        ]);
    }

    public function analytics()
    {
        $worker = Demo::worker();
        $orders = Order::query()->with('rating')->where('worker_id', $worker->id)->get();
        $done = $orders->where('pipeline_stage', 5);
        $rated = $done->filter(fn (Order $order) => $order->rating !== null);

        return view('worker.analytics', [
            'worker' => $worker,
            'orders' => $orders->count(),
            'done' => $done->count(),
            'open' => $orders->where('pipeline_stage', '<', 5)->count(),
            'earned' => $done->sum(fn (Order $order) => $order->workerShare()),
            'onTime' => $done->where('on_time', true)->count(),
            'rating' => $rated->isEmpty() ? 0 : round($rated->avg(fn (Order $order) => $order->rating->average()), 1),
            'available' => Wallet::available($worker),
        ]);
    }
}
