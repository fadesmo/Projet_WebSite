<?php

namespace App\Controller;

use App\Entity\Panier;
use App\Entity\PanierProduit;
use App\Entity\Produit;
use App\Entity\User;
use App\Form\CommType;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;


#[Route('', name: 'store')]
class StoreController extends AbstractController
{
    #[Route('', name: '_accueil')]
    public function accueilAction (): Response
    {
        dump($this->getUser());
        return $this->render('Accueil/accueil.html.twig');
    }
    #[Route('/clients/list',name: '_clients_list')]
    public function listclientAction(EntityManagerInterface $em): Response
    {
        $userRepository = $em->getRepository(User::class);
        $users = $userRepository->findBy(['userID' => 2] ); # userID = 2 réservés pour les clients
        $args = array(
            'users' => $users,
        );
            return $this->render('Admin/listClient.html.twig',$args);
    }

    #[Route('/admins/list',name: '_admins_list')]
    public function listadminAction(EntityManagerInterface $em): Response
    {
        $userRepository = $em->getRepository(User::class);
        $users = $userRepository->findBy(['userID' => 1]); # userID = 1 réservés pour les admins
        $args = array(
            'users' => $users,
        );
        dump($users);
        return $this->render('Admin/listAdmin.html.twig',$args);
    }

    #[Route('/register', name: '_register')]
    public function registerAction(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $em )
    {
        $user = new User();
        $form = $this->createForm(UserType::class,$user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() ) {

            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );

            $user->setRoles(['ROLE_CLIENT']); # on cree que un compte client
            $user->setUserID(2);

            $panier = new Panier(); # on attribue un panier a ce nouveau client ( un client sans panier n'est pas un client )
            $panier->setUser($user);
            $user->setPanier($panier);

            $em->persist($user);
            $em->persist($panier);
            $em->flush();

            return $this->redirectToRoute('security_login');
        }

        return $this->render('Security/register.html.twig', [
            'Form' => $form->createView(),
    ]);
    }

    #[Route('/modifie/profile', name: '_modifie_profile')]
    public function modifieProfileAction(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $em, Security $security ) : Response
    {
        $userId = $security->getUser();
        $id = $userId->getId();
        dump($id);

        $UserRespo = $em->getRepository(User::class);
        $user = $UserRespo->find($id);
        $form = $this->createForm(UserType::class,$user);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $user->setPassword(                         //possibilité de pas changer le mot de passe
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );

            $em->flush();

            $this->addFlash('info', 'Profil modifier avec success');
            return $this->redirectToRoute('store_accueil');
        }
        if($form->isSubmitted()){
            $this->addFlash('info', 'Modification Invalide');
        }



        return $this->render('Security/modifieprofile.html.twig', [
            'Form' => $form->createView(),
        ]);
    }
    #[Route('/supprimer/client/{id}', name: '_supprimer_client')]
    public function supprimerClientAction(int $id, EntityManagerInterface $em) : Response
    {
        $UserRepo = $em->getRepository(User::class);
        $user = $UserRepo->find($id);

        $PanierProduitRepository = $em->getRepository(PanierProduit::class);
        $paniers = $PanierProduitRepository->findBy(['panier'=> $id]);

        foreach( $paniers as $panier ){
            $quantite = $panier->getQuantite();
            $produit = $panier->getProduit();
            $produitStock = $produit->getQuantite();
            $produit->setQuantite($quantite + $produitStock);

            $em->remove($panier);

    }
        $em->remove($user);
        $em->flush();

        return $this->redirectToRoute('store_clients_list');

    }

    #[Route('/supprimer/admin/{id}', name: '_supprimer_admin')]
    public function supprimerAdminAction(int $id, EntityManagerInterface $em) : Response
    {
        $UserRepo = $em->getRepository(User::class);
        $user = $UserRepo->find($id);

        $PanierProduitRepository = $em->getRepository(PanierProduit::class);
        $paniers = $PanierProduitRepository->findBy(['panier'=> $id]);

        foreach( $paniers as $panier ){
            $quantite = $panier->getQuantite();
            $produit = $panier->getProduit();
            $produitStock = $produit->getQuantite();
            $produit->setQuantite($quantite + $produitStock);

            $em->remove($panier);

        }
        $em->remove($user);
        $em->flush();

        return $this->redirectToRoute('store_admins_list');

    }

    #[Route('/add/admin', name: '_add_admin')]
    public function addAdminAction(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $em )
    {

        $user = new User();
        $form = $this->createForm(UserType::class,$user);
        $form->handleRequest($request);
        dump($user);
        if ($form->isSubmitted() && $form->isValid() ) {
            dump($user);
            $user->setDateNaissance($form->get('dateNaissance')->getData() ?? null);


            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );

            $user->setRoles(['ROLE_ADMIN']); # on cree que un compte admin
            $user->setUserID(1);

            $panier = new Panier(); # on attribue un panier a ce nouveau admin ( un admin est toujours un client )
            $panier->setUser($user);
            $user->setPanier($panier);

            $em->persist($user);
            $em->persist($panier);
            $em->flush();

            return $this->redirectToRoute('store_admins_list');
        }

        return $this->render('Security/addAdmin.html.twig', [
            'Form' => $form->createView(),
        ]);
    }



}
