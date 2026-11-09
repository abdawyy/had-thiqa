<header class="sticky top-0 z-30 border-b border-[#EADFD4] bg-[#FFFBF8]/95 backdrop-blur">
    <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">
        <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-2">
            <span class="grid h-10 w-10 place-items-center rounded-2xl bg-[#5C3A4A] text-[#FBF6F1]">
                <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3 5 6v6c0 4.5 3 7.5 7 9 4-1.5 7-4.5 7-9V6l-7-3Z"/><path d="m8.5 12 2.4 2.4 4.6-5"/></svg>
            </span>
            <span class="whitespace-nowrap">
                <span class="block text-[16px] font-extrabold leading-none text-[#2C2428]">حد ثقة</span>
                <span class="mt-1 hidden text-[11px] text-[#8A7280] sm:block">منصة شغل للستات</span>
            </span>
        </a>
        <nav class="hidden items-center gap-1 sm:flex">
            @include('partials.nav-links')
            @auth
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="rounded-full px-3 py-2 text-[13px] font-extrabold text-[#7A3040] hover:bg-[#F8E2E2]">خروج</button>
                </form>
            @endauth
        </nav>
    </div>
    <nav class="grid grid-cols-6 border-t border-[#F3EBE3] px-1 py-1 sm:hidden">
        @include('partials.nav-links', ['compact' => true])
        @auth
            <form method="POST" action="{{ route('logout') }}" class="flex items-center justify-center">
                @csrf
                <button class="rounded-full px-2 py-2 text-[11px] font-extrabold text-[#7A3040]">خروج</button>
            </form>
        @endauth
    </nav>
</header>
