<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Services\RedditService;
use App\User;

class Users extends Controller
{
    /**
     * Retrive all user information,
     * posts with comments
     *
     * @param int $id from user
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $this->_fetchPostComments($id);
    }

    /**
     * Retrive all user information,
     * requesting social networks api to
     * fetch new comments an save them in the database.
     *
     * @param int $id from user
     *
     * @return \Illuminate\Http\Response
     */
    private function _fetchPostComments($id)
    {
        $user = User::with('posts')->findOrFail($id);
        $redditService = new RedditService();
        return response()->json($user);
        foreach ($user->posts as $post) {
            $comments = $post->comments;
            $data = $redditService->getComments($post->id);
        }
    }
}
