@extends('layouts.app')

@section('plain')
@endsection

@section('content')
    <header class="px-4 pt-6 text-right sm:px-6 lg:px-8">
        <h1 class="text-[28px] font-extrabold">تحليل البيع</h1>
        <p class="text-[13px] text-[#8A7280]">{{ $seller->name }} · نصيبك {{ \App\Support\Split::percent('author') }}% والمنصة {{ \App\Support\Split::percent('platform') }}%</p>
    </header>
    <section class="mx-4 mt-4 grid grid-cols-2 gap-2 sm:mx-6 sm:grid-cols-4 lg:mx-8">
        @foreach (['الطلبات' => $orders, 'مكتمل' => $done, 'شغال' => $open, 'الخدمات' => $gigs->count()] as $label => $value)
            <article class="rounded-2xl bg-white p-3 text-right">
                <p class="text-[11px] text-[#8A7280]">{{ $label }}</p>
                <p class="num text-[22px] font-extrabold">{{ $value }}</p>
            </article>
        @endforeach
    </section>
    <section class="mx-4 mt-4 grid gap-4 sm:mx-6 lg:mx-8 lg:grid-cols-2">
        <article class="rounded-[24px] bg-[#5C3A4A] p-5 text-right text-[#FBF6F1]">
            <p class="text-[12px] text-[#F0D7E4]">مبيعات محصّلة</p>
            <p class="num mt-2 text-4xl font-extrabold">{{ number_format($sales) }} ج</p>
            <p class="mt-2">نصيبك من المكتمل {{ number_format($share) }} ج</p>
        </article>
        <article class="rounded-[24px] bg-white p-4">
            <h2 class="text-right text-[16px] font-extrabold">الخدمات حسب الطلب</h2>
            <div class="mt-3 space-y-2">
                @foreach ($gigs->sortByDesc('orders_count') as $gig)
                    <div class="flex items-center justify-between text-[13px]">
                        <span class="num font-extrabold">{{ $gig->orders_count }}</span>
                        <span>{{ $gig->name }}</span>
                    </div>
                @endforeach
            </div>
        </article>
        @include('partials.payout', ['available' => $available])
    </section>
@endsection
