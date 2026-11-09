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
                <span class="mt-1 block text-[11px] text-[#8A7280]">منصة شغل للستات</span>
            </span>
            <span class="grid h-12 w-12 place-items-center rounded-2xl bg-[#5C3A4A] text-[#FBF6F1] shadow-sm">
                <svg viewBox="0 0 24 24" class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3 5 6v6c0 4.5 3 7.5 7 9 4-1.5 7-4.5 7-9V6l-7-3Z"/><path d="m8.5 12 2.4 2.4 4.6-5"/></svg>
            </span>
        </a>
    </header>

    <main class="px-4 sm:px-6 lg:px-8">
        <h1 class="mt-6 text-right text-[32px] font-extrabold leading-none md:text-5xl">اختر مسارك</h1>
        <p class="mt-3 text-right text-[14px] leading-7 text-[#8A7280]">مساران واضحان، لكل مسار خط عمليات، وتقييم في النهاية على الجودة والسعر والتعامل.</p>

        <div class="mt-4 grid gap-3 sm:grid-cols-3">
            <a href="{{ route('login', ['as' => 'worker']) }}" class="rounded-2xl bg-white px-4 py-3 text-center text-[13px] font-extrabold text-[#3E5C45]">دخول المنفذة</a>
            <a href="{{ route('login', ['as' => 'seller']) }}" class="rounded-2xl bg-white px-4 py-3 text-center text-[13px] font-extrabold text-[#5C3A4A]">دخول بائعة الخدمة</a>
            <a href="{{ route('login', ['as' => 'admin']) }}" class="rounded-2xl bg-[#5C3A4A] px-4 py-3 text-center text-[13px] font-extrabold text-white">دخول الإدارة</a>
        </div>

        <div class="mt-5 grid gap-4 lg:grid-cols-2">
        <a href="{{ route('login', ['as' => 'worker']) }}" class="block rounded-[28px] border border-[#D7E8D2] bg-[#F3F8F1] p-4 md:p-6">
            <div class="flex items-start justify-between">
                <span class="grid h-12 w-12 place-items-center rounded-2xl bg-white text-[#3D5A34]">
                    <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="8"/><path d="M12 8v5l3 2"/></svg>
                </span>
                <p class="text-[13px] font-bold text-[#4C6A44]">معايا وقت</p>
            </div>
            <h2 class="mt-3 text-right text-[26px] font-extrabold leading-snug">معايا وقت عاوزة اشتغل</h2>
            <p class="mt-2 text-right text-[13px] leading-7 text-[#4C6A44]">استلمي مهمة، وامشي خطوات الـ SOP واحدة تلو الأخرى، واستخدمي المهارة في موضعها حتى التسليم.</p>
            <div class="mt-3 flex flex-wrap justify-end gap-1.5">
                @foreach ($workerStages as $index => $stage)
                    <span class="rounded-full bg-white/80 px-2.5 py-1 text-[11px] font-bold text-[#3D5A34]">{{ $index + 1 }}. {{ $stage }}</span>
                @endforeach
            </div>
            <span class="mt-4 flex h-14 items-center justify-center gap-2 rounded-2xl bg-[#3E5C45] text-[16px] font-extrabold text-[#F4FBF2]">ادخلي لوحة العمل ←</span>
        </a>

        <a href="{{ route('login', ['as' => 'seller']) }}" class="block overflow-hidden rounded-[28px] bg-[#5C3A4A] p-4 text-[#FBF6F1] md:p-6">
            <div class="flex items-start justify-between">
                <span class="grid h-12 w-12 place-items-center rounded-2xl bg-white/10">
                    <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M8 4h8v16H8z"/><path d="M10 8h4M10 12h4M10 16h2"/></svg>
                </span>
                <p class="text-[13px] font-bold text-[#F0D7E4]">أبيع خدمة</p>
            </div>
            <h2 class="mt-3 text-right text-[26px] font-extrabold leading-snug">أبيع خدمة على المنصة</h2>
            <p class="mt-2 text-right text-[13px] leading-7 text-[#F3E4EC]">انشري الخدمة والسعر، وتابعي الطلبات اللي وصلت لها، من غير ما تختلطي بلوحة المنفذة.</p>
            <span class="mt-4 flex h-14 items-center justify-center rounded-2xl bg-[#E2B15A] text-[16px] font-extrabold text-[#3E2A22]">ادخلي لوحة البيع ←</span>
        </a>
        </div>

        @if ($resume)
            <a href="{{ route('client.orders.show', $resume) }}" class="mt-3 block rounded-2xl bg-white px-4 py-3 text-center text-[13px] font-extrabold text-[#5C4454]">تابعي الطلب #{{ $resume->number }}</a>
        @endif

        <a href="{{ route('client.request') }}" class="mt-3 block rounded-2xl bg-white px-4 py-3 text-center text-[13px] font-extrabold text-[#5C4454]">عندك طلب؟ اطلبي خدمة</a>

        <a href="{{ route('login', ['as' => 'admin']) }}" class="mt-4 flex items-center justify-between rounded-[24px] bg-white px-4 py-4 shadow-sm">
            <span class="text-[18px] text-[#C7B7C0]">←</span>
            <span class="text-right">
                <span class="block text-[15px] font-extrabold">إداريات المنصة</span>
                <span class="mt-1 block text-[12px] text-[#8A7280]">تشوفي المنفذات والبائعات والطلبات</span>
            </span>
        </a>
        <p class="mt-4 text-center text-[12px] text-[#A9939F]">كل البيانات هنا تجريبية — لا توجد مدفوعات حقيقية.</p>
    </main>
@endsection
