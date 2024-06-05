<?php

namespace App\Http\Controllers\API\V1\Token;

use App\Enums\YesNoEnum;
use App\Http\Controllers\Controller;
use App\Models\Token;
use Firebase\JWT\JWT;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class TokenApiController extends Controller
{
    /**
     * @var string
     */
    private string $secretJwtKey;

    public function __construct()
    {
        $this->secretJwtKey = config('jwt.secret_key');
    }

    /**
     * @return JsonResponse
     */
    public function create(): JsonResponse
    {
        $uuid    = Str::uuid();
        $payload = [
            'iss' => config('app.name'),
            'sub' => $uuid,
            'iat' => Carbon::now()->timestamp,
            'exp' => Carbon::now()->addMinutes(40)->timestamp,
        ];

        $token = JWT::encode($payload, $this->secretJwtKey, 'HS256');
        if (!empty($token)) {
            Token::query()->create(['uuid' => $uuid, 'was_used' => YesNoEnum::No]);
        }

        return response()->json([
            'status' => 'success',
            'token'  => $token,
        ], 200);
    }
}
