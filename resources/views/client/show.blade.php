@extends('layouts.app')

@section('plain')
@endsection

@php
    $phase = $order->clientPhase();
    $phaseIndex = ['payment' => 1, 'waiting' => 2, 'working' => 2, 'review' => 3, 'deliver' => 3, 'done' => 4][$phase] ?? 0;
@endphp

@section('content')
    <header class="flex items-center justify-between px-4 pt-5 sm:px-6 lg:px-8">
        <a href="{{ route('home') }}" class="rounded-full bg-white px-4 py-2 text-[13px] font-extrabold">الرئيسية</a>
        <div class="text-right">
            <p class="text-[12px] text-[#8A7280]">طلب #{{ $order->number }}</p>
            <h1 class="text-[22px] font-extrabold">{{ $order->title }}</h1>
        </div>
    </header>

    <div class="grid items-start gap-4 lg:grid-cols-2">

    <section class="mx-4 mt-4 sm:mx-6 lg:mx-8 lg:max-w-none rounded-[24px] bg-white p-4">
        <div class="flex items-center justify-between text-[12px] font-bold text-[#8A7280]">
            <span class="num">{{ number_format($order->total) }} جنيه</span>
            <span>خط عمليات طلبك</span>
        </div>
        <div class="mt-3 flex justify-between gap-1">
            @foreach ($stages as $index => $stage)
                <div class="flex-1 text-center">
                    <span class="mx-auto grid h-7 w-7 place-items-center rounded-full text-[11px] font-extrabold {{ $index <= $phaseIndex ? 'bg-[#5C4454] text-white' : 'bg-[#EFE7F0] text-[#8A7280]' }}">{{ $index + 1 }}</span>
                    <p class="mt-1 text-[9px] font-bold leading-4">{{ $stage }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <section class="mx-4 mt-4 sm:mx-6 lg:mx-8 lg:max-w-none rounded-[24px] bg-white p-4 text-right">
        <p class="text-[12px] text-[#8A7280]">{{ $order->client_name }} · {{ $order->speedMeta()['label'] }} · {{ $order->tierMeta()['detail'] }}</p>
        <p class="mt-2 text-[14px] leading-7">{{ $order->description }}</p>
        @if ($order->worker)
            <p class="mt-2 text-[12px] font-bold">المنفذة: {{ $order->worker->name }} · {{ $order->worker->tierMeta()['name'] }}</p>
        @endif
        @if ($order->discount_percent)
            <p class="mt-1 text-[12px] text-[#2F4529]">خصم كود {{ $order->referral_code }} · {{ $order->discount_percent }}%</p>
        @endif
        @if ($order->photos)
            <div class="mt-3 grid grid-cols-3 gap-2">
                @foreach ($order->photos as $photo)
                    <img src="{{ asset('storage/'.$photo) }}" alt="" class="h-20 w-full rounded-xl object-cover">
                @endforeach
            </div>
        @endif
    </section>

    @if ($phase === 'payment')
        <section class="mx-4 mt-4 sm:mx-6 lg:mx-8 lg:max-w-none rounded-[24px] bg-[#5C4454] p-4 text-right text-[#FDF6EE]">
            <p class="text-[14px] font-extrabold">يُحجز {{ number_format($order->total) }} جنيه لدى المنصة</p>
            @if ($order->payment_method === 'instapay')
                <p class="mt-2 text-[12px] leading-6 text-[#F3E4EC]">حوّلي على إنستاباي المنصة. المبلغ يتقفل عندنا، وبنخصم {{ \App\Support\Split::percent('platform') }}% ثم نحول الباقي بعد الاستلام. التحويل هنا تجريبي.</p>
                <p class="mt-2 rounded-2xl bg-white/10 px-3 py-2 text-[13px] font-extrabold">{{ $handle }}</p>
            @else
                <p class="mt-2 text-[12px] leading-6">الدفع بال{{ $order->payment_method === 'card' ? 'كارت' : 'محفظة' }} تجريبي في هذا النموذج.</p>
            @endif
            <form method="POST" action="{{ route('client.orders.pay', $order) }}">
                @csrf
                <input type="hidden" name="payment_method" value="{{ $order->payment_method }}">
                <button class="mt-3 h-12 w-full rounded-2xl bg-[#F6DE9E] font-extrabold text-[#5A4A22]">أكدت التحويل · ابدأ الطلب</button>
            </form>
        </section>
    @elseif ($phase === 'waiting')
        <section class="mx-4 mt-4 sm:mx-6 lg:mx-8 lg:max-w-none rounded-[24px] bg-white p-4 text-right">
            <h2 class="text-[18px] font-extrabold">تم تسجيل الطلب — لم يبدأ أحد بعد</h2>
            <p class="mt-2 text-[13px] leading-7 text-[#6B4F60]">سنبلغك عند بدء التنفيذ.</p>
        </section>
    @elseif ($phase === 'working')
        <section class="mx-4 mt-4 sm:mx-6 lg:mx-8 lg:max-w-none rounded-[24px] bg-white p-4 text-right">
            <h2 class="text-[18px] font-extrabold">المنفذة تعمل على طلبك</h2>
            @if ($order->tracking_fee > 0)
                <p class="mt-2 text-[13px] leading-7">خطوات الـ SOP ظاهرة لأنك أضفتِ المتابعة التفصيلية.</p>
                <ol class="mt-3 space-y-2">
                    @foreach ($order->steps as $step)
                        <li class="rounded-2xl bg-[#F6F0E8] px-3 py-2 text-[12px] {{ $step->done() ? 'text-[#2F4529]' : 'text-[#8A7280]' }}">
                            {{ $step->position }}. {{ $step->done() ? $step->done_label : $step->title }}
                        </li>
                    @endforeach
                </ol>
            @else
                <p class="mt-2 text-[13px] leading-7 text-[#6B4F60]">التفاصيل خطوة بخطوة غير متاحة. الافتراضي أن تعرفي بدء التنفيذ فقط، ثم التسليم.</p>
            @endif
        </section>
    @elseif ($phase === 'review')
        <section class="mx-4 mt-4 sm:mx-6 lg:mx-8 lg:max-w-none rounded-[24px] bg-white p-4 text-right">
            <h2 class="text-[18px] font-extrabold">اكتمل تدقيق الجودة</h2>
            <p class="mt-2 text-[13px] leading-7">تم التحقق من الأرقام والملاحظات قبل التسليم، وبانتظار اعتماد البارتنر.</p>
        </section>
    @elseif ($phase === 'deliver')
        <section class="mx-4 mt-4 sm:mx-6 lg:mx-8 lg:max-w-none rounded-[24px] bg-white p-4 text-right">
            <h2 class="text-[18px] font-extrabold">جاهز للتسليم</h2>
            <p class="mt-2 text-[13px] leading-7">الملف النهائي جاهز. في النسخة التجريبية لا يوجد ملف فعلي — التسليم نفسه مسجّل على الطلب.</p>
            <p class="mt-2 rounded-2xl bg-[#F6F0E8] px-3 py-2 text-[12px]">النسخة المعتمدة.pdf · صفحة توضيحية</p>
            <form method="POST" action="{{ route('client.orders.rate', $order) }}" class="mt-4 space-y-3">
                @csrf
                @foreach (['quality' => 'جودة العمل', 'price_score' => 'السعر', 'dealing' => 'التعامل'] as $field => $label)
                    <fieldset>
                        <legend class="text-[13px] font-extrabold">{{ $label }}</legend>
                        <div class="mt-1 flex justify-end gap-1">
                            @for ($star = 1; $star <= 5; $star++)
                                <label class="grid h-10 w-10 cursor-pointer place-items-center rounded-full bg-[#F6F0E8] text-[13px] font-extrabold has-[:checked]:bg-[#5C4454] has-[:checked]:text-white">
                                    <input class="sr-only" type="radio" name="{{ $field }}" value="{{ $star }}" required>
                                    {{ $star }}
                                </label>
                            @endfor
                        </div>
                    </fieldset>
                @endforeach
                <textarea name="note" rows="3" placeholder="ملاحظة عن العمل (اختياري)" class="w-full rounded-2xl border border-black/10 p-3 text-[13px]"></textarea>
                <button class="h-12 w-full rounded-2xl bg-[#5C3A4A] font-extrabold text-white">إرسال التقييم</button>
            </form>
        </section>
    @else
        <section class="mx-4 mt-4 sm:mx-6 lg:mx-8 lg:max-w-none rounded-[24px] bg-[#DCEFD6] p-4 text-right">
            <h2 class="text-[18px] font-extrabold">تم إرسال التقييم</h2>
            <p class="mt-2 text-[13px] leading-7">{{ $order->rating?->average() }} من 5 · اتحرر المبلغ للمنفذة والبارتنر والمنصة.</p>
            @if ($order->rating?->note)
                <p class="mt-2 text-[12px]">{{ $order->rating->note }}</p>
            @endif
        </section>
        <section class="mx-4 mt-3 sm:mx-6 lg:mx-8 space-y-2">
            @foreach ($order->shares() as $share)
                @continue($share['id'] === 'author')
                <div class="flex items-center justify-between rounded-2xl bg-white px-4 py-3 text-[13px] font-extrabold">
                    <span class="num">{{ number_format($share['amount']) }} ج</span>
                    <span>{{ $share['label'] }} · {{ $share['pct'] }}%</span>
                </div>
            @endforeach
        </section>
    @endif

    @if ($order->pipeline_stage < 5)
        <form method="POST" action="{{ route('client.orders.simulate', $order) }}" class="mx-4 mt-4 sm:mx-6 lg:mx-8 lg:max-w-none">
            @csrf
            <button class="h-10 w-full rounded-2xl border border-dashed border-[#C7B7C0] text-[12px] font-bold text-[#8A7280]">انتقلي للمرحلة التالية (نموذج تجريبي)</button>
        </form>
    @endif

    </div>
@endsection
