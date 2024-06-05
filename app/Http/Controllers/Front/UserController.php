<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;

class UserController extends Controller
{
    public function index()
    {
        return view('front.user.index');
    }

    public function create()
    {
        return view('front.user.create');

    }
}
