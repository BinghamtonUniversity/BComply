<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\User;

class CASController extends Controller {
    public function login(Request $request) {
        if(!Auth::check() && !cas()->checkAuthentication()) {
            if ($request->ajax()) {
                return response('Unauthorized.', 401);
            }
            cas()->authenticate();
        }
        $user_attributes = cas()->getAttributes();        
        $user = User::where('unique_id',$user_attributes['UDC_IDENTIFIER'])->first();
        if (!is_null($user)) {
            Auth::login($user,true);
            if ($request->has('redirect')) {
                return redirect($request->redirect);
            } else {
                return redirect('/');
            }
        } else {
            return view('not_authorized',[
                'page' => 'error',
                'bnumber'=> $user_attributes['UDC_IDENTIFIER']
            ]);
        }

    }
}
