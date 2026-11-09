<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Service;
use App\Support\Split;
use App\Support\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SellerController extends Controller
{
    public function home(Request $request)
    {
        $seller = auth()->user();
        $search = trim((string) $request->query('q', ''));
        $gigs = Service::query()
            ->where('seller_id', $seller->id)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('sample', 'like', "%{$search}%");
                });
            })
            ->withCount('orders')
            ->latest()
            ->get();
        $orders = $this->ordersFor($seller->id);
        $filter = in_array($request->query('status'), ['open', 'done', 'unpaid'], true)
            ? $request->query('status')
            : 'all';

        $visible = match ($filter) {
            'unpaid' => $orders->where('payment_status', 'unpaid'),
            'done' => $orders->where('pipeline_stage', 5),
            'open' => $orders->where('pipeline_stage', '<', 5)->where('payment_status', '!=', 'unpaid'),
            default => $orders,
        };

        $paid = $orders->whereIn('payment_status', ['held', 'released']);

        return view('seller.home', [
            'seller' => $seller,
            'gigs' => $gigs,
            'orders' => $visible,
            'filter' => $filter,
            'search' => $search,
            'stats' => [
                'gigs' => $gigs->count(),
                'live' => $gigs->where('available', true)->count(),
                'open' => $orders->where('pipeline_stage', '<', 5)->where('payment_status', '!=', 'unpaid')->count(),
                'done' => $orders->where('pipeline_stage', 5)->count(),
                'sales' => $paid->sum('total'),
                'share' => $paid->sum(fn (Order $order) => Split::amount($order->distributable(), 'author')),
            ],
        ]);
    }

    public function show(Service $service)
    {
        $this->owns($service);
        $service->loadCount('orders');
        $orders = Order::query()->with('worker', 'rating')->where('service_id', $service->id)->latest('number')->get();

        return view('seller.gig', compact('service', 'orders'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        Service::create($data + [
            'seller_id' => auth()->id(),
            'slug' => 'gig-'.Str::lower(Str::random(6)),
            'xp' => 100,
            'available' => true,
        ]);

        return back()->with('status', 'اتنشرت الخدمة وبق تظهر للعملاء.');
    }

    public function update(Request $request, Service $service)
    {
        $this->owns($service);

        if ($request->has('available') && ! $request->has('name')) {
            $service->update(['available' => $request->boolean('available')]);

            return back()->with('status', $service->available ? 'الخدمة ظاهرة.' : 'الخدمة اتوقفت.');
        }

        $service->update($this->validated($request));

        return redirect()->route('seller.gigs.show', $service)->with('status', 'اتحفظت تعديلات الخدمة.');
    }

    public function destroy(Service $service)
    {
        $this->owns($service);

        if ($service->orders()->exists()) {
            return back()->with('error', 'الخدمة عليها طلبات. وقفيها بدل الحذف.');
        }

        $service->delete();

        return redirect()->route('seller.home')->with('status', 'اتحذفت الخدمة.');
    }

    public function duplicate(Service $service)
    {
        $this->owns($service);

        $copy = $service->replicate();
        $copy->slug = 'gig-'.Str::lower(Str::random(6));
        $copy->name = $service->name.' · نسخة';
        $copy->available = false;
        $copy->save();

        return redirect()->route('seller.gigs.show', $copy)->with('status', 'اتعملت نسخة متوقفة. عدّليها قبل النشر.');
    }

    public function order(Order $order)
    {
        $order->load('service', 'worker', 'rating', 'steps');
        abort_unless($order->service?->seller_id === auth()->id(), 403);

        return view('seller.order', compact('order'));
    }

    public function analytics()
    {
        $seller = auth()->user();
        $orders = $this->ordersFor($seller->id);
        $done = $orders->where('pipeline_stage', 5);
        $gigs = Service::query()->where('seller_id', $seller->id)->withCount('orders')->get();

        return view('seller.analytics', [
            'seller' => $seller,
            'gigs' => $gigs,
            'orders' => $orders->count(),
            'done' => $done->count(),
            'open' => $orders->where('pipeline_stage', '<', 5)->count(),
            'sales' => $orders->whereIn('payment_status', ['held', 'released'])->sum('total'),
            'share' => $done->sum(fn (Order $order) => Split::amount($order->distributable(), 'author')),
            'available' => Wallet::available($seller),
        ]);
    }

    private function ordersFor(int $sellerId)
    {
        return Order::query()
            ->with('service', 'worker', 'rating')
            ->whereHas('service', fn ($query) => $query->where('seller_id', $sellerId))
            ->latest('number')
            ->get();
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:80',
            'sample' => 'required|string|max:400',
            'price' => 'required|integer|min:50|max:100000',
            'duration' => 'required|string|max:40',
            'min_level' => 'required|integer|min:1|max:5',
        ], [
            'name.required' => 'اكتبي اسم الخدمة.',
            'sample.required' => 'اكتبي وصف الخدمة.',
            'price.required' => 'حددي السعر.',
            'min_level.required' => 'حددي أقل مستوى للمنفذة.',
        ]);
    }

    private function owns(Service $service): void
    {
        abort_unless($service->seller_id === auth()->id(), 403);
    }
}
