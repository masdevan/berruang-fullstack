<?php

namespace App\Http\Controllers;

class HomeController extends Controller
{
    public function index()
    {
        if (auth()->check()) {
            return redirect('/messages');
        }

        return view('auth.login');
    }
}
