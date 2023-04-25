<?php

namespace App\Controller;

use App\Entity\Panier;
use App\Entity\PanierProduit;
use App\Entity\Produit;
use App\Form\CommType;
use App\Form\ProduitType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\SecurityBundle\Security;

#[Route('/produit', name: 'produit')]
class ProduitController extends AbstractController
{
    #[Route('/', name: '')]
    public function indexAction(): Response
    {
        return $this->redirectToRoute('produit_list', ['page' => 1]);
    }

    #[Route(
        '/list/{postId}',
        name: '_list',
        requirements: ['postId' => '[1-9]\d*'],
        defaults: [ 'postId' => 0 ],        // la valeur par défaut ne respecte pas les contraintes
    )]
    #[IsGranted('ROLE_CLIENT', message: 'No access for your Role!', statusCode: 404)]
    public function listAction(int $postId,Request $request, EntityManagerInterface $em, Security $security): Response
    {

        $user = $security->getUser();
        $id = $user->getId();
        dump($id);


        $produitRepository = $em->getRepository(Produit::class);
        $panierproduitRepository = $em->getRepository(PanierProduit::class);
        $panierRepository = $em->getRepository(Panier::class);
        $produitpanier = $panierproduitRepository->findBy(['panier' => $id]);
        $produits = $produitRepository->findAll();
        $forms = [];
        $mins = [];
        $maxs = [];

        foreach ($produits as $produit) {
            $panier = $panierRepository->find($id);
            $panierproduit = $panierproduitRepository->findOneBy(['panier' => $panier, 'produit' => $produit]);
            $prodId = $produit->getId();

            $max = $produit->getQuantite();
            $prix = $produit->getPrixUnitaire();
            if (is_null($panierproduit)) $min = 0;
            else $min = $panierproduit->getQuantite();


                $form = $this->createForm(CommType::class, null, ['data' => ['max' => $max, 'min' => $min]]);

            $form->add('send', SubmitType::class, ['label' => 'Commander']);

            $form->handleRequest($request);
            $forms[$produit->getId()] = $form->createView();

            // ce tableau ce sera utile dans le utile pour afficher le choix et button commande
            $maxs[$produit->getId()] = $max;
            $mins[$produit->getId()] = $min;

            if ($form->isSubmitted() && $form->isValid() && $prodId === $postId) {


                $choix = $form->get('choix')->getData();

                if (!is_null($panierproduit)) {
                    $panierproduit->setQuantite($min + $choix);
                    $quantite = $panierproduit->getQuantite();
                    $panierproduit->setPrix($prix * $quantite);
                    if ($quantite === 0) $em->remove($panierproduit);
                } else {
                    if ($choix > 0) {
                        $panierproduit1 = new PanierProduit();
                        $panierproduit1
                            ->setQuantite($choix)
                            ->setPrix($prix * $choix);
                        $em->persist($panierproduit1);
                        $produit->addPanierProduit($panierproduit1);
                        $panier->addPanierProduit($panierproduit1);
                    }
                }
                $produit->setQuantite($max - $choix);
                $em->flush();

                $this->addFlash('info', 'SUBMITTED AND VALID' . $choix . 'AND' . $produit->getId());
                return $this->redirectToRoute('panier');

            }
            if ($form->isSubmitted() && $prodId === $postId)

                $this->addFlash('info', 'JUST SUBMITTED');

        //}
        }

        $args = array(
            'produits' => $produits,
            'mins' =>$mins,
            'maxs' =>$maxs,
            'forms' => $forms,
        );
        return $this->render('Produit/list.html.twig', $args);
    }

    #[Route(
        '/view/{id}',
        name: '_view',
        requirements: ['id' => '[1-9]\d*'],
    )]
    #[IsGranted('ROLE_CLIENT', message: 'No access for your Role!', statusCode: 404)]
    public function viewAction(int $id, EntityManagerInterface $em): Response
    {
        $produitRepository = $em->getRepository(Produit::class);
        $produit = $produitRepository->find($id);

        if (is_null($produit))
        {
            $this->addFlash('info', 'view : produit ' . $id . ' inexistant');
            return $this->redirectToRoute('produit_list');
        }

        $args = array(
            'produit' => $produit,
        );
        return $this->render('Produit/view.html.twig', $args);
    }





    #[Route(
        '/edit/{id}',
        name: '_edit',
        requirements: ['id' => '[1-9]\d*'],
    )]
    #[IsGranted('ROLE_ADMIN', message: 'No access for your Role!', statusCode: 404)]
    public function editProduitAction(int $id, EntityManagerInterface $em, Request $request): Response
    {
        $ProduitRepository = $em->getRepository(Produit::class);
        $Produit = $ProduitRepository->find($id);

        $form = $this->createForm(ProduitType::class,$Produit);
        $form->add('send', SubmitType::class,['label'=>'Modifie Produit']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $em->flush();
            $this->addFlash('info', 'modification réussi');
            return $this->redirectToRoute('produit_view',['id' => $id]);
        }
        if ($form->isSubmitted())
            $this->addFlash('info','modification incorrect');

        $args = array(
            'myform' => $form->createView(),
            'produit' => $Produit,
        );

        return $this->render('Produit/modifie.html.twig',$args);
    }

    #[Route(
        '/delete/{id}',
        name: '_delete',
        requirements: ['id' => '[1-9]\d*'],
    )]
    #[IsGranted('ROLE_ADMIN', message: 'No access for your Role!', statusCode: 404)]
    public function deleteAction(int $id, EntityManagerInterface $em): Response
    {
        $produitRepository = $em->getRepository(Produit::class);
        $produit = $produitRepository->find($id);

        if (is_null($produit))
            throw new NotFoundHttpException('erreur suppression produit ' . $id);

        $em->remove($produit);
        $em->flush();
        $this->addFlash('info', 'suppression produit ' . $id . ' réussie');

        return $this->redirectToRoute('produit_list');
    }
    #[Route('/add', name: '_add')]
    #[IsGranted('ROLE_ADMIN', message: 'No access for your Role!', statusCode: 404)]
    public function produitAddAction (EntityManagerInterface $em, Request $request ): Response
    {
        $produit = new Produit();

        $form = $this->createForm(ProduitType::class,$produit);
        $form->add('send',SubmitType::class,['label'=>'add produit']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $em->persist($produit);
            $em->flush();
            $this->addFlash('info','ajout produit réussi');
            return $this->redirectToRoute('produit_view',['id' => $produit->getId()]);

        }
        if ($form->isSubmitted())
            $this->addFlash('info','formulaire ajout produit incorrect');

        $args = array(
            'myform' => $form->createView(),
        );

        return $this->render('Produit/produit_add.html.twig',$args);
    }






    #[Route(
        '/adderr',
        name: '_adderr',
    )]
    #[IsGranted('ROLE_ADMIN', message: 'No access for your Role!', statusCode: 404)]
    public function addAction(): Response
    {
        $this->addFlash('info', 'échec ajout produit');
        return $this->redirectToRoute('produit_view', ['id' => 3]);
    }
}
