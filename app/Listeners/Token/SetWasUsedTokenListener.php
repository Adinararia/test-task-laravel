<?php

namespace App\Listeners\Token;

use App\Enums\YesNoEnum;
use App\Models\Token;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class SetWasUsedTokenListener
{
    public function handle($event): void
    {
        $decodedToken = JWT::decode($event->token, new Key(config('jwt.secret_key'), 'HS256'));
        Token::query()->where('uuid', $decodedToken->sub)->update(['was_used' => YesNoEnum::Yes]);
    }
}
