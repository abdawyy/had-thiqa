@extends('layouts.app')

@section('content')
    <header class="flex items-start justify-between px-4 pt-5 sm:px-6 lg:px-8">
        <button type="button" onclick="document.getElementById('help').showModal()" class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-2 text-[12px] font-extrabold shadow-sm">
            طلب مساعدة
        </button>
        <div class="flex items-center gap-2">
            <div class="text-right">
                <p class="text-[16px] font-extrabold leading-none">لوحة المنفذة</p>
                <p class="mt-1 text-[11px] text-[#8A7280]">متابعة المهام والأرباح</p>
            </div>
            <span class="grid h-12 w-12 place-items-center rounded-full bg-[#5C4454] text-[18px] font-extrabold text-white">{{ $worker->letter }}</span>
        </div>
    </header>

    <div class="mt-4 grid items-start gap-4 px-4 sm:px-6 lg:grid-cols-2 lg:px-8">
    <section class="rounded-[28px] bg-white p-4 shadow-sm md:p-6">
        <h1 class="text-right text-[28px] font-extrabold md:text-4xl">أهلاً {{ $worker->name }}</h1>
        <p class="mt-1 text-right text-[12.5px] leading-6 text-[#8A7280]">هذه حالة حسابك اليوم: الرصيد والمستوى والمبلغ الجاهز للسحب.</p>
        <div class="mt-4 grid grid-cols-3 gap-2">
            <div class="rounded-2xl bg-[#DCEFD6] px-2 py-3 text-center">
                <p class="text-[10px] text-[#4C6A44]">جاهز للسحب</p>
                <p class="num text-[18px] font-extrabold">{{ number_format($worker->withdrawable) }}</p>
                <p class="text-[10px] text-[#4C6A44]">جنيه</p>
            </div>
            <div class="rounded-2xl bg-[#E9E4F5] px-2 py-3 text-center">
                <p class="text-[10px] text-[#5B4A78]">المستوى</p>
                <p class="num text-[18px] font-extrabold">{{ $worker->level }}</p>
                <p class="text-[10px] text-[#5B4A78]">{{ $worker->level_name }}</p>
            </div>
            <div class="rounded-2xl bg-[#F6EDE4] px-2 py-3 text-center">
                <p class="text-[10px] text-[#8A7280]">رصيد الحساب</p>
                <p class="num text-[18px] font-extrabold">{{ number_format($worker->balance) }}</p>
                <p class="text-[10px] text-[#A9939F]">جنيه</p>
            </div>
        </div>
        <div class="mt-4 flex items-center justify-between text-[11px] text-[#8A7280]">
            <span>المستوى التالي</span>
            <span class="num">{{ number_format($worker->points) }} نقطة · متبقٍ {{ $progress['left'] }} XP</span>
        </div>
        <div class="mt-2 h-2 overflow-hidden rounded-full bg-[#EFE7F0]">
            <div class="h-full rounded-full bg-[#5C4454]" style="width: {{ $progress['percent'] }}%"></div>
        </div>
        <div class="mt-3 flex flex-wrap justify-end gap-1.5">
            @if ($worker->speed_badge)
                <span class="rounded-full bg-[#DCEFD6] px-2.5 py-1 text-[11px] font-extrabold text-[#2F4529]">شارة السرعة</span>
            @endif
            <span class="rounded-full bg-[#E7F3E4] px-2.5 py-1 text-[11px] font-extrabold text-[#3D5A34]">{{ $worker->jobs_clean }} مهام بدون تعديل</span>
            <span class="rounded-full bg-[#E9E4F5] px-2.5 py-1 text-[11px] font-extrabold text-[#5B4A78]">{{ $worker->streak }} مهام متتالية</span>
        </div>
    </section>

    <section class="rounded-[28px] bg-white p-4 shadow-sm md:p-6">
        <div class="flex items-center justify-between">
            <span class="rounded-full px-2.5 py-1 text-[11px] font-extrabold {{ $worker->is_available ? 'bg-[#DCEFD6] text-[#2F4529]' : 'bg-[#F6EDE4] text-[#8A7280]' }}">
                {{ $worker->is_available ? 'متاحة للعمل' : 'غير متاحة' }}
            </span>
            <h2 class="text-[16px] font-extrabold">متاحة للعمل اليوم</h2>
        </div>
        <form method="POST" action="{{ route('worker.availability') }}" class="mt-3">
            @csrf
            <input type="hidden" name="available" value="{{ $worker->is_available ? 0 : 1 }}">
            <button class="primary-action h-14 w-full rounded-2xl text-[15px] font-extrabold {{ $worker->is_available ? 'bg-[#5C4454] text-white' : 'border border-[#E7E0D8] bg-white text-[#5C4454]' }}">
                {{ $worker->is_available ? 'وقفي الإتاحة' : 'فعّلي الإتاحة' }}
            </button>
        </form>
        <p class="mt-3 text-right text-[12px] leading-6 text-[#8A7280]">
            {{ $worker->is_available ? 'الطلبات المتاحة لك ظاهرة أدناه مع السعر والمدة وموعد التسليم.' : 'لن يصلك أي طلب اليوم. رابط الدعوة يبقى فعّالاً.' }}
        </p>
    </section>
    </div>

    <section class="mx-4 mt-6 sm:mx-6 lg:mx-8">
        <h2 class="text-right text-[22px] font-extrabold underline decoration-[#E7C56A] decoration-4 underline-offset-8">المهام المتاحة اليوم</h2>
        <div class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
            @if (! $worker->is_available)
                <p class="rounded-2xl bg-white p-4 text-center text-[13px] leading-7 text-[#8A7280]">لا توجد مهام اليوم. فعّلي الإتاحة عشان الطلبات تظهر.</p>
            @else
            @forelse ($offers as $offer)
                @php $locked = $offer->lockedFor($worker); @endphp
                <form method="POST" action="{{ $locked ? '#' : route('worker.claim', $offer) }}" class="rounded-[24px] bg-white p-4 shadow-sm {{ $locked ? 'opacity-70' : '' }}">
                    @csrf
                    <div class="text-right">
                        <p class="text-[15px] font-extrabold">{{ $offer->title }}</p>
                        <p class="num mt-1 text-[22px] font-extrabold text-[#3E5C45]">نصيبك {{ number_format($offer->workerShare()) }} ج</p>
                        <p class="mt-1 text-[12px] text-[#8A7280]">سعر الخدمة {{ number_format($offer->service->price) }} جنيه · {{ $offer->meta }}</p>
                    </div>
                    <div class="mt-3 flex flex-wrap justify-end gap-1.5 text-[11px] font-bold">
                        <span class="rounded-full bg-[#F6EDE4] px-2 py-1 text-[#6B4F60]">{{ $offer->due_label }}</span>
                        <span class="rounded-full bg-[#DCEFD6] px-2 py-1 text-[#3D5A34]">{{ $offer->duration }}</span>
                        @if ($offer->lockedFor($worker))
                            <span class="rounded-full bg-[#EFE7F0] px-2 py-1 text-[#8A7280]">مقفولة · تفتح عند مستوى {{ $offer->min_level }}</span>
                        @else
                            <span class="rounded-full bg-[#F6DE9E] px-2 py-1 text-[#5A4A22]">XP {{ $offer->xp }}+</span>
                        @endif
                    </div>
                    <button @disabled($locked) class="primary-action mt-3 h-12 w-full rounded-2xl text-[14px] font-extrabold {{ $locked ? 'cursor-not-allowed bg-[#EFE7F0] text-[#8A7280]' : 'bg-[#5C3A4A] text-white' }}">
                        {{ $locked ? 'تفتح عند مستوى '.$offer->min_level : 'استلمي المهمة' }}
                    </button>
                </form>
            @empty
                <p class="rounded-2xl bg-white p-4 text-center text-[13px] text-[#8A7280]">لا توجد مهام اليوم.</p>
            @endforelse
            @endif
        </div>
        <p class="mt-3 text-right text-[12px] leading-6 text-[#8A7280]">خط العمليات يبدأ بعد استلام المهمة، وتتابعينه من تبويب «المشاريع».</p>
    </section>

    <section class="mx-4 mb-6 mt-4 rounded-[24px] bg-[#5C4454] p-4 text-[#FDF6EE] sm:mx-6 md:p-6 lg:mx-8">
        <p class="text-[15px] font-extrabold">رابط دعوة بعمولة</p>
        <p class="mt-1 text-[12px] leading-6 text-[#F7E7EF]">الكود {{ $worker->referral_code }} يخصم 10% لعميلك. عمولتك تتسجل على الطلب.</p>
        <button type="button" onclick="navigator.clipboard.writeText('{{ route('client.request', ['code' => $worker->referral_code]) }}'); this.textContent='تم النسخ'" class="mt-3 h-11 rounded-2xl bg-white/15 px-4 text-[13px] font-extrabold">نسخ الرابط</button>
    </section>

    <dialog id="help" class="w-[min(420px,92vw)] rounded-3xl bg-[#F6F0E8] p-0 text-[#3F2C39] backdrop:bg-black/40">
        <form method="POST" action="{{ route('worker.help') }}" class="p-5">
            @csrf
            <div class="flex items-center justify-between">
                <button type="button" onclick="document.getElementById('help').close()" class="text-[13px] font-bold text-[#8A7280]">إغلاق</button>
                <h3 class="text-[16px] font-extrabold">طلب مساعدة</h3>
            </div>
            <label class="mt-4 block text-right text-[12px] font-bold">الموضوع
                <select name="topic" class="mt-1 h-12 w-full rounded-2xl border border-black/10 bg-white px-3">
                    @foreach (config('hadthiqa.help_topics') as $topic)
                        <option>{{ $topic }}</option>
                    @endforeach
                </select>
            </label>
            <label class="mt-3 block text-right text-[12px] font-bold">التفاصيل
                <textarea name="body" required rows="4" placeholder="اكتبي تفاصيل المشكلة أو السؤال" class="mt-1 w-full rounded-2xl border border-black/10 bg-white p-3"></textarea>
            </label>
            <button class="mt-3 h-12 w-full rounded-2xl bg-[#5C4454] font-extrabold text-white">إرسال الطلب</button>
            <p class="mt-2 text-center text-[11px] text-[#8A7280]">فريق الدعم · {{ config('hadthiqa.support_phone') }} · رقم تجريبي</p>
        </form>
    </dialog>
@endsection
