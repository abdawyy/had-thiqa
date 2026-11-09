@extends('layouts.app')

@section('content')
    <header class="flex items-center justify-between px-4 pt-5 sm:px-6 lg:px-8">
        <a href="{{ route('worker.orders.show', $order) }}" class="rounded-full bg-white px-4 py-2 text-[13px] font-extrabold">رجوع</a>
        <h1 class="text-[22px] font-extrabold">توزيع الأرباح</h1>
    </header>
    <div class="mx-4 mt-4 grid items-start gap-4 sm:mx-6 lg:mx-8 lg:grid-cols-2">
    <section class="rounded-[24px] bg-[#5C4454] p-4 text-[#FDF6EE] md:p-6">
        <p class="text-[12px] text-[#F0D7E4]">الطلب #{{ $order->number }} · {{ $order->title }}</p>
        <p class="mt-2 text-[13px]">قيمة الطلب الموزَّعة</p>
        <p class="num text-[32px] font-extrabold">{{ number_format($order->distributable()) }} <span class="text-[14px]">جنيه</span></p>
    </section>
    <section class="space-y-2">
        @foreach ($order->shares() as $share)
            <div class="flex items-center justify-between rounded-2xl bg-white px-4 py-3">
                <span class="num text-[16px] font-extrabold">{{ number_format($share['amount']) }} ج · {{ $share['pct'] }}%</span>
                <span class="text-[14px] font-extrabold">{{ $share['label'] }}</span>
            </div>
        @endforeach
        @if ($order->tracking_fee > 0)
            <div class="flex items-center justify-between rounded-2xl bg-[#F8E9B6] px-4 py-3">
                <span class="num text-[16px] font-extrabold">{{ number_format($order->tracking_fee) }} ج</span>
                <span class="text-[14px] font-extrabold">متابعة تفصيلية: مبلغ للمنصة</span>
            </div>
        @endif
        <p class="text-right text-[12px] leading-6 text-[#8A7280]">متابعة تفصيلية: مبلغ للمنصة، خارج توزيع الأربعة.</p>
    </section>
    </div>
    <section class="mx-4 mt-4 rounded-[24px] bg-[#F8E2CA] p-4 text-right sm:mx-6 md:p-6 lg:mx-8">
        <p class="text-[13px] font-extrabold">لماذا للـ SOP والمهارة نصيب؟</p>
        <p class="mt-2 text-[12.5px] leading-7 text-[#6B4F60]">كل خطوة تتكرر مع كل طلب. كاتبة الـ SOP والمهارة تأخذ نسبة في كل مرة تُستخدم، فتصبح المعرفة أصلًا ينمو.</p>
    </section>
    <p class="mx-4 mt-3 text-right text-[11px] leading-6 text-[#8A7280] sm:mx-6 lg:mx-8">الأرقام توضيحية للنموذج. التحويل يتم بعد اعتماد البارتنر واستلام العميل.</p>
@endsection
