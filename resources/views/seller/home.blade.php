@extends('layouts.app')

@section('plain')
@endsection

@section('content')
    <header class="flex items-center justify-between px-4 pt-6 sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="rounded-full bg-white px-4 py-2 text-[13px] font-extrabold text-[#7A3040]">خروج</button>
        </form>
        <div class="text-right">
            <p class="text-[12px] text-[#8A7280]">لوحة البيع</p>
            <h1 class="text-[28px] font-extrabold">أهلاً {{ $seller->name }}</h1>
        </div>
    </header>

    <section class="mx-4 mt-4 grid grid-cols-2 gap-2 sm:mx-6 sm:grid-cols-4 lg:mx-8">
        <article class="rounded-2xl bg-white p-3 text-right">
            <p class="text-[11px] text-[#8A7280]">خدمات ظاهرة</p>
            <p class="num text-[22px] font-extrabold">{{ $stats['live'] }}</p>
            <p class="text-[11px] text-[#A9939F]">من {{ $stats['gigs'] }}</p>
        </article>
        <article class="rounded-2xl bg-[#E9E4F5] p-3 text-right">
            <p class="text-[11px]">طلبات شغالة</p>
            <p class="num text-[22px] font-extrabold">{{ $stats['open'] }}</p>
        </article>
        <article class="rounded-2xl bg-[#F3F8F1] p-3 text-right">
            <p class="text-[11px]">مكتملة</p>
            <p class="num text-[22px] font-extrabold">{{ $stats['done'] }}</p>
        </article>
        <article class="rounded-2xl bg-[#5C3A4A] p-3 text-right text-[#FBF6F1]">
            <p class="text-[11px] text-[#F0D7E4]">ربحك الجاهز {{ \App\Support\Split::percent('author') }}%</p>
            <p class="num text-[22px] font-extrabold">{{ number_format($stats['share']) }}</p>
            <p class="text-[11px] text-[#E8DCE2]">معلّق {{ number_format($stats['pending']) }} · العميل يدفع مش أنتِ</p>
        </article>
    </section>

    <div class="mx-4 mt-4 grid items-start gap-4 sm:mx-6 lg:mx-8 lg:grid-cols-2">
        <section class="rounded-[24px] bg-white p-4 md:p-6">
            <h2 class="text-right text-[18px] font-extrabold">انشري خدمة</h2>
            <p class="mt-1 text-[12px] leading-6 text-[#8A7280]">السعر ده اللي العميل بيدفعه للمنصة. أنتِ مش بتدفعي حاجة. بعد التقييم بتكسبي {{ \App\Support\Split::percent('author') }}%، والمنصة تخصم {{ \App\Support\Split::percent('platform') }}%.</p>
            <form method="POST" action="{{ route('seller.gigs.store') }}" class="mt-3 space-y-2">
                @csrf
                <input name="name" value="{{ old('name') }}" required placeholder="اسم الخدمة" class="h-11 w-full rounded-2xl border border-black/10 px-3">
                <textarea name="sample" required placeholder="وصف قصير يظهر للعميل" class="w-full rounded-2xl border border-black/10 p-3">{{ old('sample') }}</textarea>
                <div class="grid grid-cols-3 gap-2">
                    <input name="price" type="number" min="50" value="{{ old('price') }}" required placeholder="السعر" class="h-11 rounded-2xl border border-black/10 px-3">
                    <input name="duration" value="{{ old('duration') }}" required placeholder="30 دقيقة" class="h-11 rounded-2xl border border-black/10 px-3">
                    <input name="min_level" type="number" min="1" max="5" value="{{ old('min_level', 1) }}" required placeholder="مستوى" class="h-11 rounded-2xl border border-black/10 px-3">
                </div>
                <button class="h-11 w-full rounded-2xl bg-[#5C3A4A] font-extrabold text-white">نشر الخدمة</button>
            </form>
        </section>

        <section>
            <h2 class="text-right text-[18px] font-extrabold">خدماتي</h2>
            <form method="GET" action="{{ route('seller.home') }}" class="mt-3">
                <input type="hidden" name="status" value="{{ $filter }}">
                <input name="q" value="{{ $search }}" placeholder="ابحثي في خدماتك" class="h-11 w-full rounded-2xl border border-black/10 bg-white px-3">
            </form>
            <div class="mt-3 grid gap-2">
                @forelse ($gigs as $gig)
                    <article class="rounded-2xl bg-white p-4">
                        <div class="flex items-start justify-between gap-3">
                            <span class="num text-[14px] font-extrabold">{{ number_format($gig->price) }} ج</span>
                            <div class="text-right">
                                <p class="text-[15px] font-extrabold">{{ $gig->name }}</p>
                                <p class="text-[12px] text-[#8A7280]">{{ $gig->available ? 'ظاهرة للعملاء' : 'متوقفة' }} · {{ $gig->orders_count }} طلب · من مستوى {{ $gig->min_level }}</p>
                            </div>
                        </div>
                        <div class="mt-3 flex flex-wrap justify-end gap-2">
                            <a href="{{ route('seller.gigs.show', $gig) }}" class="rounded-full bg-[#5C3A4A] px-3 py-1 text-[11px] font-extrabold text-white">تعديل</a>
                            <form method="POST" action="{{ route('seller.gigs.duplicate', $gig) }}">
                                @csrf
                                <button class="rounded-full bg-[#E9E4F5] px-3 py-1 text-[11px] font-extrabold text-[#5C3A4A]">نسخة</button>
                            </form>
                            <form method="POST" action="{{ route('seller.gigs.update', $gig) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="available" value="{{ $gig->available ? 0 : 1 }}">
                                <button class="rounded-full bg-[#F6F0E8] px-3 py-1 text-[11px] font-extrabold">{{ $gig->available ? 'إيقاف' : 'نشر تاني' }}</button>
                            </form>
                        </div>
                    </article>
                @empty
                    <p class="rounded-2xl bg-white p-4 text-center text-[13px] text-[#8A7280]">لسه ما نشرتيش خدمة.</p>
                @endforelse
            </div>
        </section>
    </div>

    <section class="mx-4 mt-6 sm:mx-6 lg:mx-8">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div class="flex gap-2">
                @foreach (['all' => 'الكل', 'unpaid' => 'بانتظار الدفع', 'open' => 'شغالة', 'done' => 'مكتملة'] as $key => $label)
                    <a href="{{ route('seller.home', ['status' => $key]) }}" class="rounded-full px-3 py-1 text-[12px] font-extrabold {{ $filter === $key ? 'bg-[#5C3A4A] text-white' : 'bg-white text-[#5C3A4A]' }}">{{ $label }}</a>
                @endforeach
            </div>
            <h2 class="text-right text-[18px] font-extrabold">طلبات على خدماتي</h2>
        </div>
        <div class="mt-3 grid gap-2 md:grid-cols-2">
            @forelse ($orders as $order)
                <a href="{{ route('seller.orders.show', $order) }}" class="rounded-2xl bg-white p-4 text-right">
                    <p class="text-[14px] font-extrabold">#{{ $order->number }} · {{ $order->title }}</p>
                    <p class="mt-1 text-[12px] text-[#8A7280]">{{ $order->client_name }} · المنفذة {{ $order->worker?->name ?? 'لسه ما اتحددتش' }}</p>
                    <p class="mt-1 text-[12px] font-extrabold text-[#3E5C45]">ربحك {{ number_format(\App\Support\Split::amount($order->distributable(), 'author')) }} ج{{ $order->payment_status === 'released' ? ' · اتضاف' : ' · لسه معلّق' }}</p>
                    <p class="mt-1 text-[12px] font-bold text-[#5C3A4A]">{{ $order->statusLabel() }}@if ($order->rating) · تقييم {{ $order->rating->average() }}@endif</p>
                </a>
            @empty
                <p class="rounded-2xl bg-white p-4 text-[13px] text-[#8A7280]">ما فيش طلبات في الفلتر ده.</p>
            @endforelse
        </div>
    </section>
@endsection
