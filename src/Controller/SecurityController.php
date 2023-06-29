<?php

namespace App\Controller;

use App\Entity\Stock;
use App\form\StockType;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\RedirectResponse;


class SecurityController extends AbstractController
{
    /**
     * @Route("/", name="webcam_authentication")
     */
    public function webcamAuthentication(Request $request): Response
    {
        return $this->render('security/webcam_authentication.html.twig');
    }
    /**
     * @Route("/dashboard", name="dashboard")
     */
    public function dashboard(Request $request,EntityManagerInterface $entityManager): Response
    {
        if ($this->getUser() == null){
            return new RedirectResponse($this->generateUrl('webcam_authentication'));
        }
        $stocks = $entityManager->getRepository(Stock::class)->findAll();
        $user = $this->getUser();
        $selectedStock = $user->getStocks();
        $selected = array();
        foreach ($selectedStock as $value){
            $selected[] = $value->getId();
        }
        // Créer le formulaire
        $form = $this->createForm(StockType::class, null, [
            'stocks' => $stocks,
            'data' => ['stocks' => $selected],
        ]);

        // Traiter la soumission du formulaire
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Mettre à jour les stocks restants en fonction des cases cochées
            $data = $form->getData();
            $stockUser = $user->getStocks();
            foreach ($stockUser as $key => $value) {
                $user->removeStock($value);
                $value->setReste($value->getReste() - 1);
                $entityManager->persist($user);
                $entityManager->persist($value);

            }
            $entityManager->flush();

                foreach ($data as $key => $value) {
                if (strpos($key, 'stock_') === 0) {
                    $stockId = substr($key, strlen('stock_'));
                    $stock = $entityManager->getRepository(Stock::class)->find($stockId);
                    if ($value){
                        $stock->setReste($stock->getReste() + 1);
                        $user->addStock($stock);
                        $entityManager->persist($user);
                        $entityManager->persist($stock);
                    }

                }
            }
//                    foreach ($data['stocks'] as $stock) {
//                $stock = $entityManager->getRepository(Stock::class)->find($stock);
//                $stock->setReste($stock->getReste() + 1);
//                $user->addStock($stock);
//                $entityManager->persist($user);
//                $entityManager->persist($stock);
//            }
            $entityManager->flush();

            // Rediriger vers une autre page ou afficher un message de succès
        }

        return $this->render('security/dashboard.html.twig', [
            'form' => $form->createView(),
            'stocks' => $stocks,
        ]);
    }
    /**
     * @Route("/logout", name="logout")
     */
    public function logout(AuthenticationUtils $authenticationUtils)
    {
        // Déconnectez l'utilisateur
        $this->get('security.token_storage')->setToken(null);
        $this->get('session')->invalidate();

        // Redirection vers la page de webcam_authentication après la déconnexion
        return new RedirectResponse($this->generateUrl('webcam_authentication'));
    }
}