<?php

namespace App\Message;

class NewUserWelcomeEmail
{
    public function __construct(
        private string $userId,
    ) {  
    }

    public function getUserId(): string
    {
        return $this->userId;
    }
}