<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Session\Session;

class UserController extends AbstractController
{

    /**
     * @Route("/user/login",  name="user_login")
     * @Method({"POST"})
     */
    public function userLogin(Request $request)
    {
        $token = $request->request->get('token');

        if (!empty($token)) {
            $clientId = $_ENV['OAUTH_GOOGLE_ID'];

            // Get $id_token via HTTPS POST.
            $client = new \Google_Client(['client_id' => $clientId]);  // Specify the CLIENT_ID of the app that accesses the backend
            $payload = $client->verifyIdToken($token);

            if ($payload) {

                $userFullName = $payload['name'];
                $userEmail = $payload['email'];
                $userExists = $this->getDoctrine()->getRepository(User::class)->findOneBy(['email' => $userEmail]);

                if (!empty($userEmail) && !empty($userFullName) && empty($userExists)) {

                    //save the new user
                    $user = new User();
                    $user->setName($userFullName);
                    $user->setEmail($userEmail);
                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->persist($user);
                    $entityManager->flush();
                }

                //save token into the session
                $session = new Session();
                $session->start();
                $session->set('token', $token);
                
            }

        }
        return $this->redirectToRoute('main_feed');
    }

    /**
     * @Route("/user/logout",  name="user_logout")
     * @Method({"POST"})
     */
    public function userLogout(Request $request){
        $token = $request->request->get('token');
        $token = $this->get('session')->clear();

        $clientId = $_ENV['OAUTH_GOOGLE_ID'];
        $client = new \Google_Client(['client_id' => $clientId]);  // Specify the CLIENT_ID of the app that accesses the backend
        $client->revokeToken($token);

        return $this->redirectToRoute('main_feed');

    }

}