@extends('layouts.app')

@section('content')
    <header class="px-4 pt-6 text-right sm:px-6 lg:px-8">
        <p class="text-[12px] font-bold text-[#8A7280]">{{ $worker->name }}</p>
        <h1 class="text-[28px] font-extrabold">مواهبي</h1>
        <p class="mt-2 text-[13px] leading-7 text-[#6B4F60]">أضيفي مهاراتك هنا. تظهر للعملاء في الترشيحات المهاراتُ المعتمدة فقط.</p>
    </header>
    <form method="POST" action="{{ route('worker.skills.store') }}" class="mx-4 mt-4 flex gap-2 sm:mx-6 lg:mx-8 lg:max-w-xl">
        @csrf
        <button class="h-12 rounded-2xl bg-[#5C4454] px-4 text-[14px] font-extrabold text-white">إضافة</button>
        <input name="name" placeholder="مثال: تصميم إضاءة داخلية" class="h-12 flex-1 rounded-2xl border border-black/10 bg-white px-3 text-[14px]">
    </form>
    <p class="mx-4 mt-2 text-right text-[12px] text-[#8A7280] sm:mx-6 lg:mx-8">المهارة الجديدة تُراجَع أولاً، ثم تُعتمد وتظهر للعملاء.</p>
    <div class="mx-4 mt-4 grid gap-3 sm:mx-6 md:grid-cols-2 xl:grid-cols-3 lg:mx-8">
        @foreach ($skills as $skill)
            <article class="rounded-[24px] bg-white p-4">
                <div class="flex items-center justify-between">
                    <span class="rounded-full px-2.5 py-1 text-[11px] font-extrabold {{ $skill->approved() ? 'bg-[#DCEFD6] text-[#2F4529]' : ($skill->status === 'rejected' ? 'bg-[#F8E2E2] text-[#7A3040]' : 'bg-[#F8E9B6] text-[#5A4A22]') }}">
                        {{ $skill->approved() ? 'معتمدة · تظهر للعملاء' : ($skill->status === 'rejected' ? 'مرفوضة' : 'قيد الاعتماد · لا تظهر للعملاء') }}
                    </span>
                    <h2 class="text-[15px] font-extrabold">{{ $skill->name }}</h2>
                </div>
                <p class="mt-2 text-right text-[12px] text-[#8A7280]">{{ $skill->level_label }}</p>
                <div class="mt-2 h-2 overflow-hidden rounded-full bg-[#EFE7F0]">
                    <div class="h-full rounded-full bg-[#5C4454]" style="width: {{ $skill->progress }}%"></div>
                </div>
            </article>
        @endforeach
    </div>
    <p class="mx-4 mt-4 text-right text-[13px] font-bold sm:mx-6 lg:mx-8">المعروض للعملاء الآن: {{ $skills->where('status', 'approved')->count() }} مهارة معتمدة.</p>
@endsection
