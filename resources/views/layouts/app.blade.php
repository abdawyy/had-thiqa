<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'حد ثقة' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --cream: #FBF6F1;
            --ink: #2C2428;
            --plum: #5C3A4A;
            --gold: #E2B15A;
            --line: #EADFD4;
        }
        body { font-family: "Cairo", Tahoma, sans-serif; background: var(--cream); color: var(--ink); }
        .num { font-variant-numeric: tabular-nums; }
        .primary-action { scroll-margin-top: 6rem; }
    </style>
</head>
<body>
    <div class="min-h-screen">
        @if (auth()->user()?->role === 'seller')
            @include('partials.seller-nav')
        @elseif (auth()->user()?->role === 'admin')
            @include('partials.admin-nav')
        @elseif (auth()->user()?->role === 'worker' && ! View::hasSection('plain'))
            @include('partials.nav')
        @endif
        <div class="mx-auto w-full max-w-7xl pb-10">
            @include('partials.flash')
            @yield('content')
            @include('partials.footer')
        </div>
    </div>
</body>
</html>
