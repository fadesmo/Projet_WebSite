<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


#[Route('', name: 'store')]
class StoreController extends AbstractController
{
    #[Route('', name: '_accueil')]
    public function accueilAction (): Response
    {
        return $this->render('Accueil/accueil.html.twig');
    }




}
