@extends('layouts.app')

@section('plain')
@endsection

@section('content')
    <main class="mx-auto max-w-md px-4 py-10">
        <a href="{{ route('home') }}" class="text-[13px] font-extrabold text-[#8A7280]">رجوع</a>
        <h1 class="mt-4 text-right text-3xl font-extrabold">
            {{ $as === 'seller' ? 'دخول بائعة الخدمة' : ($as === 'admin' ? 'دخول الإدارة' : 'دخول المنفذة') }}
        </h1>
        <p class="mt-2 text-right text-[13px] leading-7 text-[#8A7280]">
            @if ($as === 'worker')
                الحساب التجريبي: sara@hadthiqa.test — كلمة المرور password
            @elseif ($as === 'seller')
                الحساب التجريبي: seller@hadthiqa.test — كلمة المرور password
            @else
                الحساب التجريبي: admin@hadthiqa.test — كلمة المرور password
            @endif
        </p>
        <form method="POST" action="{{ route('login.store') }}" class="mt-6 space-y-3">
            @csrf
            <input type="hidden" name="as" value="{{ $as }}">
            <label class="block text-right text-[12px] font-bold">البريد
                <input name="email" type="email" value="{{ old('email') }}" required class="mt-1 h-12 w-full rounded-2xl border border-black/10 bg-white px-3">
            </label>
            <label class="block text-right text-[12px] font-bold">كلمة المرور
                <input name="password" type="password" required class="mt-1 h-12 w-full rounded-2xl border border-black/10 bg-white px-3">
            </label>
            @if ($errors->any())
                <p class="rounded-2xl bg-[#F8E2E2] px-3 py-2 text-[13px] font-bold text-[#7A3040]">{{ $errors->first() }}</p>
            @endif
            <button class="h-12 w-full rounded-2xl bg-[#5C3A4A] font-extrabold text-white">دخول</button>
        </form>
        @if ($as !== 'admin')
            <a href="{{ route('register', ['as' => $as]) }}" class="mt-4 block text-center text-[13px] font-extrabold text-[#5C3A4A]">إنشاء حساب جديد</a>
        @endif
    </main>
@endsection
