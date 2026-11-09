<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Service;
use App\Models\User;
use App\Services\OrderFlow;
use Illuminate\Http\Request;

class ClientOrderController extends Controller
{
    public function __construct(private OrderFlow $flow) {}

    public function create(Request $request)
    {
        if ($redirect = $this->sendStaffHome()) {
            return $redirect;
        }

        return view('client.request', [
            'services' => Service::query()->where('available', true)->orderBy('id')->get(),
            'workers' => User::query()->where('role', 'worker')->where('is_available', true)->orderByDesc('rating')->get(),
            'speeds' => config('hadthiqa.speeds'),
            'tiers' => config('hadthiqa.tiers'),
            'code' => strtoupper((string) $request->query('code', '')),
            'pricing' => [
                'tracking_fee' => (int) config('hadthiqa.tracking_fee'),
                'discounts' => config('hadthiqa.discount_codes'),
            ],
        ]);
    }

    public function store(Request $request)
    {
        if ($redirect = $this->sendStaffHome()) {
            return $redirect;
        }

        $data = $request->validate([
            'service_id' => 'required|exists:services,id',
            'client_name' => 'nullable|string|max:80',
            'description' => 'required|string|max:2000',
            'speed' => 'required|in:normal,rush',
            'detail_level' => 'required|in:beginner,pro,expert',
            'worker_id' => 'nullable|exists:users,id',
            'payment_method' => 'required|in:instapay,card,wallet',
            'tracking' => 'nullable|boolean',
            'referral_code' => 'nullable|string|max:40',
            'voice_note' => 'nullable|boolean',
            'photos' => 'nullable|array|max:3',
            'photos.*' => 'image|max:4096',
        ], [
            'service_id.required' => 'اختاري الخدمة أولاً.',
            'description.required' => 'اكتبي تفاصيل الطلب.',
        ]);

        $photos = [];
        foreach ($request->file('photos', []) as $photo) {
            $photos[] = $photo->store('orders', 'public');
        }

        $order = $this->flow->createClientOrder($data, $photos);
        $ids = session('client_order_ids', []);
        $ids[] = $order->id;
        session([
            'last_order_id' => $order->id,
            'client_order_ids' => array_values(array_unique($ids)),
        ]);

        return redirect()
            ->route('client.orders.show', $order)
            ->with('status', 'تم تسجيل الطلب. ادفع على المنصة عشان يبدأ التنفيذ.');
    }

    public function show(Order $order)
    {
        if ($redirect = $this->guardClientOrder($order)) {
            return $redirect;
        }

        $order->load('service', 'worker', 'steps', 'rating');

        return view('client.show', [
            'order' => $order,
            'stages' => config('hadthiqa.client_stages'),
            'handle' => config('hadthiqa.instapay_handle'),
        ]);
    }

    public function pay(Request $request, Order $order)
    {
        if ($redirect = $this->guardClientOrder($order)) {
            return $redirect;
        }

        $data = $request->validate([
            'payment_method' => 'required|in:instapay,card,wallet',
        ]);
        $this->flow->pay($order, $data['payment_method']);

        return back()->with('status', 'اتحجز المبلغ لدى المنصة، ومش هيتحوّل إلا بعد استلامك للعمل.');
    }

    public function simulate(Order $order)
    {
        if ($redirect = $this->guardClientOrder($order)) {
            return $redirect;
        }

        $message = $this->flow->simulate($order);

        return back()->with('status', $message);
    }

    public function rate(Request $request, Order $order)
    {
        if ($redirect = $this->guardClientOrder($order)) {
            return $redirect;
        }

        $data = $request->validate([
            'quality' => 'required|integer|min:1|max:5',
            'price_score' => 'required|integer|min:1|max:5',
            'dealing' => 'required|integer|min:1|max:5',
            'note' => 'nullable|string|max:1000',
        ], [
            'quality.required' => 'قيّمي المحاور الثلاثة أولاً.',
            'price_score.required' => 'قيّمي المحاور الثلاثة أولاً.',
            'dealing.required' => 'قيّمي المحاور الثلاثة أولاً.',
        ]);

        $this->flow->rate(
            $order,
            [
                'quality' => (int) $data['quality'],
                'price_score' => (int) $data['price_score'],
                'dealing' => (int) $data['dealing'],
            ],
            $data['note'] ?? null,
            $order->client_name,
        );

        return back()->with('status', 'تم إرسال التقييم، واتحرر المبلغ المحجوز.');
    }

    private function sendStaffHome()
    {
        return match (auth()->user()?->role) {
            'seller' => redirect()->route('home'),
            'worker' => redirect()->route('worker.home'),
            'admin' => redirect()->route('admin.home'),
            default => null,
        };
    }

    private function guardClientOrder(Order $order)
    {
        if ($redirect = $this->sendStaffHome()) {
            return $redirect;
        }

        $ids = array_map('intval', session('client_order_ids', []));
        if ((int) session('last_order_id') === $order->id) {
            $ids[] = $order->id;
        }

        abort_unless(in_array($order->id, $ids, true), 403);

        return null;
    }
}
