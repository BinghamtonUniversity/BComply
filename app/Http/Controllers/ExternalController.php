<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Support\Facades\Auth;

use Illuminate\Http\Request;

class ExternalController extends Controller
{
    public function __construct() {
    }

    public function list(Request $request) {
        if ($request->has('accountId')) {
            $user = User::where('unique_id','like','_ext%')
                ->where('unique_id',$request->accountId)
                ->orWhere('unique_id','_ext_'.$request->accountId)
                ->first();
            if (!is_null($user)) {
                Auth::login($user);
                if ($request->has('redirect')) {
                    return redirect($request->redirect);
                } else {
                    return redirect('/');
                }
            } else {
                return view('external_login',['page'=>'external','error'=>'The '.$request->accountId.' guest user account is not authorized']);
            }
        } else {
            return view('external_login',['page'=>'external']);
        }
    }
}
