@extends('layouts.app')

@section('plain')
@endsection

@section('content')
    <header class="px-4 pt-6 text-right sm:px-6 lg:px-8">
        <h1 class="text-[28px] font-extrabold">لوحة الإدارة</h1>
        <p class="text-[13px] text-[#8A7280]">المحصّل، المنفذات، والخدمات في مكان واحد.</p>
    </header>

    <section class="mx-4 mt-4 grid grid-cols-2 gap-2 sm:mx-6 sm:grid-cols-4 lg:mx-8">
        <article class="tile bg-[#5C4454] text-[#FDF6EE]">
            <p class="text-[11px]">إجمالي المحصّل</p>
            <p class="num text-[20px] font-extrabold">{{ number_format($collected) }}</p>
            <p class="text-[11px] text-[#E8DCE2]">جنيه</p>
        </article>
        <article class="tile bg-[#F8E9B6]">
            <p class="text-[11px]">نصيب المنصة</p>
            <p class="num text-[20px] font-extrabold">{{ number_format($platform) }}</p>
            <p class="text-[11px]">{{ \App\Support\Split::percent('platform') }}% من الأساس. المتابعة {{ number_format($trackingIncome) }} ج خارج النسبة</p>
        </article>
        <article class="tile">
            <p class="text-[11px]">متوسط التقييم</p>
            <p class="num text-[20px] font-extrabold">{{ $average }}</p>
            <p class="text-[11px] text-[#8A7280]">{{ $ratings->count() }} تقييم</p>
        </article>
        <article class="tile">
            <p class="text-[11px]">طلبات شغالة</p>
            <p class="num text-[20px] font-extrabold">{{ $active }}</p>
        </article>
    </section>

    <section class="mx-4 mt-5 sm:mx-6 lg:mx-8">
        <div>
            <h2 class="text-right text-[18px] font-extrabold">المنفذات · عايزات يشتغلوا</h2>
            <div class="mt-3 max-h-80 space-y-2 overflow-y-auto pe-1">
                @foreach ($workers as $person)
                    <article class="sheet flex items-center justify-between gap-3 px-4 py-3">
                        <span class="shrink-0 rounded-full px-2.5 py-1 text-[11px] font-extrabold {{ $person->is_available ? 'bg-[#DCEFD6] text-[#2F4529]' : 'bg-[#F6EDE4] text-[#8A7280]' }}">{{ $person->is_available ? 'متاحة' : 'غير متاحة' }}</span>
                        <div class="text-right">
                            <p class="text-[14px] font-extrabold">{{ $person->name }}</p>
                            <p class="text-[12px] text-[#8A7280]">{{ $person->email }} · مستوى {{ $person->level }} {{ $person->level_name }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <nav class="sticky top-[68px] z-20 mx-4 mt-4 flex gap-2 overflow-x-auto bg-[#FBF6F1]/95 py-2 text-[12px] font-extrabold text-[#5C4454] backdrop-blur sm:mx-6 lg:mx-8">
        <a href="#services" class="rounded-full bg-white px-3 py-1">الخدمات</a>
        <a href="#payments" class="rounded-full bg-white px-3 py-1">المدفوعات</a>
        <a href="#ratings" class="rounded-full bg-white px-3 py-1">التقييمات</a>
        <a href="#skills" class="rounded-full bg-white px-3 py-1">المهارات</a>
        <a href="#help" class="rounded-full bg-white px-3 py-1">المساعدة</a>
    </nav>

    <section id="services" class="mx-4 mt-5 scroll-mt-4 sm:mx-6 lg:mx-8">
        <h2 class="text-right text-[18px] font-extrabold">الخدمات والأسعار</h2>
        <p class="text-right text-[12px] text-[#8A7280]">تُعدَّل من هنا فقط · العملاء يرون الخدمات المتاحة فقط</p>
        <div class="mt-3 grid gap-2 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($services as $service)
                <article class="rounded-2xl bg-white p-3">
                    <div class="flex items-start justify-between gap-2">
                        <span class="num text-[13px] font-extrabold">{{ number_format($service->price) }} ج</span>
                        <div class="text-right">
                            <p class="text-[14px] font-extrabold">{{ $service->name }}</p>
                            <p class="text-[11px] text-[#8A7280]">{{ $service->available ? 'ظاهرة للعملاء' : 'مخفية عن العملاء' }}</p>
                        </div>
                    </div>
                    <div class="mt-2 flex gap-2">
                        <form method="POST" action="{{ route('admin.services.update', $service) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="available" value="{{ $service->available ? 0 : 1 }}">
                            <button class="rounded-full bg-[#F6F0E8] px-3 py-1 text-[11px] font-extrabold">{{ $service->available ? 'إخفاء عن العملاء' : 'إظهار للعملاء' }}</button>
                        </form>
                        <form method="POST" action="{{ route('admin.services.destroy', $service) }}" onsubmit="return confirm('حذف الخدمة؟')">
                            @csrf
                            @method('DELETE')
                            <button class="rounded-full bg-[#F8E2E2] px-3 py-1 text-[11px] font-extrabold text-[#7A3040]">حذف الخدمة</button>
                        </form>
                    </div>
                </article>
            @endforeach
        </div>
        <form method="POST" action="{{ route('admin.services.store') }}" class="mt-3 space-y-2 rounded-[24px] bg-white p-4">
            @csrf
            <p class="text-right text-[14px] font-extrabold">إضافة خدمة جديدة</p>
            <input name="name" required placeholder="اسم الخدمة" class="h-11 w-full rounded-2xl border border-black/10 px-3">
            <input name="sample" required placeholder="وصف قصير يظهر للعميل" class="h-11 w-full rounded-2xl border border-black/10 px-3">
            <div class="grid grid-cols-3 gap-2">
                <input name="price" type="number" min="50" required placeholder="السعر" class="h-11 rounded-2xl border border-black/10 px-2">
                <input name="xp" type="number" min="10" required placeholder="XP" class="h-11 rounded-2xl border border-black/10 px-2">
                <input name="min_level" type="number" min="1" max="5" value="1" required class="h-11 rounded-2xl border border-black/10 px-2">
            </div>
            <input name="duration" required placeholder="المدة، مثال: 30 دقيقة" class="h-11 w-full rounded-2xl border border-black/10 px-3">
            <button class="h-11 w-full rounded-2xl bg-[#5C4454] font-extrabold text-white">إضافة</button>
        </form>
    </section>

    <section id="payments" class="mx-4 mt-6 scroll-mt-4 sm:mx-6 lg:mx-8">
        <h2 class="text-right text-[18px] font-extrabold">المدفوعات</h2>
        <div class="mt-3 grid gap-2 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($orders as $order)
                <details class="rounded-2xl bg-white p-3">
                    <summary class="cursor-pointer text-right">
                        <span class="block text-[14px] font-extrabold">#{{ $order->number }} · {{ $order->title }}</span>
                        <span class="text-[12px] text-[#8A7280]">{{ $order->client_name }} · {{ $order->worker?->name ?? 'بدون منفذة' }} · {{ number_format($order->total) }} ج · {{ $order->statusLabel() }}</span>
                    </summary>
                    <div class="mt-2 space-y-1 text-[12px]">
                        @foreach ($order->shares() as $share)
                            @continue($share['id'] === 'author')
                            <div class="flex justify-between"><span class="num">{{ number_format($share['amount']) }} ج</span><span>{{ $share['label'] }}</span></div>
                        @endforeach
                        @if ($order->tracking_fee > 0)
                            <div class="flex justify-between font-extrabold"><span class="num">{{ number_format($order->tracking_fee) }} ج</span><span>متابعة تفصيلية: مبلغ للمنصة، خارج توزيع الأربعة</span></div>
                        @endif
                        <p class="text-[#8A7280]">{{ $order->rating ? 'التقييم '.$order->rating->average().' · '.$order->rating->note : 'لا يوجد تقييم بعد — يظهر بعد التسليم.' }}</p>
                    </div>
                </details>
            @endforeach
        </div>
    </section>

    <section id="ratings" class="mx-4 mt-6 scroll-mt-4 sm:mx-6 lg:mx-8">
        <h2 class="text-right text-[18px] font-extrabold">كل التقييمات</h2>
        <div class="mt-3 grid gap-2 md:grid-cols-2 xl:grid-cols-3">
            @forelse ($ratings as $rating)
                <article class="rounded-2xl bg-white p-3 text-right">
                    <p class="text-[13px] font-extrabold">{{ $rating->author_name }} · {{ $rating->average() }} من 5</p>
                    <p class="text-[12px] text-[#8A7280]">{{ $rating->order->title }} · {{ $rating->created_at->locale('ar')->translatedFormat('F Y') }}</p>
                    <p class="mt-1 text-[12px] leading-6">{{ $rating->note }}</p>
                </article>
            @empty
                <p class="text-[13px] text-[#8A7280]">لا توجد تقييمات بعد.</p>
            @endforelse
        </div>
    </section>
    <section id="skills" class="mx-4 mt-6 scroll-mt-4 sm:mx-6 lg:mx-8">
        <h2 class="text-right text-[18px] font-extrabold">المهارات</h2>
        <div class="mt-3 grid gap-2 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($skills as $skill)
                <article class="rounded-2xl bg-white p-3 text-right">
                    <p class="text-[14px] font-extrabold">{{ $skill->name }}</p>
                    <p class="text-[12px] text-[#8A7280]">{{ $skill->user->name }} · {{ $skill->status }}</p>
                    @if ($skill->status === 'pending')
                        <div class="mt-2 flex gap-2">
                            <form method="POST" action="{{ route('admin.skills.review', $skill) }}">@csrf<input type="hidden" name="decision" value="approved"><button class="rounded-full bg-[#DCEFD6] px-3 py-1 text-[11px] font-extrabold">اعتماد</button></form>
                            <form method="POST" action="{{ route('admin.skills.review', $skill) }}">@csrf<input type="hidden" name="decision" value="rejected"><button class="rounded-full bg-[#F8E2E2] px-3 py-1 text-[11px] font-extrabold text-[#7A3040]">رفض</button></form>
                        </div>
                    @endif
                </article>
            @endforeach
        </div>
        @unless ($isAdmin)
            <p class="mt-2 text-right text-[12px] text-[#8A7280]">ادخلي كإدارة من الصفحة الرئيسية عشان الاعتماد يتحفظ.</p>
        @endunless
    </section>

    <section id="help" class="mx-4 mt-6 scroll-mt-4 sm:mx-6 lg:mx-8">
        <h2 class="text-right text-[18px] font-extrabold">المساعدة</h2>
        <div class="mt-3 grid gap-2 md:grid-cols-2 xl:grid-cols-3">
            @forelse ($tickets as $ticket)
                <article class="rounded-2xl bg-white p-3 text-right">
                    <p class="text-[14px] font-extrabold">{{ $ticket->topic }}</p>
                    <p class="text-[12px] leading-6 text-[#6B4F60]">{{ $ticket->body }}</p>
                    <p class="text-[11px] text-[#8A7280]">{{ $ticket->user?->name }} · {{ $ticket->status === 'closed' ? 'مقفول' : 'مفتوح' }}</p>
                    @if ($ticket->status !== 'closed')
                        <form method="POST" action="{{ route('admin.tickets.close', $ticket) }}" class="mt-2">
                            @csrf
                            <button class="rounded-full bg-[#F6F0E8] px-3 py-1 text-[11px] font-extrabold">إقفال</button>
                        </form>
                    @endif
                </article>
            @empty
                <p class="text-[13px] text-[#8A7280]">لا توجد طلبات مساعدة.</p>
            @endforelse
        </div>
    </section>
    <p class="mx-4 mt-4 text-right text-[11px] leading-6 text-[#8A7280] sm:mx-6 lg:mx-8">الأرقام من النموذج التجريبي. التحصيل يتم على المنصة، ويُحرَّر التحويل بعد استلام العميل.</p>
@endsection
