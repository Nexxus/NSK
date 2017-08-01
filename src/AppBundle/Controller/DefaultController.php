<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use AppBundle\Entity\User;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="home")
     */
    public function indexAction(Request $request)
    {
        return $this->render('base.html.twig');
    }

    /**
     * @Route("/login", name="login")
     */
    public function loginAction(Request $request)
    {
        // get auth utils object
        $authUtils = $this->get('security.authentication_utils');
        
        $error = $authUtils->getLastAuthenticationError();
        
        // last username entered by the user
        //$lastUsername = $authUtils->getLastUsername();

        
        /*$form = $this->createFormBuilder()
                ->add('_username', TextType::class)
                ->add('_password', PasswordType::class)
                ->add('save', SubmitType::class)
                ->getForm();
        
        $form->handleRequest($request);
        
        if($form->isSubmitted())
        {
            $user = $form->getData();
        }*/
        
        return $this->render('security/login.html.twig', array(
            //"form" => $form->createView(),
            "error" => $error,
        ));
        
        
    }
}
