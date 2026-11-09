@extends('layouts.app')

@section('plain')
@endsection

@section('content')
    <header class="flex items-center justify-between px-4 pt-5 sm:px-6 lg:px-8">
        <a href="{{ route('home') }}" class="rounded-full bg-white px-4 py-2 text-[13px] font-extrabold">رجوع</a>
        <h1 class="text-[22px] font-extrabold">طلب جديد</h1>
    </header>
    <form id="order-form" method="POST" action="{{ route('client.store') }}" enctype="multipart/form-data" class="px-4 pb-8 sm:px-6 lg:px-8">
        @csrf
        <section data-step="1" class="mt-4">
            <p class="text-right text-[13px] font-extrabold text-[#8A7280]">١ من ٤ · اختر الخدمة</p>
            <div class="mt-3 grid gap-2 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($services as $service)
                    <label class="block cursor-pointer rounded-[22px] bg-white p-4 has-[:checked]:ring-2 has-[:checked]:ring-[#5C4454]">
                        <input class="sr-only" type="radio" name="service_id" value="{{ $service->id }}" data-price="{{ $service->price }}" data-name="{{ $service->name }}" @checked(old('service_id') == $service->id)>
                        <span class="flex items-start justify-between gap-3">
                            <span class="num rounded-full bg-[#5C4454] px-3 py-1 text-[12px] font-extrabold text-white">{{ number_format($service->price) }} ج</span>
                            <span class="text-right">
                                <span class="block text-[15px] font-extrabold">{{ $service->name }}</span>
                                <span class="mt-1 block text-[12px] leading-6 text-[#8A7280]">{{ $service->sample }}</span>
                            </span>
                        </span>
                    </label>
                @endforeach
            </div>
            <button type="button" data-next class="mt-4 h-12 w-full rounded-2xl bg-[#5C4454] font-extrabold text-white">التالي</button>
        </section>

        <section data-step="2" hidden class="mt-4">
            <p class="text-right text-[13px] font-extrabold text-[#8A7280]">٢ من ٤ · تفاصيل الطلب</p>
            <label class="mt-3 block text-right text-[12px] font-bold">اسمك
                <input name="client_name" value="{{ old('client_name') }}" placeholder="مثال: م. منى" class="mt-1 h-12 w-full rounded-2xl border border-black/10 bg-white px-3">
            </label>
            <label class="mt-3 block text-right text-[12px] font-bold">وصف قصير
                <textarea name="description" rows="4" placeholder="اكتب تفاصيل طلبك هنا" class="mt-1 w-full rounded-2xl border border-black/10 bg-white p-3">{{ old('description') }}</textarea>
            </label>
            <label class="mt-3 block rounded-2xl bg-white p-3 text-right text-[12px] font-bold">ارفع صور · حتى 3
                <input type="file" name="photos[]" accept="image/*" multiple class="mt-2 block w-full text-[12px]">
            </label>
            <label class="mt-3 flex items-center justify-between rounded-2xl bg-white px-3 py-3 text-[13px] font-bold">
                <input type="checkbox" name="voice_note" value="1">
                <span>ملاحظة صوتية تجريبية</span>
            </label>
            <label class="mt-3 block text-right text-[12px] font-bold">كود الدعوة (اختياري)
                <input name="referral_code" value="{{ old('referral_code', $code) }}" placeholder="THIQA10" class="mt-1 h-12 w-full rounded-2xl border border-black/10 bg-white px-3">
            </label>
            <div class="mt-4 grid grid-cols-2 gap-2">
                <button type="button" data-prev class="h-12 rounded-2xl bg-white font-extrabold">رجوع</button>
                <button type="button" data-next class="h-12 rounded-2xl bg-[#5C4454] font-extrabold text-white">التالي</button>
            </div>
        </section>

        <section data-step="3" hidden class="mt-4">
            <p class="text-right text-[13px] font-extrabold text-[#8A7280]">٣ من ٤ · السرعة ومستوى التفاصيل</p>
            <div class="mt-3 grid grid-cols-2 gap-2">
                @foreach ($speeds as $key => $speed)
                    <label class="rounded-2xl bg-white p-3 text-right has-[:checked]:ring-2 has-[:checked]:ring-[#5C4454]">
                        <input class="sr-only" type="radio" name="speed" value="{{ $key }}" data-mult="{{ $speed['mult'] }}" @checked(old('speed', 'normal') === $key)>
                        <span class="block text-[14px] font-extrabold">{{ $speed['label'] }}</span>
                        <span class="mt-1 block text-[11px] text-[#8A7280]">{{ $speed['note'] }}</span>
                    </label>
                @endforeach
            </div>
            <p class="mt-4 text-right text-[12px] font-bold">مستوى التفاصيل يحدد مين ينفّذ. السعر ثابت حسب الخدمة.</p>
            <div class="mt-2 space-y-2">
                @foreach ($tiers as $key => $tier)
                    <label class="block rounded-2xl bg-white p-3 text-right has-[:checked]:ring-2 has-[:checked]:ring-[#5C4454]">
                        <input class="sr-only tier-input" type="radio" name="detail_level" value="{{ $key }}" @checked(old('detail_level', 'pro') === $key)>
                        <span class="block text-[14px] font-extrabold">{{ $tier['name'] }} · {{ $tier['detail'] }}</span>
                        <span class="block text-[11px] text-[#8A7280]">{{ $tier['desc'] }}</span>
                    </label>
                @endforeach
            </div>
            <p class="mt-4 text-right text-[13px] font-extrabold">المتاحات الآن</p>
            <div class="mt-2 grid gap-2 sm:grid-cols-2" id="workers">
                <label class="block rounded-2xl bg-[#F6DE9E] p-3 text-right">
                    <input class="sr-only" type="radio" name="worker_id" value="" checked>
                    <span class="block text-[14px] font-extrabold">اختر لي الأنسب</span>
                    <span class="block text-[11px]">الترتيب حسب ملاءمة الطلب والإتاحة والتقييم.</span>
                </label>
                @foreach ($workers as $person)
                    <label data-tier="{{ $person->tier }}" class="worker-card block rounded-2xl bg-white p-3 text-right has-[:checked]:ring-2 has-[:checked]:ring-[#5C4454]">
                        <input class="sr-only" type="radio" name="worker_id" value="{{ $person->id }}">
                        <span class="flex items-center justify-between">
                            <span class="num text-[12px] font-bold text-[#8A7280]">{{ $person->rating ?: 'جديدة' }}</span>
                            <span>
                                <span class="block text-[14px] font-extrabold">{{ $person->name }}</span>
                                <span class="block text-[11px] text-[#8A7280]">{{ $person->tierMeta()['name'] }} · {{ $person->jobs_text }}{{ $person->is_new ? ' · يُعتمد عملها' : '' }}</span>
                            </span>
                        </span>
                    </label>
                @endforeach
            </div>
            <div class="mt-4 grid grid-cols-2 gap-2">
                <button type="button" data-prev class="h-12 rounded-2xl bg-white font-extrabold">رجوع</button>
                <button type="button" data-next class="h-12 rounded-2xl bg-[#5C4454] font-extrabold text-white">التالي: الدفع</button>
            </div>
        </section>

        <section data-step="4" hidden class="mt-4">
            <p class="text-right text-[13px] font-extrabold text-[#8A7280]">٤ من ٤ · الدفع على المنصة</p>
            <div class="mt-3 rounded-[24px] bg-white p-4 text-right">
                <p id="summary" class="text-[14px] font-extrabold leading-7"></p>
                <p class="mt-2 text-[12px] leading-6 text-[#6B4F60]">المبلغ محجوز إلى أن توافق.</p>
                <p class="mt-2 text-[12px]">المتابعة التفصيلية اختيارية: تشوفي خطوات الـ SOP. من غيرها بتعرفي بس إن حد بدأ، والتسليم في الآخر. +{{ number_format($pricing['tracking_fee']) }} جنيه.</p>
                <label class="mt-3 flex items-center justify-between rounded-2xl bg-[#F6F0E8] px-3 py-3 text-[13px] font-bold">
                    <input id="tracking" type="checkbox" name="tracking" value="1">
                    <span>متابعة تفصيلية</span>
                </label>
            </div>
            <div class="mt-3 grid grid-cols-3 gap-2 text-center text-[12px] font-extrabold">
                @foreach (['instapay' => 'إنستاباي', 'card' => 'كارت', 'wallet' => 'محفظة'] as $method => $label)
                    <label class="rounded-2xl bg-white py-3 has-[:checked]:bg-[#5C4454] has-[:checked]:text-white">
                        <input class="sr-only" type="radio" name="payment_method" value="{{ $method }}" @checked(old('payment_method', 'instapay') === $method)>
                        {{ $label }}
                    </label>
                @endforeach
            </div>
            <p class="mt-2 text-center text-[11px] text-[#8A7280]">الكارت والمحفظة وإنستاباي تجريبيين في هذا النموذج.</p>
            <div class="mt-4 grid grid-cols-2 gap-2">
                <button type="button" data-prev class="h-12 rounded-2xl bg-white font-extrabold">رجوع للتعديل</button>
                <button class="primary-action h-12 rounded-2xl bg-[#5C3A4A] font-extrabold text-white">سجّل الطلب</button>
            </div>
        </section>
    </form>
    <script>
        const sections = [...document.querySelectorAll('[data-step]')];
        const pricing = @json($pricing);
        let step = 1;
        function show(n) {
            step = n;
            sections.forEach((el) => el.hidden = Number(el.dataset.step) !== n);
            if (n === 4) paintSummary();
            if (n === 3) filterWorkers();
        }
        function selected(name) {
            return document.querySelector(`input[name="${name}"]:checked`);
        }
        function paintSummary() {
            const service = selected('service_id');
            const speed = selected('speed');
            const price = service ? Number(service.dataset.price) : 0;
            const mult = speed ? Number(speed.dataset.mult) : 1;
            const code = (document.querySelector('[name="referral_code"]').value || '').trim().toUpperCase();
            const base = Math.round(price * mult);
            const percent = Number((pricing.discounts || {})[code] || 0);
            const discount = Math.round(base * percent / 100);
            const tracking = document.getElementById('tracking').checked ? Number(pricing.tracking_fee) : 0;
            const total = base - discount + tracking;
            const lines = [
                (service ? service.dataset.name : 'الخدمة'),
                `الخدمة ${base.toLocaleString('en-US')} جنيه`,
            ];
            if (discount) lines.push(`خصم ${discount.toLocaleString('en-US')} جنيه`);
            if (tracking) lines.push(`متابعة ${tracking.toLocaleString('en-US')} جنيه`);
            lines.push(`الإجمالي ${total.toLocaleString('en-US')} جنيه`);
            document.getElementById('summary').textContent = lines.join(' · ');
        }
        function filterWorkers() {
            const tier = selected('detail_level')?.value;
            document.querySelectorAll('.worker-card').forEach((card) => {
                const hide = card.dataset.tier !== tier;
                card.hidden = hide;
                const input = card.querySelector('input');
                if (hide && input?.checked) {
                    input.checked = false;
                    document.querySelector('input[name="worker_id"][value=""]').checked = true;
                }
            });
        }
        document.querySelectorAll('[data-next]').forEach((btn) => btn.addEventListener('click', () => {
            if (step === 1 && !selected('service_id')) return alert('اختاري الخدمة أولاً');
            if (step === 2 && !document.querySelector('[name="description"]').value.trim()) return alert('اكتبي تفاصيل الطلب');
            show(step + 1);
        }));
        document.querySelectorAll('[data-prev]').forEach((btn) => btn.addEventListener('click', () => show(step - 1)));
        document.getElementById('tracking').addEventListener('change', paintSummary);
        document.querySelectorAll('.tier-input').forEach((input) => input.addEventListener('change', filterWorkers));
    </script>
@endsection
