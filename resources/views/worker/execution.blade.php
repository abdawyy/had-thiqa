@extends('layouts.app')

@section('content')
    <header class="flex items-center justify-between px-4 pt-5 sm:px-6 lg:px-8">
        <a href="{{ route('worker.home') }}" class="rounded-full bg-white px-4 py-2 text-[13px] font-extrabold shadow-sm">رجوع</a>
        <h1 class="text-[22px] font-extrabold">مهمتك</h1>
    </header>

    <div class="mt-4 grid items-start gap-4 px-4 sm:px-6 lg:grid-cols-2 lg:px-8">
    <section class="rounded-[28px] bg-[#5C4454] p-4 text-[#FDF6EE] md:p-6">
        <h2 class="text-right text-[22px] font-extrabold">{{ $order->title }}</h2>
        <p class="mt-1 text-right text-[12px] text-[#F0D7E4]">{{ $order->client_name }} · {{ $order->description }}</p>
        <div class="mt-3 flex flex-wrap justify-end gap-1.5 text-[11px] font-extrabold">
            <span class="rounded-full bg-white/15 px-2.5 py-1">{{ $order->speedMeta()['note'] }}</span>
            <span class="rounded-full bg-[#E9E4F5] px-2.5 py-1 text-[#5B4A78]">نصيبك {{ number_format($order->workerShare()) }} ج</span>
            <span class="num rounded-full bg-[#F6DE9E] px-2.5 py-1 text-[#5A4A22]">XP +{{ $order->xp }}</span>
        </div>
        <div class="mt-4 flex items-center justify-between text-[11px]">
            <span class="num">{{ $order->stepsDone() }} من {{ $order->steps->count() }}</span>
            <span>خطوات الـ SOP</span>
        </div>
        <div class="mt-2 grid grid-cols-5 gap-1">
            @foreach ($order->steps as $step)
                <span class="h-1.5 rounded-full {{ $step->done() ? 'bg-[#B7E0A8]' : 'bg-white/25' }}"></span>
            @endforeach
        </div>
    </section>

    <section class="rounded-[24px] bg-white p-4 md:p-6">
        <div class="flex items-center justify-between text-[12px] font-bold text-[#8A7280]">
            <span class="num">{{ $order->pipeline_stage + 1 }} من 6</span>
            <span>خط عملياتك</span>
        </div>
        <div class="mt-3 flex items-start justify-between gap-1">
            @foreach (array_reverse($stages) as $index => $stage)
                @php $real = count($stages) - 1 - $index; @endphp
                <div class="flex flex-1 flex-col items-center gap-1 text-center">
                    <span class="grid h-7 w-7 place-items-center rounded-full text-[11px] font-extrabold {{ $real < $order->pipeline_stage ? 'bg-[#DCEFD6] text-[#2F4529]' : ($real === $order->pipeline_stage ? 'bg-[#5C4454] text-white' : 'bg-[#EFE7F0] text-[#8A7280]') }}">{{ $real + 1 }}</span>
                    <span class="text-[9px] font-bold leading-4 {{ $real === $order->pipeline_stage ? 'text-[#5C4454]' : 'text-[#A9939F]' }}">{{ $stage }}</span>
                </div>
            @endforeach
        </div>
    </section>

    </div>

    @if ($order->pipeline_stage === 0)
        <form method="POST" action="{{ route('worker.orders.begin', $order) }}" class="mx-4 mt-4 sm:mx-6 lg:mx-8">
            @csrf
            <button class="h-14 w-full rounded-2xl bg-[#5C3A4A] text-[16px] font-extrabold text-white">ابدئي المهمة</button>
        </form>
    @endif

    @if ($order->pipeline_stage === 1)
        @php $current = $order->currentStep(); @endphp
        <section class="mx-4 mt-4 sm:mx-6 lg:mx-8">
            <h2 class="text-right text-[20px] font-extrabold">خطوات العمل</h2>
            @if ($current)
                <article class="mt-3 rounded-[24px] bg-white p-4 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <span class="grid h-9 w-9 place-items-center rounded-full bg-[#5C4454] text-[13px] font-extrabold text-white">{{ $current->position }}</span>
                        <div class="text-right">
                            <h3 class="text-[16px] font-extrabold">{{ $current->title }}</h3>
                            <p class="mt-1 text-[12.5px] leading-6 text-[#6B4F60]">{{ $current->description }}</p>
                        </div>
                    </div>
                    @if ($current->kind === 'ai')
                        @if ($current->result)
                            <ul class="mt-3 space-y-1 text-right text-[12px] text-[#3D5A34]">
                                @foreach ($current->result as $line)
                                    <li>• {{ $line }}</li>
                                @endforeach
                            </ul>
                            <p class="mt-2 text-right text-[11px] leading-6 text-[#8A7280]">دي مسودة المهارة، مش تسليم نهائي.</p>
                            <form method="POST" action="{{ route('worker.orders.skill.confirm', [$order, $current]) }}">
                                @csrf
                                <button class="primary-action mt-3 h-12 w-full rounded-2xl bg-[#5C3A4A] text-[15px] font-extrabold text-white">أعتمد النتيجة</button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('worker.orders.skill', [$order, $current]) }}">
                                @csrf
                                <button class="primary-action mt-4 h-12 w-full rounded-2xl bg-[#E7F3E4] text-[15px] font-extrabold text-[#3E5C45]">{{ $current->cta ?: 'شغّلي المهارة' }}</button>
                            </form>
                        @endif
                    @else
                        <form method="POST" action="{{ route('worker.orders.step', [$order, $current]) }}">
                            @csrf
                            <textarea name="note" rows="2" placeholder="ملاحظة اختيارية على الخطوة" class="mt-3 w-full rounded-2xl border border-black/10 p-3 text-[13px]"></textarea>
                            <button class="primary-action mt-2 h-12 w-full rounded-2xl bg-[#E7F3E4] text-[15px] font-extrabold text-[#3E5C45]">أنهيت الخطوة</button>
                        </form>
                    @endif
                </article>
            @endif
            <div class="mt-3 space-y-2">
                @foreach ($order->steps as $step)
                    <div class="rounded-2xl bg-white/70 px-3 py-2 text-right text-[12px] {{ $step->done() ? 'text-[#3D5A34]' : 'text-[#8A7280]' }}">
                        {{ $step->position }}. {{ $step->done() ? $step->done_label : $step->title }}
                        @if ($step->done() && $step->result)
                            <ul class="mt-1 space-y-1 text-[11px]">
                                @foreach ($step->result as $line)
                                    <li>• {{ $line }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @endforeach
            </div>
            <p class="mt-3 rounded-2xl bg-[#F8E9B6] px-3 py-2 text-[11px] leading-6 text-[#5A4A22]">التسليم في الموعد يمنحك شارة السرعة و30 XP. المهارات تعمل داخل المنصة.</p>
        </section>
    @endif

    @if ($order->pipeline_stage === 2)
        <form method="POST" action="{{ route('worker.orders.deliver', $order) }}" class="mx-4 mt-4 sm:mx-6 lg:mx-8 rounded-[24px] bg-white p-4">
            @csrf
            <h2 class="text-right text-[18px] font-extrabold">قائمة الجودة</h2>
            <p class="mt-1 text-right text-[12px] text-[#8A7280]">اقفلي كل بند قبل التسليم.</p>
            <div class="mt-3 space-y-2">
                @foreach ($checks as $index => $label)
                    <label class="flex items-center justify-between gap-3 rounded-2xl bg-[#F6F0E8] px-3 py-3 text-[13px] font-bold">
                        <input type="checkbox" name="checks[{{ $index }}]" value="1" class="h-5 w-5">
                        <span>{{ $label }}</span>
                    </label>
                @endforeach
            </div>
            <button class="mt-4 h-12 w-full rounded-2xl bg-[#5C4454] font-extrabold text-white">اعتماد وتسليم</button>
        </form>
    @endif

    @if ($order->pipeline_stage === 3)
        <section class="mx-4 mt-4 sm:mx-6 lg:mx-8 rounded-[24px] bg-white p-4">
            <h2 class="text-right text-[18px] font-extrabold">اعتماد البارتنر</h2>
            <p class="mt-2 text-right text-[13px] leading-7 text-[#6B4F60]">الاعتماد هنا من لوحة المنفذة للعرض، مش من حساب بارتنر منفصل. {{ $order->deliveredCleanly() ? '' : 'تسليم وفيه بنود مراجعة.' }}</p>
            <ul class="mt-3 space-y-1 text-right text-[12px]">
                @foreach ($order->quality_checks ?? [] as $check)
                    <li class="{{ !empty($check['ok']) ? 'text-[#2F4529]' : 'text-[#7A3040]' }}">{{ !empty($check['ok']) ? 'تم' : 'محتاج مراجعة' }} · {{ $check['label'] }}</li>
                @endforeach
            </ul>
            <form method="POST" action="{{ route('worker.orders.approve', $order) }}">
                @csrf
                <button class="mt-4 h-12 w-full rounded-2xl bg-[#5C3A4A] font-extrabold text-white">اعتماد البارتنر وتسليم</button>
            </form>
        </section>
    @endif

    @if ($order->pipeline_stage === 4)
        <section class="mx-4 mt-4 sm:mx-6 lg:mx-8 rounded-[24px] bg-white p-4 text-right">
            <h2 class="text-[18px] font-extrabold">بانتظار تقييم العميل</h2>
            <p class="mt-2 text-[13px] leading-7 text-[#6B4F60]">سُلّم العمل. التقييم على جودة العمل والسعر والتعامل، وبعده يتحرر نصيبك.</p>
            <p class="mt-3 rounded-2xl bg-[#F6F0E8] px-3 py-2 text-[12px] leading-6">التقييم يتفتح للعميل من صفحة طلبه. لوحة المنفذة لا تفتح صفحة الدفع.</p>
        </section>
    @endif

    @if ($order->pipeline_stage === 5)
        <section class="mx-4 mt-4 sm:mx-6 lg:mx-8 rounded-[24px] bg-white p-4 text-right">
            <h2 class="text-[18px] font-extrabold">تم التسليم</h2>
            <p class="mt-2 text-[13px] leading-7">نصيبك من الطلب <span class="num font-extrabold">{{ number_format($order->workerShare()) }} جنيه</span></p>
            @if ($order->rating)
                <p class="mt-2 text-[12px] text-[#6B4F60]">تقييم العميل {{ $order->rating->average() }} من 5. {{ $order->rating->note }}</p>
            @endif
            <a href="{{ route('worker.orders.split', $order) }}" class="mt-4 flex h-12 items-center justify-center rounded-2xl bg-[#F6DE9E] font-extrabold text-[#5A4A22]">شوف توزيع الأرباح</a>
        </section>
    @endif

    <p class="mx-4 mt-4 sm:mx-6 lg:mx-8 text-center text-[12px] text-[#8A7280]">تنفيذ {{ $order->worker->name }} · مستوى تفاصيل {{ $order->tierMeta()['detail'] }}</p>
@endsection
