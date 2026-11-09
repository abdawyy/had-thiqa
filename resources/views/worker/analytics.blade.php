@extends('layouts.app')

@section('content')
    <header class="px-4 pt-6 text-right sm:px-6 lg:px-8">
        <h1 class="text-[28px] font-extrabold">تحليل الشغل</h1>
        <p class="text-[13px] text-[#8A7280]">{{ $worker->name }} · المنصة تخصم {{ \App\Support\Split::percent('platform') }}% قبل نصيبك</p>
    </header>
    <section class="mx-4 mt-4 grid grid-cols-2 gap-2 sm:mx-6 sm:grid-cols-4 lg:mx-8">
        @foreach (['الطلبات' => $orders, 'مكتمل' => $done, 'شغال' => $open, 'في المعاد' => $onTime] as $label => $value)
            <article class="tile text-right">
                <p class="text-[11px] text-[#8A7280]">{{ $label }}</p>
                <p class="num text-[22px] font-extrabold">{{ $value }}</p>
            </article>
        @endforeach
    </section>
    <section class="mx-4 mt-4 grid gap-4 sm:mx-6 lg:mx-8 lg:grid-cols-2">
        <article class="rounded-[24px] bg-[#5C3A4A] p-5 text-[#FBF6F1]">
            <p class="text-[12px] text-[#F0D7E4]">نصيبك من المكتمل · {{ \App\Support\Split::percent('executor') }}%</p>
            <p class="num mt-2 text-4xl font-extrabold">{{ number_format($earned) }} ج</p>
            <p class="mt-2 text-[13px]">متوسط التقييم {{ $rating }} من 5</p>
        </article>
        @include('partials.payout', ['available' => $available])
    </section>
@endsection
