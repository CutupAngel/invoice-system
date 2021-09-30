<?php

namespace App\Http\Controllers;

use Auth;
use Permissions;
use Illuminate\Http\Request;

class MarketingController extends Controller
{
    public function __construct()
    {
        if (!Permissions::has('marketing')) {
            //throw new Permissions::$exception;
      			return redirect(route('login'));
        }
    }

    public function index(Request $request)
    {
        return view('Marketing.marketing');
    }
}
