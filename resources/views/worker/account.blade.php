@extends('layouts.app')

@section('content')
    <header class="flex items-center justify-between px-4 pt-6 sm:px-6 lg:px-8">
        <span class="grid h-12 w-12 place-items-center rounded-full bg-[#5C4454] text-[18px] font-extrabold text-white">{{ $worker->letter }}</span>
        <div class="text-right">
            <h1 class="text-[28px] font-extrabold">حسابي</h1>
            <p class="text-[12px] text-[#8A7280]">{{ $worker->name }} · {{ $worker->level_name }}</p>
        </div>
    </header>
    <div class="mx-4 mt-4 grid items-start gap-4 sm:mx-6 lg:mx-8 lg:grid-cols-2">
    <section class="rounded-[24px] bg-white p-4 text-right md:p-6">
        <p class="text-[13px] leading-7 text-[#6B4F60]">نصيبك الجاهز {{ number_format($available) }} جنيه · معلّق لحد تقييم العميل {{ number_format($pending) }} جنيه. العميل هو اللي دفع، والمنصة بتحوّل لك {{ \App\Support\Split::percent('executor') }}%.</p>
        <div class="mt-3 grid grid-cols-3 gap-2 text-center">
            @foreach (['مهام منجزة' => $worker->jobs_done, 'بدون تعديل' => $worker->jobs_clean, 'التقييم' => $worker->rating] as $label => $value)
                <div class="rounded-2xl bg-[#F6F0E8] py-3">
                    <p class="num text-[18px] font-extrabold">{{ $value }}</p>
                    <p class="text-[11px] text-[#8A7280]">{{ $label }}</p>
                </div>
            @endforeach
        </div>
    </section>
    @if ($last)
        <section class="rounded-[24px] bg-white p-4 text-right md:p-6">
            <p class="text-[13px] font-extrabold">نصيبك من آخر مهمة</p>
            <p class="mt-1 text-[12px] leading-6 text-[#6B4F60]">{{ \App\Support\Split::percent('executor') }}% من قيمة الطلب ({{ number_format($last->distributable()) }} جنيه) = <span class="num font-extrabold">{{ number_format($last->workerShare()) }} جنيه</span></p>
            <a href="{{ route('worker.orders.split', $last) }}" class="mt-3 flex h-11 items-center justify-center rounded-2xl bg-[#F6DE9E] text-[13px] font-extrabold text-[#5A4A22]">عرض التوزيع بالتفصيل</a>
        </section>
    @endif
    </div>
    <p class="mx-4 mt-4 text-right text-[12px] leading-6 text-[#8A7280] sm:mx-6 lg:mx-8">السعر يحدده الأدمن لكل خدمة، والمنفذة لا تحدد سعرها.</p>
    <a href="{{ route('worker.analytics') }}" class="mx-4 mt-4 flex h-12 items-center justify-center rounded-2xl bg-[#5C3A4A] text-[14px] font-extrabold text-white sm:mx-6 lg:mx-8 lg:max-w-xs">تحليل الشغل والسحب</a>
@endsection
