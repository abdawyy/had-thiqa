<?php

namespace App\Http\Controllers;

use App\Support\Wallet;
use Illuminate\Http\Request;

class PayoutController extends Controller
{
    public function store(Request $request)
    {
        $user = $request->user();
        $available = Wallet::available($user);

        $data = $request->validate([
            'amount' => 'required|integer|min:1|max:'.$available,
            'instapay_handle' => 'required|string|max:80',
        ], [
            'amount.max' => 'المبلغ أكبر من الجاهز للسحب.',
            'instapay_handle.required' => 'اكتبي معرف إنستاباي.',
        ]);

        Wallet::payout($user, (int) $data['amount'], $data['instapay_handle']);

        if ($user->role === 'worker') {
            $user->update([
                'withdrawable' => max(0, $user->withdrawable - (int) $data['amount']),
            ]);
        }

        return back()->with('status', 'اتحول '.$data['amount'].' جنيه على إنستاباي '.$data['instapay_handle'].' من خلال المنصة.');
    }
}
