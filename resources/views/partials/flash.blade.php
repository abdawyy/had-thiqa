@if (session('status') || session('error') || $errors->any())
    <div class="px-4 pt-4">
        <div class="rounded-2xl px-4 py-3 text-[13px] font-bold leading-6 {{ session('error') || $errors->any() ? 'bg-[#F8E2E2] text-[#7A3040]' : 'bg-[#DCEFD6] text-[#2F4529]' }}">
            {{ session('error') ?? session('status') ?? $errors->first() }}
        </div>
    </div>
@endif
