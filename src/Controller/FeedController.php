<?php

namespace App\Controller;

use App\Entity\Layoff;
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

class FeedController extends AbstractController
{

    /**
     * @Route("/",  name="main_feed")
     * @Method({"GET"})
     */
    public function index()
    {
        $feed = $this->getDoctrine()->getRepository(Layoff::class)->findAll();
        return $this->render('feed/index.html.twig', array('articles' => $feed));
    }

    /**
     * @Route("/new", name="create_layoff")
     * Method({"GET", "POST"})
     */
    public function create(Request $request)
    {
        $article = new Layoff();

        $form = $this->createFormBuilder($article)
            ->add('picture', FileType::class,array('attr' => array('class'=>'custom-file'),'required' => false), [
                'label' => 'Profile picture',

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
            ->add('email', EmailType::class, array('attr' => array('class' => 'form-control')))
            ->add('linkedin', TextType::class, array('attr' => array('class' => 'form-control'),'required' => false))
            ->add('xing', TextType::class, array('attr' => array('class' => 'form-control'),'required' => false))
            ->add('phone', TextType::class, array('attr' => array('class' => 'form-control'),'required' => false))
            ->add('portfolio', TextType::class, array('attr' => array('class' => 'form-control'),'required' => false))

            ->add('about', TextareaType::class, array(
                'attr' => array('class' => 'form-control')
                 ,'required' => false
            ))
            ->add('save', SubmitType::class, array(
                'label' => 'Create',
                'attr' => array('class' => 'btn btn-primary mt-3')
            ))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $article = $form->getData();

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($article);
            $entityManager->flush();

            return $this->redirectToRoute('main_feed');
        }

        return $this->render('layoffs/create.html.twig', array(
            'form' => $form->createView()
        ));
    }

//        /**
//         * @Route("/feed/save")
//         * @Method({"GET"})
//         */
//        public function save(){
//            $em = $this->getDoctrine()->getManager();
//
//            $article = new Feed();
//            $article->setTitle('article boo');
//            $article->setBody('this is article boo body');
//
//            $em->persist($article);
//            $em->flush();
//
//            return new Response('saved an article with id of ' . $article->getId());
//        }

}