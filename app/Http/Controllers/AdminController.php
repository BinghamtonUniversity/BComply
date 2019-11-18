<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Support\Facades\Auth;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function __construct() {
    }

    public function admin(Request $request, $page=null) {
        if (is_null($page)) {
            return view('default.admin',['page'=>null, 'id'=>null,'title'=>'Admin']);
        } else {
            return view('default.admin',['page'=>$page, 'id'=>null,'title'=>ucwords($page)]);
        }
    }
}
