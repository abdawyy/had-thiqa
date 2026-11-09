<header class="sticky top-0 z-30 border-b border-[#EADFD4] bg-[#FFFBF8]/95 backdrop-blur">
    <div class="mx-auto flex max-w-7xl items-center gap-3 px-4 py-3 sm:px-6 lg:px-8">
        <a href="{{ $home }}" class="flex shrink-0 items-center gap-2">
            <span class="grid h-10 w-10 place-items-center rounded-2xl bg-[#5C3A4A] text-sm font-extrabold text-[#FBF6F1]">{{ $mark }}</span>
            <span class="leading-tight">
                <span class="block text-[15px] font-extrabold">حد ثقة</span>
                <span class="block text-[11px] text-[#8A7280]">{{ $role }}</span>
            </span>
        </a>
        <nav class="flex min-w-0 flex-1 items-center gap-1 overflow-x-auto text-[13px] font-extrabold [scrollbar-width:none]">
            @foreach ($links as $link)
                @php $pattern = explode(',', $link[2] ?? $link[0]); @endphp
                <a href="{{ route($link[0]) }}" class="shrink-0 rounded-full px-3 py-2 {{ request()->routeIs(...$pattern) ? 'bg-[#5C3A4A] text-white' : 'text-[#6B5560] hover:bg-[#F3EBE3]' }}">{{ $link[1] }}</a>
            @endforeach
        </nav>
        <form method="POST" action="{{ route('logout') }}" class="shrink-0">
            @csrf
            <button class="rounded-full px-3 py-2 text-[13px] font-extrabold text-[#7A3040] hover:bg-[#F8E2E2]">خروج</button>
        </form>
    </div>
</header>
