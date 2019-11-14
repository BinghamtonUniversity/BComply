<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserDashboardController extends Controller
{
    public function home(Request $request) {
        if (!Auth::check()) {
            return redirect('/demo');
        }
        return view('home',['modules'=>[
            (Object)[
                'name'=>'Test Module',
                'module_id'=>1,
                'module_version_id'=>1,
                'filename'=>'story.html',
            ],
        ],'user'=>Auth::user()]);
    }

}
