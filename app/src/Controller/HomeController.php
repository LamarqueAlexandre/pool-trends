<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\String\Slugger\SluggerInterface;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(Request $request, SluggerInterface $slugger): Response
    {
        $form = $this->createFormBuilder()
            ->add('hand_history', FileType::class, [
                'label' => 'Hand History',
                'mapped' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '100Mi',
                        'mimeTypes' => [
                            'text/plain'
                        ],
                        'mimeTypesMessage' => 'Please upload a valid document'
                    ])
                ]
            ])
            ->add('upload', SubmitType::class, ['label' => 'Upload the file'])
            ->getForm();
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $handHistoryFile = $form->get('hand_history')->getData();
            $originalFilename = pathinfo($handHistoryFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename . '-' . uniqid() . '.' . $handHistoryFile->guessExtension();

            $handHistoryFile->move(
                $this->getParameter('hands_history_directory') . '/' . sys_get_temp_dir() . '-' . uniqid(),
                $newFilename
            );

            return $this->redirectToRoute('app_home');
        }

        return $this->render('home/index.html.twig', [
            'form' => $form
        ]);
    }
}
