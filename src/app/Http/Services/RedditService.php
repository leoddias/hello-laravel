<?php

namespace App\Http\Services;

use App\Exceptions\RedditException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Log;
use Rudolf\OAuth2\Client\Provider\Reddit;
use App\Comment;

const COMMENT_TYPE = 't1';
class RedditService
{
    private $_access_token;
    private $_user;
    private $_password;
    private $_client;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
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
                    'clientId' => env('REDIT_APP_ID'),
                    'clientSecret' => env('REDIT_APP_SECRET'),
                    'redirectUri' => 'http://github.com/leoddias/hellolaravel',
                    'userAgent' => 'laravel:hello:1.0, (by /u/leoddias)',
                    'scopes' => ['identity', 'read', 'submit'],
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
     * @param string $title    of the post
     * @param string $user     reddit's user
     * @param string $password reddit's password
     *
     * @return array with response from reddit's api
     */
    public function createNewPost($title, $user, $password)
    {
        $this->_user = $user;
        $this->_password = $password;
        $this->_access_token = $this->_auth();

        try {
            $response = $this->_client->post(
                'https://oauth.reddit.com/api/submit',
                [
                    'form_params' => [
                        'api_type' => 'json',
                        'sr' => 'u_' . $this->_user,
                        'title' => $title,
                        'text' => 'Default Message for app hello-laravel',
                        'kind' => 'self',
                        'uh' => 'f0f0f0f0',
                    ],
                    'headers' => [
                        "User-Agent" => 'laravel:hello:1.0, (by /u/leoddias)',
                        'Authorization' => 'Bearer ' . $this->_access_token,
                    ],
                ]
            );
            $data = json_decode($response->getBody()->getContents());
            if (isset($data->errors) && sizeof($data->erros) > 0) {
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
     * @return array with all comments
     */
    private function _getComments($post_id)
    {
        try {
            $url = "https://www.reddit.com/comments/" . $post_id . "/.json";
            $response = $this->_client->get(
                $url,
                [
                    'headers' =>
                    [
                        "User-Agent" => 'laravel:hello:1.0, (by /u/leoddias)',
                    ],
                ]
            );
            return $this->_redditCommentsToArray(
                $response->getBody()->getContents()
            );
        } catch (\Exception $e) {
            Log::error('Erro ao tentar recueprar comentarios do post ' . $post_id . ' error: ' . $e->getMessage());
        }
    }

    /**
     * Put all comments from json to an array
     *
     * @param array $rawJson from reddits api
     *
     * @return array
     */
    private function _redditCommentsToArray($rawJson)
    {
        $dataJson = json_decode($rawJson);
        $array = [];

        foreach ($dataJson as $data) {
            foreach ($data->data->children as $datachild) {
                if ($datachild->kind === COMMENT_TYPE) {
                    $array[$datachild->data->id] = $datachild->data->body;
                }
            }
        }

        return $array;
    }

    /**
     * For each post, request in reddit's api
     * to fetch new comments and save them in the database
     *
     * @param Collection $posts object list
     *
     * @return void.
     */
    public function fetchPostsComments($posts)
    {
        foreach ($posts as $post) {
            $this->fetchPostComments($post);
        }
    }

    /**
     * Request in reddit's api to
     * fetch new comments an save them in the database.
     *
     * @param Post $post object
     *
     * @return void.
     */
    public function fetchPostComments($post)
    {
        $postComments = $this->_postCommentsToArray($post);
        $redditComments = $this->_getComments($post->third_api_id);

        foreach ($redditComments as $key => $comment) {
            if (!in_array($key, $postComments)) {
                Comment::create(
                    [
                        'post_id' => $post->id,
                        'title' => "Comment " . $key,
                        'text' => $comment,
                        'third_api_id' => $key,

                    ]
                );
            }
        }
    }

    /**
     * Put all know reddit comment id in a array
     *
     * @param Post $post to use
     *
     * @return array
     */
    private function _postCommentsToArray($post)
    {
        $arr = [];
        foreach ($post->comments as $userComment) {
            $arr[] = $userComment->third_api_id;
        }
        return $arr;
    }
}
