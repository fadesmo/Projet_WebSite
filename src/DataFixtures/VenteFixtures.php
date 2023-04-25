<?php

namespace App\DataFixtures;

use App\Entity\Panier;
use App\Entity\PanierProduit;
use App\Entity\Produit;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class VenteFixtures extends Fixture
{

    private ?UserPasswordHasherInterface $passwordHasher = null;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $em): void
    {
        /* =====================================================
         * = produit
         * =====================================================*/

        $produit1 = new Produit();
        $produit1
            ->setLibelle('BMW')
            ->setPrixUnitaire(1000)
            ->setQuantite(10);
        $em->persist($produit1);

        $produit2 = new Produit();
        $produit2
            ->setLibelle('MERCEDES')
            ->setPrixUnitaire(800)
            ->setQuantite(20);
        $em->persist($produit2);

        $produit3 = new Produit();
        $produit3
            ->setLibelle('AUDI')
            ->setPrixUnitaire(700)
            ->setQuantite(25);
        $em->persist($produit3);

        $produit4 = new Produit();
        $produit4
            ->setLibelle('FERRARI')
            ->setPrixUnitaire(10000)
            ->setQuantite(60);
        $em->persist($produit4);

        $produit5 = new Produit();
        $produit5
            ->setLibelle('NISSAN')
            ->setPrixUnitaire(50000)
            ->setQuantite(5);
        $em->persist($produit5);

        /* ===========================================================
         * = utilisateurs
         * ===========================================================*/

        $user1 = new User();
        $user1
            //->setPrenom(null)
            //->setNom(null)
            ->setLogin('sadmin')
            ->setRoles(['ROLE_SUPERADMIN'])
            ->setUserID(0);
        $hashedPassword = $this->passwordHasher->hashPassword($user1,'nimdas');
        $user1->setPassword($hashedPassword);

        $panier1 = new Panier();
        $panier1->setUser($user1);
        $user1->setPanier($panier1);
        $em->persist($user1);
        $em->persist($panier1);

        $user2 = new User();
        $user2
            //->setPrenom(null)
            //->setNom(null)
            ->setLogin('gilles')
            ->setRoles(['ROLE_ADMIN'])
            ->setUserID(1);
        $hashedPassword = $this->passwordHasher->hashPassword($user2,'sellig');
        $user2->setPassword($hashedPassword);

        $panier2 = new Panier();
        $panier2->setUser($user2);
        $user2->setPanier($panier2);
        $em->persist($user2);
        $em->persist($panier2);

        $user3 = new User();
        $user3
            //->setPrenom(null)
            //->setNom(null)
            ->setLogin('rita')
            ->setRoles(['ROLE_CLIENT'])
            ->setUserID(2);
        $hashedPassword = $this->passwordHasher->hashPassword($user3,'atir');
        $user3->setPassword($hashedPassword);

        $panier3 = new Panier();
        $panier3->setUser($user3);
        $user3->setPanier($panier3);
        $em->persist($user3);
        $em->persist($panier3);

        $user4 = new User();
        $user4
            //->setPrenom(null)
            //->setNom(null)
            ->setLogin('simon')
            ->setRoles(['ROLE_CLIENT'])
            ->setUserID(2);
        $hashedPassword = $this->passwordHasher->hashPassword($user4,'nomis');
        $user4->setPassword($hashedPassword);

        $panier4 = new Panier();
        $panier4->setUser($user4);
        $user4->setPanier($panier4);
        $em->persist($user4);
        $em->persist($panier4);

        /* ===========================================================
         * = panier
         * ===========================================================*/

        $panier1produit1 = new PanierProduit();
        $panier1produit1
            ->setQuantite(3)
            ->setPrix(($produit1->getPrixUnitaire())*3);
        $em->persist($panier1produit1);
        $panier3->addPanierProduit($panier1produit1);
        $produit1->addPanierProduit($panier1produit1);

        $panier1produit2 = new PanierProduit();
        $panier1produit2
            ->setQuantite(2)
            ->setPrix($produit2->getPrixUnitaire()*2);
        $em->persist($panier1produit2);
        $panier3->addPanierProduit($panier1produit2);
        $produit2->addPanierProduit($panier1produit2);

        $panier1produit3 = new PanierProduit();
        $panier1produit3
            ->setQuantite(2)
            ->setPrix($produit3->getPrixUnitaire()*2);
        $em->persist($panier1produit3);
        $panier3->addPanierProduit($panier1produit3);
        $produit3->addPanierProduit($panier1produit3);

        $panier1produit4 = new PanierProduit();
        $panier1produit4
            ->setQuantite(2)
            ->setPrix($produit4->getPrixUnitaire()*2);
        $em->persist($panier1produit4);
        $panier3->addPanierProduit($panier1produit4);
        $produit4->addPanierProduit($panier1produit4);


        $panier2produit5 = new PanierProduit();
        $panier2produit5
            ->setQuantite(2)
            ->setPrix($produit3->getPrixUnitaire()*2);
        $em->persist($panier2produit5);
        $panier4->addPanierProduit($panier2produit5);
        $produit3->addPanierProduit($panier2produit5);

        $panier2produit6 = new PanierProduit();
        $panier2produit6
            ->setQuantite(2)
            ->setPrix($produit4->getPrixUnitaire()*2);
        $em->persist($panier2produit6);
        $panier4->addPanierProduit($panier2produit6);
        $produit4->addPanierProduit($panier2produit6);



        $em->flush();
    }
}
