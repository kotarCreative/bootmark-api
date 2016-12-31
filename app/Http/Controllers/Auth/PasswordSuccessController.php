<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;

class PasswordSuccessController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function index()
    {
        return view('password_success');
    }
}