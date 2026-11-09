<footer class="mt-10 border-t border-[#EADFD4] px-4 py-6 text-right text-[12px] leading-6 text-[#8A7280] sm:px-6 lg:px-8">
    <p class="font-extrabold text-[#5C3A4A]">حد ثقة</p>
    <p>المنصة في الوسط: العميل يدفع على إنستاباي {{ config('hadthiqa.instapay_handle') }}، والمنصة تخصم {{ \App\Support\Split::percent('platform') }}% من كل خدمة ثم تحوّل الباقي.</p>
    <p>الدفع في هذا النموذج تجريبي. لا يوجد تحويل بنكي حقيقي.</p>
</footer>
