@extends('layouts.app')

@section('plain')
@endsection

@section('content')
    <main class="mx-auto max-w-md px-4 py-10">
        <a href="{{ route('login', ['as' => $as]) }}" class="text-[13px] font-extrabold text-[#8A7280]">عندك حساب؟ ادخلي</a>
        <h1 class="mt-4 text-right text-3xl font-extrabold">{{ $as === 'seller' ? 'حساب بائعة خدمة' : 'حساب منفذة' }}</h1>
        <form method="POST" action="{{ route('register.store') }}" class="mt-6 space-y-3">
            @csrf
            <input type="hidden" name="as" value="{{ $as }}">
            <label class="block text-right text-[12px] font-bold">الاسم
                <input name="name" value="{{ old('name') }}" required class="mt-1 h-12 w-full rounded-2xl border border-black/10 bg-white px-3">
            </label>
            <label class="block text-right text-[12px] font-bold">البريد
                <input name="email" type="email" value="{{ old('email') }}" required class="mt-1 h-12 w-full rounded-2xl border border-black/10 bg-white px-3">
            </label>
            <label class="block text-right text-[12px] font-bold">كلمة المرور
                <input name="password" type="password" required class="mt-1 h-12 w-full rounded-2xl border border-black/10 bg-white px-3">
            </label>
            <label class="block text-right text-[12px] font-bold">تأكيد كلمة المرور
                <input name="password_confirmation" type="password" required class="mt-1 h-12 w-full rounded-2xl border border-black/10 bg-white px-3">
            </label>
            @if ($errors->any())
                <p class="rounded-2xl bg-[#F8E2E2] px-3 py-2 text-[13px] font-bold text-[#7A3040]">{{ $errors->first() }}</p>
            @endif
            <button class="h-12 w-full rounded-2xl bg-[#5C3A4A] font-extrabold text-white">إنشاء الحساب</button>
        </form>
    </main>
@endsection
