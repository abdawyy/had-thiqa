@extends('layouts.app')

@section('content')
    <header class="px-4 pt-6 text-right sm:px-6 lg:px-8">
        <h1 class="text-[28px] font-extrabold">المشاريع</h1>
        <p class="mt-2 text-[13px] leading-7 text-[#6B4F60]">كل مشروع مستلم من المنصة، ومرحلته الحالية في خط العمليات.</p>
    </header>
    <div class="mx-4 mt-4 grid gap-3 sm:mx-6 md:grid-cols-2 xl:grid-cols-3 lg:mx-8">
        @forelse ($orders as $order)
            <a href="{{ route('worker.orders.show', $order) }}" class="block rounded-[24px] bg-white p-4 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <span class="rounded-full px-2.5 py-1 text-[11px] font-extrabold {{ $order->pipeline_stage >= 5 ? 'bg-[#DCEFD6] text-[#2F4529]' : 'bg-[#E9E4F5] text-[#5B4A78]' }}">{{ $order->statusLabel() }}</span>
                    <div class="text-right">
                        <h2 class="text-[15px] font-extrabold">{{ $order->title }}</h2>
                        <p class="mt-1 text-[12px] text-[#8A7280]">{{ $order->client_name }} · تسليم {{ $order->due_label }}</p>
                        <p class="mt-1 text-[12px] font-extrabold text-[#3E5C45]">نصيبك {{ number_format($order->workerShare()) }} جنيه{{ $order->payment_status === 'released' ? ' · اتضاف' : ' · لسه معلّق' }}</p>
                    </div>
                </div>
                <p class="mt-2 text-right text-[12px] font-bold text-[#5C4454]">المرحلة الحالية: {{ config('hadthiqa.worker_stages')[$order->pipeline_stage] }}</p>
            </a>
        @empty
            <p class="rounded-2xl bg-white p-4 text-center text-[13px] text-[#8A7280]">لا توجد مشاريع مستلمة حتى الآن.</p>
        @endforelse
    </div>
@endsection
