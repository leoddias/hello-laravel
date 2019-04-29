<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Post;
use Illuminate\Support\Facades\Validator;
use App\Exceptions\RedditException;
use App\Http\Services\RedditService;

class Posts extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $posts = Post::with('user', 'comments')->get();
        return response()->json($posts);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request data to create a new post
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $inputs = $request->only(['title', 'social_network']);
        Validator::make($inputs, $this->_rules())->validate();
        try {

            $post = Post::create(
                [
                    'title' => $inputs['title'],
                    'user_id' => Auth::id(),
                ]
            );

            if (in_array('reddit', $inputs['social_network'])) {
                $redditService = new RedditService(
                    $request->social_user,
                    $request->social_password
                );

                $response = $redditService->createNewPost($post->title);
                $post->third_api_id = $response->json->data->id;
                $post->save();
            }
            return response()->json($post->toArray(), 201);
        } catch (RedditException $re) {
            return response()->json(
                [
                    'message' => 'Post criado, porem não compartilhado em rede social!',
                    'error_message' => $re->getMessage()
                ],
                $re->getCode()
            );
        } catch (\Exception $e) {
            return response()->json(
                [
                    'message' => 'Erro inesperado!',
                    'error_message' => $e->getMessage()
                ],
                500
            );
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id from post
     *
     * @return Response
     */
    public function show($id)
    {
        $posts = Post::with('user', 'comments')->findOrFail($id);
        return response()->json($posts);
    }

    /**
     * Regras de validação
     *
     * @return array
     */
    private function _rules()
    {
        return [
            'title' => 'required|string',
            'social_network.*' => 'string',
        ];
    }
}
