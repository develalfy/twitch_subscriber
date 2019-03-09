<?php

namespace App\Core\Services;

use App\User;
use Auth;
use GuzzleHttp\Client;

class UserService
{
    private $user;
    private $apiUrl = "https://api.twitch.tv/helix/";

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * @param \SocialiteProviders\Manager\OAuth2\User $twitchUser
     * @return User
     */
    public function findOrCreate(\SocialiteProviders\Manager\OAuth2\User $twitchUser): User
    {
        $user = $this->user->where('twitch_id', $twitchUser->id)->first();
        if ($user){
            $this->updateToken($twitchUser);
            return $user;
        }

        return $this->user->create([
            'name' => $twitchUser->name,
            'twitch_token' => $twitchUser->token,
            'email' => $twitchUser->email,
            'twitch_id' => $twitchUser->id,
        ]);
    }

    /**
     * @param User $user
     */
    public function loginUser(User $user)
    {
        Auth::login($user);
    }

    /**
     * Log the user out
     */
    public function logoutUser()
    {
        Auth::logout();
    }

    /**
     * Get streamer if exists
     * @param string $name
     * @return object
     */
    public function getStreamer(string $name): object
    {
        $client = new Client();
        $header = array('Authorization'=>'Bearer ' . Auth::user()->twitch_token);
        $response = $client->get($this->apiUrl. "users?login=" . $name, array('headers' => $header));

        $streamer = collect(json_decode($response->getBody()->getContents())->data);
        if ($streamer->count())
            return collect($streamer[0]);

        return $streamer;
    }

    /**
     * update twitch token
     * @param \SocialiteProviders\Manager\OAuth2\User $twitchUser
     * @return bool
     */
    private function updateToken(\SocialiteProviders\Manager\OAuth2\User $twitchUser)
    {
        return $this->user->where(['email' =>$twitchUser['email']])->first()->update(['twitch_token' => $twitchUser->token]);
    }
}