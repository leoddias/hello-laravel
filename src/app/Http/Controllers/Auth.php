<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;

class Auth extends Controller
{
    /**
     * Gera um token de autenticação
     *
     * @param \Illuminate\Http\Request $request dados de login
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $inputs = $request->only(['email', 'password']);
        Validator::make($inputs, $this->_rules())->validate();
        try {
            if (!$token = auth()->attempt($inputs)) {
                return response()->json(['errors' => 'Não autorizado!'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['errors' => 'Não foi possível gerar o Token'], 500);
        }
        return $this->respondeComToken($token);
    }

    /**
     * Cria e retorna um array com a estrutura do token
     *
     * @param string $token jwt
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondeComToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
        ]);
    }
    /**
     * Regras de validação
     * @return array
     */
    private function _rules()
    {
        return [
            'email' => 'required|email',
            'password' => 'required|string',
        ];
    }
}
