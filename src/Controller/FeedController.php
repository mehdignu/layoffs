<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use League\OAuth2\Client\Provider\Google;

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

class FeedController extends AbstractController
{


    /**
     * @Route("/",  name="main_feed")
     * @Method({"GET"})
     */
    public function index()
    {

        $feed = $this->getDoctrine()->getRepository(User::class)->findAll();

        $token = $this->get('session')->get('token');
        $loggedIn = false;


        if ($token) {
            $clientId = $_ENV['OAUTH_GOOGLE_ID'];
            $client = new \Google_Client(['client_id' => $clientId]);  // Specify the CLIENT_ID of the app that accesses the backend
            $payload = $client->verifyIdToken($token);
            if ($payload) {
                $loggedIn = true;
            }
        }

        return $this->render('feed/index.html.twig', array('feeds' => $feed, 'loggedIn' => $loggedIn, 'token' => $token));
        
    }


    /**
     * @Route("/privacy",  name="privacy")
     * @Method({"GET"})
     */
    public function privacy()
    {
        $token = $this->get('session')->get('token');
        $loggedIn = false;


        if ($token) {
            $clientId = $_ENV['OAUTH_GOOGLE_ID'];
            $client = new \Google_Client(['client_id' => $clientId]);  // Specify the CLIENT_ID of the app that accesses the backend
            $payload = $client->verifyIdToken($token);
            if ($payload) {
                $loggedIn = true;
            }
        }

        return $this->render('static/privacy.html.twig', array('loggedIn' => $loggedIn));

    }

    /**
     * @Route("/impressum",  name="impressum")
     * @Method({"GET"})
     */
    public function impressum()
    {

        return $this->render('static/impressum.html.twig');

    }

}