<?php

namespace App\Controller;
use App\Repository\StockRepository;
use App\Service\KairosService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Entity\User;
use Symfony\Component\Routing\Annotation\Route;
use Jenssegers\ImageHash\ImageHash;
use Jenssegers\ImageHash\Implementations\DifferenceHash;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserController extends AbstractController
{

    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }
    /**
     * @Route("/authenticate", name="authenticate_user", methods={"POST"})
     */
    public function authenticateUser(Request $request): Response
    {
        $imageData = $request->request->get('image');
        $imageData = str_replace('data:image/jpeg;base64,', '', $imageData); // Supprimez la partie "data:image/jpeg;base64," de la chaîne
        $imageData = str_replace(' ', '+', $imageData); // Remplacez les espaces par des "+" si nécessaire

        $fileName = uniqid('image_') . '.jpg';
        $fileLocation = $this->getParameter('kernel.project_dir') . '/public/tmp/' . $fileName;
        file_put_contents($fileLocation, base64_decode($imageData));

        // Effectuez la comparaison de l'image avec les images d'utilisateurs existants
        $users = $this->getDoctrine()->getRepository(User::class)->findAll();

        $publicDirectory = $this->getParameter('kernel.project_dir') . '/public';

        foreach ($users as $user) {
            $userImagePath = $publicDirectory . '/user_images/' . $user->getImageData(); // Chemin vers les images des utilisateurs
           $result = json_decode($this->compare($fileLocation, $userImagePath),true);
           if (isset($result['match']) && $result['match']) {
                // L'image du visage correspond à l'utilisateur
                // Authentifiez l'utilisateur
               $token = new UsernamePasswordToken($user,  'main', $user->getRoles());
               $this->tokenStorage->setToken($token);

                // Redirigez l'utilisateur vers une page après l'authentification réussie
                return new Response(200);

            }


        }
        return new Response(404);

    }
    /**
     * @Route("/api/stock", name="api_stock", methods={"GET"})
     */
    public function getStocks(StockRepository $stockRepository): Response
    {
        $stocks = $stockRepository->findAll();

        $data = [];
        foreach ($stocks as $stock) {
            $data[] = [
                'id' => $stock->getId(),
                'libelle' => $stock->getLibelle(),
                'quantite' => $stock->getStock(),
            ];
        }

        return new JsonResponse($data);
    }
    function compare($a, $b)
    {

        $output = shell_exec('docker run -it -d macgyvertechnology/face-comparison-model:3');
        $output = preg_replace('/[^0-9a-z]/', '', $output);

// write images to container
        exec('docker cp ' . $a . ' ' . $output . ':/macgyver/temp/known.jpg');
        exec('docker cp ' . $b . ' ' . $output . ':/macgyver/temp/test.jpg');

// Run main file
        $probability = shell_exec('docker exec -t ' . $output . ' bin/bash -c "python3 /macgyver/main"');
// Stop the Container
        exec("docker stop " . $output);

// Delete the Container
        exec("docker rm " . $output);

        return $probability;
    }

}
