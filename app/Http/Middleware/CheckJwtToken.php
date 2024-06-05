<?php

namespace App\Http\Middleware;

use App\Enums\YesNoEnum;
use App\Models\Token;
use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckJwtToken
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (is_null($token) || empty($token) || $token === 'null') {
            return response()->json(['message' => 'Token not provided'], 401);
        }

        try {
            $decodedToken = JWT::decode($token, new Key(config('jwt.secret_key'), 'HS256'));
            $tokenModel   = Token::query()->where('uuid', $decodedToken->sub)->first();

            if (!$tokenModel) {
                return response()->json(['message' => 'Token not found'], 404);
            } elseif ($tokenModel->was_used == YesNoEnum::Yes) {
                return response()->json(['message' => 'Token is used'], 401);
            }
        } catch (\Exception $e) {
            throw new HttpResponseException(response()->json(['error' => $e->getMessage()], 401));
        }


        return $next($request);
    }
}
