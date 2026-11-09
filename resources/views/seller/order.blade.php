@extends('layouts.app')

@section('plain')
@endsection

@section('content')
    <header class="flex items-center justify-between px-4 pt-6 sm:px-6 lg:px-8">
        <a href="{{ route('seller.home') }}" class="rounded-full bg-white px-4 py-2 text-[13px] font-extrabold">رجوع</a>
        <div class="text-right">
            <p class="text-[12px] text-[#8A7280]">طلب #{{ $order->number }}</p>
            <h1 class="text-[24px] font-extrabold">{{ $order->title }}</h1>
        </div>
    </header>

    <div class="mx-4 mt-4 grid items-start gap-4 sm:mx-6 lg:mx-8 lg:grid-cols-2">
        <section class="rounded-[24px] bg-white p-4 text-right md:p-6">
            <p class="text-[13px] font-extrabold">{{ $order->statusLabel() }}</p>
            <p class="mt-2 text-[13px] leading-7">{{ $order->description }}</p>
            <p class="mt-3 text-[12px] text-[#8A7280]">العميل: {{ $order->client_name }}</p>
            <p class="text-[12px] text-[#8A7280]">المنفذة: {{ $order->worker?->name ?? 'لسه ما اتحددتش' }}</p>
            <p class="text-[12px] text-[#8A7280]">التسليم: {{ $order->due_label }} @if ($order->due_at) · {{ $order->due_at->format('Y-m-d H:i') }} @endif</p>
            <p class="mt-3 text-[14px] font-extrabold">قيمة الطلب {{ number_format($order->total) }} ج</p>
            <p class="text-[13px] text-[#3E5C45]">نصيبك {{ \App\Support\Split::percent('author') }}% = {{ number_format(\App\Support\Split::amount($order->distributable(), 'author')) }} ج بعد استلام العميل. المنصة تخصم {{ \App\Support\Split::percent('platform') }}%.</p>
            @if ($order->rating)
                <p class="mt-3 text-[13px]">تقييم العميل {{ $order->rating->average() }} من 5. {{ $order->rating->note }}</p>
            @endif
        </section>
        <section class="rounded-[24px] bg-white p-4 md:p-6">
            <h2 class="text-right text-[16px] font-extrabold">تقدم التنفيذ</h2>
            <p class="mt-2 text-right text-[13px] leading-7 text-[#6B4F60]">تفاصيل شغل المنفذة خاصة بيها. أنتِ بتشوفي التقدم بس، مش خطوات المهارة ولا ملاحظاتها.</p>
            <p class="mt-4 text-right text-[28px] font-extrabold num">{{ $order->stepsDone() }} <span class="text-[14px] text-[#8A7280]">من {{ $order->steps->count() }}</span></p>
            <div class="mt-3 h-2 overflow-hidden rounded-full bg-[#EFE7F0]">
                <div class="h-full rounded-full bg-[#5C3A4A]" style="width: {{ $order->steps->count() ? round($order->stepsDone() / $order->steps->count() * 100) : 0 }}%"></div>
            </div>
        </section>
    </div>
@endsection
