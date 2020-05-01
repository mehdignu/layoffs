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
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class UserController extends AbstractController
{

    /**
     * @Route("/user/profile",  name="user_profile")
     * @Method({"GET"})
     */
    public function userProfile(Request $request)
    {

        $userVerified = false;
        $token = $this->get('session')->get('token');

        if ($token) {

            $clientId = $_ENV['OAUTH_GOOGLE_ID'];
            // Get $id_token via HTTPS POST.
            $client = new \Google_Client(['client_id' => $clientId]);  // Specify the CLIENT_ID of the app that accesses the backend
            $payload = $client->verifyIdToken($token);

            if ($payload) {
                $userVerified = true;
                $userEmail = $payload['email'];
                $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['email' => $userEmail]);


                $form = $this->createFormBuilder($user)
                    ->add('picture', FileType::class, [
                        'label' => 'Profile picture ',

                        // unmapped means that this field is not associated to any entity property
                        'mapped' => false,

                        // make it optional so you don't have to re-upload the PDF file
                        // every time you edit the Product details
                        'required' => false,

                        // unmapped fields can't define their validation using annotations
                        // in the associated entity, so you can use the PHP constraint classes
                        'constraints' => [
                            new File([
                                'maxSize' => '5024k',
                                'mimeTypes' => [
                                    'image/jpeg',
                                    'image/png',
                                ],
                                'mimeTypesMessage' => 'Please upload a valid image',
                            ])
                        ],
                    ])
                    ->add('name', TextType::class, array('attr' => array('class' => 'form-control')))
                    ->add('city', ChoiceType::class, array(
                        'label' => 'Bundesland',

                        'attr' => array('class' => 'form-control'),

                        'choices' => array(
                            'Bundesländer' => array(
                                'Baden-Württemberg' => 'Baden-Württemberg',
                                'Bayern' => 'Bayern',
                                'Berlin' => 'Berlin',
                                'Brandenburg' => 'Brandenburg',
                                'Bremen' => 'Bremen',
                                'Hamburg' => 'Hamburg',
                                'Hessen' => 'Hessen',
                                'Mecklenburg-Vorpommern' => 'Mecklenburg-Vorpommern',
                                'Niedersachsen' => 'Niedersachsen',
                                'Nordrhein-Westfalen' => 'Nordrhein-Westfalen',
                                'Rheinland-Pfalz' => 'Rheinland-Pfalz',
                                'Saarland' => 'Saarland',
                                'Sachsen' => 'Sachsen',
                                'Sachsen-Anhalt' => 'Sachsen-Anhalt',
                                'Schleswig-Holstein' => 'Schleswig-Holstein',
                                'Thüringen' => 'Thüringen',
                            )
                        ),
                    ))
                    ->add('profession', TextType::class, array('attr' => array('class' => 'form-control'), 'required' => true))
                    ->add('email', EmailType::class, array('attr' => array('class' => 'form-control')))
                    ->add('linkedin', TextType::class, array('attr' => array('class' => 'form-control'), 'required' => false))
                    ->add('xing', TextType::class, array('attr' => array('class' => 'form-control'), 'required' => false))
                    ->add('phone', TextType::class, array('attr' => array('class' => 'form-control'), 'required' => false))
                    ->add('portfolio', TextType::class, array('attr' => array('class' => 'form-control'), 'required' => false))
                    ->add('about', TextareaType::class, array(
                        'attr' => array('class' => 'form-control', 'maxlength' => '255')
                    , 'required' => false
                    ))
                    ->add('isPublic', CheckboxType::class, array(
                        'label' => 'Make your profile public : ',

                        'attr' => array('class' => 'form-control')
                    , 'required' => false
                    ))
                    ->add('save', SubmitType::class, array(
                        'label' => 'Update',
                        'attr' => array('class' => 'btn btn-primary mt-3')
                    ))
                    ->getForm();

                $form->handleRequest($request);

                if ($form->isSubmitted() && $form->isValid()) {
                    $user = $form->getData();

                    /** @var UploadedFile $picture */
                    $picture = $form->get('picture')->getData();

                    // this condition is needed because the 'brochure' field is not required
                    // so the PDF file must be processed only when a file is uploaded
                    if ($picture) {
                        $originalFilename = pathinfo($picture->getClientOriginalName(), PATHINFO_FILENAME);
                        // this is needed to safely include the file name as part of the URL
                        $newFilename = uniqid() . '.' . $picture->guessExtension();

                        // Move the file to the directory where brochures are stored
                        try {
                            $picture->move(
                                $this->getParameter('pictures_directory'),
                                $newFilename
                            );
                        } catch (FileException $e) {
                            // ... handle exception if something happens during file upload
                        }


                        // updates the 'brochureFilename' property to store the PDF file name
                        // instead of its contents
                        $user->setPicture($newFilename);

                    }

                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->persist($user);
                    $entityManager->flush();

                    return $this->redirectToRoute('main_feed');
                }

                return $this->render('layoffs/create.html.twig', array(
                    'form' => $form->createView(),
                    'picture' => $user->getPicture(),
                    'name' => $user->getName(),
                    'loggedIn' => $userVerified
                ));
            }
        }

        if (!$userVerified)
            return $this->redirectToRoute('main_feed');

    }

    /**
     * @Route("/user/login",  name="user_login")
     * @Method({"POST"})
     */
    public function userLogin(Request $request)
    {
        $token = $request->request->get('token');


        $cookies = $request->cookies;

        // check cookie consent
        $consent = false;
        if ($cookies->has('cookie_consent_user_accepted')) {
            $consent = $cookies->get('cookie_consent_user_accepted');
        }

        if (!empty($token) && ($consent == true)) {
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
                    $user->setIsPublic(false);
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

        $json_data = array(
            'data' => 100,
            'msg' => "Success"
        );
        $response = new Response(json_encode($json_data));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/user/logout",  name="user_logout")
     * @Method({"POST"})
     */
    public function userLogout(Request $request)
    {
        $token = $request->request->get('token');
        $this->get('session')->remove('token');

        $clientId = $_ENV['OAUTH_GOOGLE_ID'];
        $client = new \Google_Client(['client_id' => $clientId]);  // Specify the CLIENT_ID of the app that accesses the backend
        $client->revokeToken($token);

        return $this->redirectToRoute('main_feed');

    }

}