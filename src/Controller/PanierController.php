<?php

namespace App\Controller;

use App\Entity\Panier;
use App\Entity\Produit;
use App\Entity\PanierProduit;
use App\Entity\User;
use App\Form\CommType;
use App\Form\PanierProduitType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;



#[Route('/panier', name: 'panier')]
class PanierController extends AbstractController
{

    #[Route('', name:'' )]
    public function panierlistAction(Security $security, EntityManagerInterface $em) : Response
    {
        $user = $security->getUser();
        $id = $user->getId();

        $panierproduitRespository = $em->getRepository(PanierProduit::class);
        $produits = $panierproduitRespository->findBy(['panier'=> $id]);
        $args = array(
            'id' => $id,
            'produits' => $produits,
        );
        return $this->render('Panier/panier.html.twig',$args);
    }

    #[Route('/client/{id}', name:'_client_id' )]
    public function panierClientAction(Security $security, EntityManagerInterface $em, int $id) : Response
    {

        $panierproduitRespository = $em->getRepository(PanierProduit::class);
        $produits = $panierproduitRespository->findBy(['panier'=> $id]);
        $args = array(
            'id' => $id,
            'produits' => $produits,
        );
        return $this->render('Panier/panier.html.twig',$args);
    }

    #[Route('/supprimer/{id}', name:'_supprimer' )]
    public function supprimerProduitAction(int $id, EntityManagerInterface $em) : Response
    {

        $PanierProduitRepository = $em->getRepository(PanierProduit::class);
        $monproduit = $PanierProduitRepository->find($id);

        $produitreference = $monproduit->getProduit();

         // stock dans le panier
         $stockpanier = $monproduit->getQuantite();

        // stock dans la base de donnes
        $stockproduit = $produitreference->getQuantite();

        // on re attribue les stock de quoi on a supprimer
        $produitreference->setQuantite($stockproduit + $stockpanier);

        $em->remove($monproduit);
        $em->flush();

        $this->addFlash('info', 'Produit :'. $produitreference->getLibelle() . 'Supprimer' );
        return $this->redirectToRoute('panier');
    }

    #[Route('/vider/{id}', name: '_vider')]
    public function viderPanierAction(int $id, EntityManagerInterface $em) : Response
    {

        $PanierProduitRepository = $em->getRepository(PanierProduit::class);
        $paniers = $PanierProduitRepository->findBy(['panier'=> $id]);

        foreach( $paniers as $panier ){
            $quantite = $panier->getQuantite();
            $produit = $panier->getProduit();
            $produitStock = $produit->getQuantite();
            $produit->setQuantite($quantite + $produitStock);

            $em->remove($panier);

        }

        $em->flush();

        return $this->redirectToRoute('panier');

    }
    #[Route('/payer', name:'_payer' )]
    public function payerAction( EntityManagerInterface $em, Security $security) : Response
    {
            $user = $security->getUser();
            $id = $user->getId();

            $PanierProduitRepository = $em->getRepository(PanierProduit::class);
            $paniers = $PanierProduitRepository->findBy(['panier'=> $id]);

            foreach( $paniers as $panier ){

                $em->remove($panier);

            }

            $em->flush();

            return $this->render('Panier/payer.html.twig');


    }

    #[Route('/ajouter',name: '_ajouter')]
    public function addcartAction( EntityManagerInterface $em) : Response
    {

        $produitRepository = $em->getRepository(Produit::class);
        $produits = $produitRepository->findAll();
        $args = array(
            'produits' => $produits,
        );
        return $this->render('Produit/addcart.html.twig', $args);
    }

    public function commandeAction (int $id,int $id_produit, Request $request, EntityManagerInterface $em) : Response
    {

        //$produitRepository = $em->getRepository(Produit::class);
        //$panierproduitRepository = $em->getRepository(PanierProduit::class);

        //$produit = $produitRepository->find($id_produit);
        //$panierproduit = $panierproduitRepository->findOneBy(['panier' => $id,'produit' => $id_produit]);

        //$max = $produit->getQuantite();
        //if (is_null($panierproduit)) $min = 0;
        //else $min = $panierproduit->getQuantite();
        $max = 10;
        $min = 5;

        $form = $this->createForm(CommType::class, null,['data' => ['max' => $max , 'min' => $min]]);
        $form->add('send', SubmitType::class,['label' => 'add produit']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() )
        {
            $this->addFlash('info', 'note ok');
            return $this->redirectToRoute('panier');

        }

        if ($form->isSubmitted()) {
            $this->addFlash('info', 'formulaire note incorrect');

        }

        $args = array(
            'myform' => $form->createView(),
        );

        return $this->render('Panier/formproduit.html.twig',$args);
    }


    public function formviewAction (int $id,int $id_produit, Request $request, EntityManagerInterface $em) : Response
    {
        $cun = 0;
        $produitRepository = $em->getRepository(Produit::class);
        $panierRepository = $em->getRepository(Panier::class);

        $produit = $produitRepository->find($id_produit);
        $panier  = $panierRepository->find($id);
        $panierproduit = new PanierProduit();

        $form = $this->createForm(PanierProduitType::class, $panierproduit);
        $form->add('send', SubmitType::class,['label' => 'add produit']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $quantite = $panierproduit->getQuantite();
            $panierproduit->setProduit($produit);
            $panierproduit->setPanier($panier);
            $panierproduit->setPrix($produit->getPrixUnitaire()*$quantite);
            $panier->addPanierProduit($panierproduit);
            $produit->addPanierProduit($panierproduit);

            $em->persist($panierproduit);
            $em->flush();
            $this->addFlash('info','SUCCESS');
            return $this->redirectToRoute('panier');
        }

        if ($form->isSubmitted())
            $this->addFlash('info','formulaire ajout produit incorrect');

        $args = array(
            'form' => $form->createView(),
        );

        $cun = $cun + 1;
        $this->addFlash('info','IIIIIICCCCCIIIIIII'. $cun);

        return $this->render('Panier/formproduit.html.twig',$args);
    }


}
