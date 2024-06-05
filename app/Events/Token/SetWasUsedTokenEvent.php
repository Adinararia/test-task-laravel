<?php

namespace App\Events\Token;

use Illuminate\Foundation\Events\Dispatchable;

class SetWasUsedTokenEvent
{
    use Dispatchable;

    public string $token;

    public function __construct(string $token)
    {
        $this->token = $token;
    }
}
