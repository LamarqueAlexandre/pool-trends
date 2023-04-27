<?php

namespace App\Controller;

use App\Message\NewUserWelcomeEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

class NotificationController extends AbstractController
{
    #[Route('/send-notification', name: 'send_notification')]
    public function index(MessageBusInterface $bus): Response
    {
        $bus->dispatch(New NewUserWelcomeEmail($this->getUser()->getId()->__toString()));
        return $this->render('notification/index.html.twig', [
            'controller_name' => 'NotificationController',
        ]);
    }
}
