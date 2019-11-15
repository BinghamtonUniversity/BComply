<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\ModuleAssignment;

class UserDashboardController extends Controller
{
    public function home(Request $request) {
        if (!Auth::check()) {
            return redirect('/demo');
        }
        $assignments = ModuleAssignment::where('user_id',Auth::user()->id)
            ->where('date_assigned','<=',now())
            ->with('version')->get();
        // return $assignments;
        return view('home',['assignments'=>$assignments,'user'=>Auth::user()]);
    }

}
