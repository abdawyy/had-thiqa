<?php

namespace App\Http\Controllers;

use App\Models\Order;

class HomeController extends Controller
{
    public function index()
    {
        $resume = session('last_order_id')
            ? Order::query()->find(session('last_order_id'))
            : null;

        return view('chooser', [
            'resume' => $resume,
            'user' => auth()->user(),
            'workerStages' => config('hadthiqa.worker_stages'),
            'clientStages' => config('hadthiqa.client_stages'),
        ]);
    }
}
