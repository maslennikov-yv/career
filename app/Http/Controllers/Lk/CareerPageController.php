<?php

namespace App\Http\Controllers\Lk;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CareerPageController extends Controller
{
    public function show(Request $request): View
    {
        return view('lk.career', [
            'user' => $request->user(),
        ]);
    }
}
