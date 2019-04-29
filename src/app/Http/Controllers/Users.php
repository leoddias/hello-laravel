<?php

namespace App\Http\Controllers;

use App\Comment;
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
        $user = User::with('posts.comments')->findOrFail($id);

        $redditService = new RedditService();
        $user = $redditService->fetchPostComments($user);

        $user = User::with('posts.comments')->findOrFail($id);

        return response()->json($user);
    }
}
