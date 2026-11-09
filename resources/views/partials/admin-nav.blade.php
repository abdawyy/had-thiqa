<header class="sticky top-0 z-30 border-b border-[#EADFD4] bg-[#FFFBF8]/95 backdrop-blur">
    <div class="mx-auto flex max-w-7xl items-center justify-between gap-3 px-4 py-3 sm:px-6 lg:px-8">
        <a href="{{ route('admin.home') }}" class="font-extrabold">إدارة حد ثقة</a>
        <nav class="flex items-center gap-1 text-[13px] font-extrabold">
            <a href="{{ route('admin.home') }}" class="rounded-full px-3 py-2 {{ request()->routeIs('admin.home') ? 'bg-[#5C3A4A] text-white' : 'text-[#6B5560]' }}">اللوحة</a>
            <a href="{{ route('admin.analytics') }}" class="rounded-full px-3 py-2 {{ request()->routeIs('admin.analytics') ? 'bg-[#5C3A4A] text-white' : 'text-[#6B5560]' }}">تحليل</a>
            <form method="POST" action="{{ route('logout') }}">@csrf<button class="rounded-full px-3 py-2 text-[#7A3040]">خروج</button></form>
        </nav>
    </div>
</header>
