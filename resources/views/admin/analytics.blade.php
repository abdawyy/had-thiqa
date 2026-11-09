@extends('layouts.app')

@section('plain')
@endsection

@section('content')
    <header class="px-4 pt-6 text-right sm:px-6 lg:px-8">
        <h1 class="text-[28px] font-extrabold">تحليل المنصة</h1>
        <p class="text-[13px] text-[#8A7280]">المنصة في الوسط وتخصم {{ \App\Support\Split::percent('platform') }}% من كل خدمة</p>
    </header>
    <section class="mx-4 mt-4 grid grid-cols-2 gap-2 sm:mx-6 sm:grid-cols-4 lg:mx-8">
        @foreach (['الطلبات' => $orders, 'شغالة' => $open, 'مكتملة' => $done, 'المنفذات' => $workers, 'البائعات' => $sellers] as $label => $value)
            <article class="rounded-2xl bg-white p-3 text-right">
                <p class="text-[11px] text-[#8A7280]">{{ $label }}</p>
                <p class="num text-[22px] font-extrabold">{{ $value }}</p>
            </article>
        @endforeach
        <article class="rounded-2xl bg-[#5C3A4A] p-3 text-right text-[#FBF6F1]">
            <p class="text-[11px] text-[#F0D7E4]">حصة المنصة 10%</p>
            <p class="num text-[22px] font-extrabold">{{ number_format($platform) }}</p>
        </article>
        <article class="rounded-2xl bg-[#F8E9B6] p-3 text-right">
            <p class="text-[11px]">المحصّل</p>
            <p class="num text-[22px] font-extrabold">{{ number_format($collected) }}</p>
        </article>
        <article class="rounded-2xl bg-white p-3 text-right">
            <p class="text-[11px] text-[#8A7280]">تحويلات إنستاباي</p>
            <p class="num text-[22px] font-extrabold">{{ number_format($payouts) }}</p>
        </article>
    </section>
    <section class="mx-4 mt-4 rounded-[24px] bg-white p-4 sm:mx-6 lg:mx-8">
        <h2 class="text-right text-[16px] font-extrabold">أعلى الخدمات</h2>
        <div class="mt-3 space-y-2">
            @foreach ($top as $row)
                <div class="flex items-center justify-between text-[13px]">
                    <span class="num font-extrabold">{{ $row['count'] }} طلب · {{ number_format($row['total']) }} ج</span>
                    <span>{{ $row['name'] }}</span>
                </div>
            @endforeach
        </div>
    </section>
@endsection
