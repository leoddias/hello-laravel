<?php

namespace App\Http\Services;

use App\Exceptions\RedditException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Rudolf\OAuth2\Client\Provider\Reddit;
use Log;

class RedditService
{
    private $_access_token;
    private $_user;
    private $_password;
    private $_client;

    /**
     * Create a new controller instance.
     *
     * @param string $user     reddit username
     * @param string $password reddit password
     *
     * @return void
     */
    public function __construct($user, $password)
    {
        $this->_user = $user;
        $this->_password = $password;
        $this->_access_token = $this->_auth();
        $this->_client = new Client();
    }

    /**
     * Request access token in reddit's api
     * with the ENV's var APP_ID, APP_SECRET,
     * and the user and password provided.
     *
     * @return string with access token
     */
    private function _auth()
    {
        try {
            $reddit = new Reddit(
                [
                    'clientId'      => env('REDIT_APP_ID'),
                    'clientSecret'  => env('REDIT_APP_SECRET'),
                    'redirectUri'   => 'http://github.com/leoddias/hellolaravel',
                    'userAgent'     => 'laravel:hello:1.0, (by /u/leoddias)',
                    'scopes'        => ['identity', 'read', 'submit'],
                ]
            );

            $access_token = $reddit->getAccessToken(
                'password',
                [
                    'username' => $this->_user,
                    'password' => $this->_password,
                ]
            );

            return $access_token;
        } catch (ClientException $ce) {
            Log::info($ce);
            throw new RedditException('Erro na autenticacao' . $e->getMessage(), $ce->getCode());
        } catch (\Exception $e) {
            Log::error($e);
            throw new RedditException('Erro desconhecido na autenticacao, consulte os logs!', 500);
        }
    }

    /**
     * Create a new reddit post in user's profile.
     *
     * @param string $title of the post
     *
     * @return array with response from reddit's api
     */
    public function createNewPost($title)
    {
        try {
            $response = $this->_client->post(
                'https://oauth.reddit.com/api/submit',
                [
                    'form_params' => [
                        'api_type' => 'json',
                        'sr' => 'u_'.$this->_user,
                        'title' => $title,
                        'text' => 'Default Message for app hello-laravel',
                        'kind' => 'self',
                        'uh' => 'f0f0f0f0'
                    ],
                    'headers' => [
                        "User-Agent" => 'laravel:hello:1.0, (by /u/leoddias)',
                        'Authorization' => 'Bearer '. $this->_access_token,
                    ]
                ]
            );
            $data = json_decode($response->getBody()->getContents());
            if (isset($data->errors) && sizeof($data->erros) > 0 ) {
                throw new RedditException($data->errors[0]);
            }
            return $data;
        } catch (ClientException $ce) {
            Log::info($ce);
            throw new RedditException('Erro ao compartilhar o post no reddit' . $e->getMessage(), $ce->getCode());
        } catch (\Exception $e) {
            Log::error($e);
            throw new RedditException('Erro desconhecido ao criar o post, consulte os logs!', 500);
        }
    }

    /**
     * Get all comments from a post id
     *
     * @param string $post_id post id
     *
     * @return Object with post and all comments
     */
    public function getComments($post_id)
    {
        $url = "https://www.reddit.com/comments/" . $post_id . "/.json";
        $response = $this->_client->get($url);
        return $response->getBody()->getContents();
    }
}
