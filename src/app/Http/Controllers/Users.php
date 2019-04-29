<?php

namespace App\Http\Controllers;

use App\Comment;
use App\Http\Services\RedditService;
use App\User;
use Illuminate\Http\Request;

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
        $user = User::with('posts.comments')->findOrFail($id);
        return response()->json($user);
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
        $user = User::with('posts.comments')->findOrFail($id);
        $redditService = new RedditService();
        foreach ($user->posts as $post) {
            $postComments = $this->_commentsToArray($post);
            $redditComments = $redditService->getComments($post->third_api_id);

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
    }

    /**
     * Put all know reddit comment id in a array
     *
     * @param Post $post to use
     *
     * @return array
     */
    private function _commentsToArray($post)
    {
        $arr = [];
        foreach ($post->comments as $userComment) {
            $arr[] = $userComment->third_api_id;
        }
        return $arr;
    }
}
