@extends('layouts.app')

@section('plain')
@endsection

@section('content')
    <header class="px-4 pt-6 text-right sm:px-6 lg:px-8">
        <h1 class="text-[28px] font-extrabold">تحليل الأطراف</h1>
        <p class="text-[13px] text-[#8A7280]">العميل يدفع. المنفذة تكسب {{ \App\Support\Split::percent('executor') }}%. البائعة تكسب {{ \App\Support\Split::percent('author') }}%. المنصة تخصم {{ \App\Support\Split::percent('platform') }}%.</p>
    </header>

    <section class="mx-4 mt-4 grid grid-cols-2 gap-2 sm:mx-6 sm:grid-cols-4 lg:mx-8">
        @foreach ([
            'الطلبات' => $orders,
            'شغالة' => $open,
            'مكتملة' => $done,
            'المحصّل' => number_format($collected),
            'نصيب المنفذات' => number_format($workerShare),
            'ربح البائعات' => number_format($sellerShare),
            'حصة المنصة' => number_format($platform),
            'تحويلات إنستاباي' => number_format($payouts),
        ] as $label => $value)
            <article class="rounded-2xl bg-white p-3 text-right">
                <p class="text-[11px] text-[#8A7280]">{{ $label }}</p>
                <p class="num text-[22px] font-extrabold">{{ $value }}</p>
            </article>
        @endforeach
    </section>

    <div class="mx-4 mt-4 grid items-start gap-4 sm:mx-6 lg:mx-8 lg:grid-cols-3">
        <section class="rounded-[24px] bg-white p-4">
            <h2 class="text-right text-[18px] font-extrabold">الخدمات</h2>
            <div class="mt-3 space-y-3">
                @foreach ($gigs as $gig)
                    <article class="rounded-2xl bg-[#FBF6F1] p-3 text-right">
                        <p class="text-[14px] font-extrabold">{{ $gig['name'] }}</p>
                        <p class="text-[12px] text-[#8A7280]">البائعة {{ $gig['seller'] }} · سعر العميل {{ number_format($gig['price']) }} ج · {{ $gig['orders'] }} طلب</p>
                        <p class="mt-2 text-[12px]">محصّل {{ number_format($gig['collected']) }} ج</p>
                        <p class="text-[12px] text-[#3E5C45]">المنفذة {{ number_format($gig['worker']) }} · ربح البائعة {{ number_format($gig['seller_gain']) }} · المنصة {{ number_format($gig['platform']) }}</p>
                        <p class="text-[12px] text-[#8A7280]">ربح معلّق للبائعة {{ number_format($gig['pending']) }} ج</p>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="rounded-[24px] bg-white p-4">
            <h2 class="text-right text-[18px] font-extrabold">المنفذات</h2>
            <div class="mt-3 space-y-3">
                @foreach ($workers as $person)
                    <article class="rounded-2xl bg-[#F3F8F1] p-3 text-right">
                        <p class="text-[14px] font-extrabold">{{ $person['name'] }}</p>
                        <p class="text-[12px] text-[#8A7280]">{{ $person['level'] }} · {{ $person['jobs'] }} مكتملة · {{ $person['open'] }} شغالة</p>
                        <p class="mt-2 text-[13px] font-extrabold text-[#3E5C45]">نصيبها الجاهز {{ number_format($person['available']) }} ج</p>
                        <p class="text-[12px]">مكتسب {{ number_format($person['earned']) }} · معلّق {{ number_format($person['pending']) }}</p>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="rounded-[24px] bg-white p-4">
            <h2 class="text-right text-[18px] font-extrabold">البائعات</h2>
            <div class="mt-3 space-y-3">
                @foreach ($sellers as $person)
                    <article class="rounded-2xl bg-[#F8E9B6] p-3 text-right">
                        <p class="text-[14px] font-extrabold">{{ $person['name'] }}</p>
                        <p class="text-[12px] text-[#8A7280]">{{ $person['gigs'] }} خدمة · {{ $person['orders'] }} طلب · بتكسب مش بتدفع</p>
                        <p class="mt-2 text-[13px] font-extrabold">ربحها الجاهز {{ number_format($person['available']) }} ج</p>
                        <p class="text-[12px]">من المكتمل {{ number_format($person['gain']) }} · معلّق {{ number_format($person['pending']) }}</p>
                    </article>
                @endforeach
            </div>
        </section>
    </div>
@endsection
