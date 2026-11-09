@extends('layouts.app')

@section('content')
    <header class="flex items-start justify-between px-4 pb-2 pt-5 sm:px-6 lg:px-8">
        <span class="inline-flex items-center gap-1 rounded-full bg-white px-3 py-1.5 text-[12px] font-extrabold text-[#6B4F60] shadow-sm">
            نموذج تجريبي
            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3 5 6v6c0 4.5 3 7.5 7 9 4-1.5 7-4.5 7-9V6l-7-3Z"/></svg>
        </span>
        <a href="{{ route('home') }}" class="flex items-center gap-2 text-right">
            <span>
                <span class="block text-[18px] font-extrabold leading-none">حد ثقة</span>
                <span class="mt-1 block text-[11px] text-[#8A7280]">منصة عمل للسيدات</span>
            </span>
            <span class="grid h-12 w-12 place-items-center rounded-2xl bg-[#6B4F60] text-[#FBF6F1] shadow-sm">
                <svg viewBox="0 0 24 24" class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3 5 6v6c0 4.5 3 7.5 7 9 4-1.5 7-4.5 7-9V6l-7-3Z"/><path d="m8.5 12 2.4 2.4 4.6-5"/></svg>
            </span>
        </a>
    </header>

    <main class="mx-auto max-w-3xl px-4 pb-10 sm:px-6">
        <h1 class="mt-6 text-right text-[32px] font-extrabold leading-none text-[#3F2C39] md:text-5xl">اختر مسارك</h1>
        <p class="mt-3 text-right text-[14px] leading-7 text-[#8A7280]">ثلاثة مسارات أساسية، لكل مسار خط عمليات واضح، وتقييم في نهايته على الجودة والسعر والتعامل.</p>

        <a href="{{ route('login', ['as' => 'worker']) }}" class="mt-5 block rounded-[28px] border border-[#E7E0D8] bg-[#F7F1EA] p-4 md:p-6">
            <div class="flex items-start justify-between">
                <span class="grid h-12 w-12 place-items-center rounded-full bg-[#EFE7F0] text-[#6B4F60]">
                    <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="8"/><path d="M12 8v5l3 2"/></svg>
                </span>
                <p class="text-[13px] font-bold text-[#8A7280]">المسار الأول · المنفّذة</p>
            </div>
            <h2 class="mt-3 text-right text-[26px] font-extrabold leading-snug">سجّلي معنا</h2>
            <p class="mt-2 text-right text-[13px] leading-7 text-[#6B4F60]">تحدّدين إتاحة العمل لديك، وتختارين المهمة المناسبة، ويبدأ التنفيذ بعد اعتماد صاحب المهمة — أو باعتماد إداريات المنصة إن اختار «حد ثقة يختار» — ثم يُراجع العمل قبل التسليم.</p>
            <div class="mt-3 flex flex-wrap justify-end gap-1.5">
                @foreach ($workerStages as $index => $stage)
                    <span class="rounded-full bg-white px-2.5 py-1 text-[11px] font-bold text-[#6B4F60]">{{ $index + 1 }}. {{ $stage }}</span>
                @endforeach
            </div>
            <span class="mt-4 flex h-14 items-center justify-center rounded-2xl bg-[#D9D3CC] text-[16px] font-extrabold text-[#3F2C39]">الدخول إلى لوحة العمل ←</span>
        </a>

        <a href="{{ route('client.request') }}" class="mt-4 block rounded-[28px] border border-[#E7D7C4] bg-[#F8F3EC] p-4 md:p-6">
            <div class="flex items-start justify-between">
                <span class="grid h-12 w-12 place-items-center rounded-full bg-[#F3E6D4] text-[#6B4F60]">
                    <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M8 4h8v16H8z"/><path d="M10 8h4M10 12h4"/></svg>
                </span>
                <p class="text-[13px] font-bold text-[#8A7280]">المسار الثاني · صاحب العمل</p>
            </div>
            <h2 class="mt-3 text-right text-[26px] font-extrabold leading-snug">أحتاج «حد ثقة» للعمل</h2>
            <p class="mt-2 text-right text-[13px] leading-7 text-[#6B4F60]">تختار الخدمة وتحدّد مستوى التفاصيل المطلوبة، وتدفع قيمتها على المنصة، ثم تتابع التنفيذ حتى التسليم والتقييم.</p>
            <div class="mt-3 flex flex-wrap justify-end gap-1.5">
                @foreach ($clientStages as $index => $stage)
                    <span class="rounded-full bg-white px-2.5 py-1 text-[11px] font-bold text-[#6B4F60]">{{ $index + 1 }}. {{ $stage }}</span>
                @endforeach
            </div>
            <span class="mt-4 flex h-14 items-center justify-center rounded-2xl bg-[#E7D3B0] text-[16px] font-extrabold text-[#3F2C39]">ابدأ الطلب ←</span>
        </a>

        <a href="{{ route('login', ['as' => 'admin']) }}" class="mt-4 block rounded-[28px] border border-[#E4D5E0] bg-[#F6F1F4] p-4 md:p-6">
            <div class="flex items-start justify-between">
                <span class="grid h-12 w-12 place-items-center rounded-full bg-[#EFE7F0] text-[#6B4F60]">
                    <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h16M4 12h16M4 17h10"/></svg>
                </span>
                <p class="text-[13px] font-bold text-[#8A7280]">المسار الثالث · إداريات المنصة</p>
            </div>
            <h2 class="mt-3 text-right text-[26px] font-extrabold leading-snug">لوحة إداريات المنصة</h2>
            <p class="mt-2 text-right text-[13px] leading-7 text-[#6B4F60]">مراجعة الطلبات والمدفوعات، واعتماد بدء التنفيذ، ومتابعة مراحل العمل، وتوزيع المبالغ، وسجل التقييمات.</p>
            <div class="mt-3 flex flex-wrap justify-end gap-1.5">
                @foreach (['المدفوعات', 'اعتماد البدء', 'التوزيع', 'التقييمات'] as $index => $stage)
                    <span class="rounded-full bg-white px-2.5 py-1 text-[11px] font-bold text-[#6B4F60]">{{ $index + 1 }}. {{ $stage }}</span>
                @endforeach
            </div>
            <span class="mt-4 flex h-14 items-center justify-center rounded-2xl bg-[#E4D5E0] text-[16px] font-extrabold text-[#3F2C39]">الدخول إلى لوحة الإداريات ←</span>
        </a>

        @if ($resume)
            <a href="{{ route('client.orders.show', $resume) }}" class="mt-3 block rounded-2xl bg-white px-4 py-3 text-center text-[13px] font-extrabold text-[#5C4454]">تابعي الطلب #{{ $resume->number }}</a>
        @endif
        <p class="mt-4 text-center text-[12px] text-[#A9939F]">كل البيانات هنا تجريبية — لا توجد مدفوعات حقيقية.</p>
    </main>
@endsection
