@extends('layouts.app')

@section('plain')
@endsection

@section('content')
    <header class="flex items-center justify-between px-4 pt-6 sm:px-6 lg:px-8">
        <a href="{{ route('seller.home') }}" class="rounded-full bg-white px-4 py-2 text-[13px] font-extrabold">رجوع</a>
        <h1 class="text-[24px] font-extrabold">{{ $service->name }}</h1>
    </header>

    <div class="mx-4 mt-4 grid items-start gap-4 sm:mx-6 lg:mx-8 lg:grid-cols-2">
        <section class="rounded-[24px] bg-white p-4 md:p-6">
            <h2 class="text-right text-[18px] font-extrabold">تعديل الخدمة</h2>
            <p class="mt-1 text-right text-[12px] text-[#8A7280]">{{ $service->orders_count }} طلب مرتبط بالخدمة دي.</p>
            <form method="POST" action="{{ route('seller.gigs.edit', $service) }}" class="mt-3 space-y-2">
                @csrf
                @method('PUT')
                <input name="name" value="{{ old('name', $service->name) }}" required class="h-11 w-full rounded-2xl border border-black/10 px-3">
                <textarea name="sample" required class="w-full rounded-2xl border border-black/10 p-3">{{ old('sample', $service->sample) }}</textarea>
                <div class="grid grid-cols-3 gap-2">
                    <input name="price" type="number" min="50" value="{{ old('price', $service->price) }}" required class="h-11 rounded-2xl border border-black/10 px-3">
                    <input name="duration" value="{{ old('duration', $service->duration) }}" required class="h-11 rounded-2xl border border-black/10 px-3">
                    <input name="min_level" type="number" min="1" max="5" value="{{ old('min_level', $service->min_level) }}" required class="h-11 rounded-2xl border border-black/10 px-3">
                </div>
                <button class="h-11 w-full rounded-2xl bg-[#5C3A4A] font-extrabold text-white">حفظ</button>
            </form>
            <form method="POST" action="{{ route('seller.gigs.destroy', $service) }}" class="mt-3" onsubmit="return confirm('حذف الخدمة؟')">
                @csrf
                @method('DELETE')
                <button class="h-11 w-full rounded-2xl bg-[#F8E2E2] font-extrabold text-[#7A3040]">حذف الخدمة</button>
            </form>
        </section>

        <section>
            <h2 class="text-right text-[18px] font-extrabold">طلبات الخدمة</h2>
            <div class="mt-3 space-y-2">
                @forelse ($orders as $order)
                    <a href="{{ route('seller.orders.show', $order) }}" class="block rounded-2xl bg-white p-4 text-right">
                        <p class="font-extrabold">#{{ $order->number }} · {{ number_format($order->total) }} ج</p>
                        <p class="mt-1 text-[12px] text-[#8A7280]">{{ $order->client_name }} · {{ $order->statusLabel() }}</p>
                    </a>
                @empty
                    <p class="rounded-2xl bg-white p-4 text-[13px] text-[#8A7280]">لسه مفيش طلب على الخدمة دي، تقدري تحذفيها.</p>
                @endforelse
            </div>
        </section>
    </div>
@endsection
