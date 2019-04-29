<?php

use Illuminate\Http\Request;

Route::post('/v1/login', 'Auth@store');

/* Api v1 - Rotas com autenticacao */
Route::group(
    ['prefix' => 'v1', 'middleware' => ['auth:api']],
    function () {
        Route::resource(
            'posts',
            'Posts',
            [
                'only' => ['store'],
            ]
        );
    }
);
