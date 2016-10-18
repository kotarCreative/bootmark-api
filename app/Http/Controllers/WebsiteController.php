<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

class WebsiteController extends Controller
{
    /**
     * Shows the home page of bootmarkit.com
     *
     * @return The welcome view.
     */
    public function home()
    {
        return view('welcome');
    }
}
