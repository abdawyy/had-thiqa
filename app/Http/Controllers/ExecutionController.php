<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use App\Models\Order;
use App\Models\OrderStep;
use App\Services\OrderFlow;
use App\Support\Demo;
use App\Support\SopLibrary;
use Illuminate\Http\Request;

class ExecutionController extends Controller
{
    public function __construct(private OrderFlow $flow) {}

    public function claim(Offer $offer)
    {
        $order = $this->flow->claim($offer, Demo::worker());

        return redirect()
            ->route('worker.orders.show', $order)
            ->with('status', 'استلمتي المهمة. ابدئي من أول خطوة.');
    }

    public function show(Order $order)
    {
        $this->guard($order);
        $order->load('steps', 'service', 'worker', 'rating');

        return view('worker.execution', [
            'order' => $order,
            'worker' => Demo::worker(),
            'stages' => config('hadthiqa.worker_stages'),
            'checks' => SopLibrary::checksFor($order->service->slug),
        ]);
    }

    public function begin(Order $order)
    {
        $this->guard($order);
        $this->flow->begin($order);

        return back()->with('status', 'المهمة بدأت. امشي خطوات الـ SOP بالترتيب.');
    }

    public function step(Request $request, Order $order, OrderStep $step)
    {
        $this->guard($order);
        $this->ownsStep($order, $step);
        $data = $request->validate(['note' => 'nullable|string|max:500']);
        $this->flow->completeStep($step, $data['note'] ?? null);

        return back()->with('status', $step->done_label);
    }

    public function skill(Order $order, OrderStep $step)
    {
        $this->guard($order);
        $this->ownsStep($order, $step);
        $this->flow->runSkill($step);

        return back()->with('status', 'اتطلعت النتيجة. راجعيها ثم اعتمديها.');
    }

    public function confirmSkill(Order $order, OrderStep $step)
    {
        $this->guard($order);
        $this->ownsStep($order, $step);
        $this->flow->confirmSkill($step);

        return back()->with('status', $step->fresh()->done() ? 'أعتمدتِ نتيجة المهارة' : 'شغّلي المهارة أولاً');
    }

    public function deliver(Request $request, Order $order)
    {
        $this->guard($order);
        abort_unless($order->pipeline_stage === 2, 422);
        $order->load('service');

        $checks = collect(SopLibrary::checksFor($order->service->slug))
            ->values()
            ->map(fn (string $label, int $index) => [
                'label' => $label,
                'ok' => $request->boolean('checks.'.$index),
            ])->all();

        $this->flow->deliver($order, $checks);
        $order->refresh();

        return back()->with('status', $order->deliveredCleanly()
            ? 'اتسلم العمل وبانتظار اعتماد البارتنر.'
            : 'تسليم وفيه بنود مراجعة');
    }

    public function approve(Order $order)
    {
        $this->guard($order);
        $this->flow->approve($order);

        return back()->with('status', 'البارتنر اعتمد التسليم.');
    }

    public function split(Order $order)
    {
        $this->guard($order);
        $order->load('service', 'rating');

        return view('worker.split', ['order' => $order]);
    }

    private function guard(Order $order): void
    {
        abort_unless($order->worker_id === Demo::worker()->id, 403);
    }

    private function ownsStep(Order $order, OrderStep $step): void
    {
        abort_unless($step->order_id === $order->id, 404);
        $order->load('steps');
        $current = $order->currentStep();
        abort_unless($current && $current->id === $step->id, 422);
    }
}
