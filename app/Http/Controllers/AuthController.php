<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin(Request $request)
    {
        if ($request->query('as') === 'seller') {
            return redirect()->route('home');
        }

        return view('auth.login', [
            'as' => $this->audience($request->query('as')),
        ]);
    }

    public function login(Request $request)
    {
        if ($request->input('as') === 'seller') {
            return redirect()->route('home');
        }

        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'as' => 'required|in:worker,admin',
        ], [
            'email.required' => 'اكتبي البريد الإلكتروني.',
            'password.required' => 'اكتبي كلمة المرور.',
        ]);

        if (! Auth::attempt(['email' => $data['email'], 'password' => $data['password']], true)) {
            throw ValidationException::withMessages([
                'email' => 'بيانات الدخول غير صحيحة.',
            ]);
        }

        $request->session()->regenerate();

        if (Auth::user()->role !== $data['as']) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => 'هذا الحساب ليس لهذا المسار.',
            ]);
        }

        return redirect()->route($this->homeName(Auth::user()->role));
    }

    public function showRegister(Request $request)
    {
        abort_if(in_array($request->query('as'), ['admin', 'seller'], true), 404);

        $as = $this->audience($request->query('as'));

        return view('auth.register', ['as' => $as]);
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:80',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'as' => 'required|in:worker',
        ], [
            'name.required' => 'اكتبي الاسم.',
            'email.unique' => 'هذا البريد مستخدم بالفعل.',
            'password.min' => 'كلمة المرور 6 أحرف على الأقل.',
            'password.confirmed' => 'تأكيد كلمة المرور غير مطابق.',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'letter' => mb_substr($data['name'], 0, 1),
            'role' => $data['as'],
            'tier' => 'beginner',
            'level' => 1,
            'level_name' => $data['as'] === 'seller' ? 'بائعة' : 'ابتداء',
            'points' => 0,
            'is_available' => true,
            'referral_code' => strtoupper(Str::random(6)),
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route($this->homeName($user->role));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    private function audience(mixed $value): string
    {
        return in_array($value, ['worker', 'admin'], true) ? $value : 'worker';
    }

    private function homeName(string $role): string
    {
        return match ($role) {
            'admin' => 'admin.home',
            default => 'worker.home',
        };
    }
}
