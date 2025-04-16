<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\User;

class CASController extends Controller {
    public function login(Request $request) {
        if(!Auth::check()) {
            if ($request->ajax()) {
                return response('Unauthorized.', 401);
            }
            cas()->authenticate();
        }
        $user_attributes = cas()->getAttributes();  
        $user = User::where('unique_id',$user_attributes['UDC_IDENTIFIER'])
            ->orWhere('email',$user_attributes['mail'])->first();
        if (is_null($user)) {
            $user = new User();
        }
        $user->unique_id = $user_attributes['UDC_IDENTIFIER'];
        $user->email = $user_attributes['mail'];
        $user->first_name = $user_attributes['firstname'];
        $user->last_name = $user_attributes['lastname'];
        $user->save();
        Auth::login($user);
        if ($request->has('redirect')) {
            return redirect($request->redirect);
        } else {
            return redirect('/');
        }
    }
}
