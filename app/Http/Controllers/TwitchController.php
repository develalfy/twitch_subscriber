<?php

namespace App\Http\Controllers;

use App\Core\Services\UserService;
use App\Http\Requests\StreamRequest;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class TwitchController extends Controller
{
    protected $provider = 'Twitch';
    private $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function redirectToProvider()
    {
        return Socialite::with($this->provider)->redirect();
    }

    public function handleProviderCallback(Request $request)
    {
        try {
            $twitchUser = Socialite::with($this->provider)->user();
        } catch (\Exception $e) {
            return redirect(route('twitch.auth'))->withErrors(['errors' => 'Problem while logging using twitch']);
        }

        $user = $this->userService->findOrCreate($twitchUser);

        try {
            $this->userService->loginUser($user);
        } catch (\Exception $e) {
            return redirect(route('twitch.auth'))->withErrors(['errors' => 'Problem while logging using twitch']);
        }

        return redirect()->back();
    }

    public function stream(StreamRequest $request)
    {
        try {
            $streamer = $this->userService->getStreamer($request->name);
        } catch (\Exception $e) {
            if ($e->getCode() === 401){
                $this->userService->logoutUser();
            }

            return redirect('/')->withErrors(['errors' => "Can't find streamer, plz try another one !"]);
        }

        if (!$streamer->count()){
            return redirect('/')->withErrors(['errors' => "Can't find streamer, plz try another one !"]);
        }

        return view('home', compact('streamer'));
    }
}
