@foreach ([
    ['worker.home', 'الرئيسية', 'M3 10.5 12 3l9 7.5V21a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1v-10.5Z'],
    ['worker.skills', 'مواهبي', 'M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm-7 9a7 7 0 0 1 14 0'],
    ['worker.projects', 'المشاريع', 'M6 4h9l3 3v13a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1Zm8 0v4h4'],
    ['worker.account', 'حسابي', 'M12 21a9 9 0 1 0-9-9 9 9 0 0 0 9 9Zm-3.5-8.5 2.2 2.2 4.3-4.4'],
    ['worker.analytics', 'تحليل', 'M4 19V5m5 14V9m5 10V7m5 12V3'],
] as [$route, $label, $icon])
    <a href="{{ route($route) }}" class="flex items-center justify-center gap-1.5 rounded-full font-extrabold {{ !empty($compact) ? 'flex-col px-1 py-2 text-[11px]' : 'px-3 py-2 text-[13px]' }} {{ request()->routeIs($route) ? 'bg-[#5C3A4A] text-[#FBF6F1]' : 'text-[#6B5560] hover:bg-[#F3EBE3]' }}">
        <svg viewBox="0 0 24 24" class="{{ !empty($compact) ? 'h-4 w-4' : 'h-4 w-4' }}" fill="none" stroke="currentColor" stroke-width="1.8">
            <path d="{{ $icon }}" stroke-linejoin="round" stroke-linecap="round"/>
        </svg>
        {{ $label }}
    </a>
@endforeach
