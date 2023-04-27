<?php 

namespace App\MessageHandler;

use App\Message\NewUserWelcomeEmail;
use App\Repository\UserRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class NewUserWelcomeEmailHandler
{
    public function __construct(
        private UserRepository $userRepository
    ) {

    }

    public function __invoke(NewUserWelcomeEmail $welcomeEmail)
    {
        $user = $this->userRepository->find($welcomeEmail->getUserId());
    }
}