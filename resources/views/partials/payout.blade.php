<section class="rounded-[24px] bg-white p-4 text-right md:p-6">
    <h2 class="text-[16px] font-extrabold">{{ auth()->user()->role === 'seller' ? 'استلمي ربحك' : 'استلمي نصيبك' }}</h2>
    <p class="mt-1 text-[12px] leading-6 text-[#8A7280]">فلوس داخلة لك. العميل دفع للمنصة، والمنصة بتحوّل الجاهز على إنستاباي. المتاح الآن {{ number_format($available) }} جنيه.</p>
    <form method="POST" action="{{ route('payout.store') }}" class="mt-3 space-y-2">
        @csrf
        <input name="instapay_handle" required placeholder="اسمك@instapay" class="h-11 w-full rounded-2xl border border-black/10 px-3">
        <input name="amount" type="number" min="1" max="{{ max(1, $available) }}" placeholder="المبلغ" class="h-11 w-full rounded-2xl border border-black/10 px-3">
        <button class="h-11 w-full rounded-2xl bg-[#5C3A4A] font-extrabold text-white" @disabled($available < 1)>استلمي على إنستاباي</button>
    </form>
</section>
