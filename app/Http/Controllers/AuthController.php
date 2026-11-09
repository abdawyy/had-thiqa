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
        return view('auth.login', [
            'as' => $this->audience($request->query('as')),
        ]);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'as' => 'required|in:worker,seller,admin',
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
        $as = $this->audience($request->query('as'));
        abort_if($as === 'admin', 404);

        return view('auth.register', ['as' => $as]);
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:80',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'as' => 'required|in:worker,seller',
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
        return in_array($value, ['worker', 'seller', 'admin'], true) ? $value : 'worker';
    }

    private function homeName(string $role): string
    {
        return match ($role) {
            'seller' => 'seller.home',
            'admin' => 'admin.home',
            default => 'worker.home',
        };
    }
}
