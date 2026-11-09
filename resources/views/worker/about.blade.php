@extends('layouts.app')

@section('content')
    <header class="px-4 pt-6 text-right sm:px-6 lg:px-8">
        <p class="text-[12px] text-[#8A7280]">الصفحة التعريفية</p>
        <h1 class="text-[28px] font-extrabold">كيف تعمل المنصة؟</h1>
    </header>
    <div class="mx-4 mt-4 grid gap-3 sm:mx-6 md:grid-cols-2 lg:mx-8">
        @foreach ([
            ['1', 'الطلب', 'العميل يختار الخدمة ويدفع. المبلغ يتحجز لدى المنصة حتى التسليم.'],
            ['2', 'التنفيذ', 'المنفذة تمشي خطوات الـ SOP، والمهارة تشتغل في موضعها داخل المنصة.'],
            ['3', 'المراجعة', 'البارتنر يراجع الجودة قبل ما يوصل العمل للعميل.'],
            ['4', 'التقييم والتوزيع', 'العميل يقيّم الجودة والسعر والتعامل، وبعد الموافقة يتحرر نصيب المنفذة.'],
        ] as [$n, $title, $body])
            <article class="sheet p-4 text-right">
                <p class="text-[12px] font-extrabold text-[#8A7280]">{{ $n }}</p>
                <h2 class="mt-1 text-[18px] font-extrabold">{{ $title }}</h2>
                <p class="mt-2 text-[13px] leading-7 text-[#6B4F60]">{{ $body }}</p>
            </article>
        @endforeach
    </div>
    <p class="mx-4 mt-4 text-right text-[12px] leading-6 text-[#8A7280] sm:mx-6 lg:mx-8">منصة عمل للسيدات. كل البيانات هنا تجريبية — لا توجد مدفوعات حقيقية. الدعم {{ config('hadthiqa.support_phone') }}.</p>
@endsection
