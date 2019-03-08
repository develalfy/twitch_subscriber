<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class TwitchController extends Controller
{
    public function __construct()
    {
    }

    public function redirectToProvider()
    {
        return Socialite::with('Twitch')->redirect();
    }

    public function handleProviderCallback(Request $request)
    {
        return $request->all();
    }
}
