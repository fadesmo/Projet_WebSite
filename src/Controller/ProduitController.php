<?php

namespace App\Controller;

use App\Entity\Produit;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/produit', name: 'produit')]
class ProduitController extends AbstractController
{
    #[Route('/', name: '')]
    public function indexAction(): Response
    {
        return $this->redirectToRoute('produit_list', ['page' => 1]);
    }

    #[Route(
        '/list/{page}',
        name: '_list',
        requirements: ['page' => '[1-9]\d*'],
        defaults: [ 'page' => 0],        // la valeur par défaut ne respecte pas les contraintes
    )]
    public function listAction(int $page, EntityManagerInterface $em): Response
    {
        $produitRepository = $em->getRepository(Produit::class);
        $produits = $produitRepository->findAll();
        $args = array(
            'page' => $page,
            'produits' => $produits,
        );
        return $this->render('Produit/list.html.twig', $args);
    }
}
