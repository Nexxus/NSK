<?php

namespace AdminBundle\Controller;

use AppBundle\Entity\User;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
* @Route("/admin")
*/
class DefaultController extends Controller
{
    /**
     * @Route("/", name="admin_index")
     
     */
    public function indexAction()
    {
        return $this->render('admin/index.html.twig');
    }
    
    /**
     * @Route("/user/index/", name="admin_user_index")
     * @Method({"GET", "POST"})
     */
    public function viewUserAction()
    {
        $em = $this->getDoctrine()->getManager();
        
        $userQuery = $em->getRepository("AppBundle:User")->findAll();
        
        return $this->render('admin/user/index.html.twig',
                ['users' => $userQuery]);
    }
    
    /**
     * @Route("/user/edit/{id}", name="admin_user_edit")
     * @Method({"GET", "POST"})
     */
    public function editUserAction(Request $request, User $user, $id) {
        $em = $this->getDoctrine()->getManager();
        
        $form = $this->createFormBuilder($user)
                ->add('username', TextType::class)
                ->add('firstname', TextType::class, ['required' => false])
                ->add('lastname', TextType::class, ['required' => false])
                ->add('email', EmailType::class, ['required' => false])
                ->add('role', ChoiceType::class, ['mapped' => false,
                    'choices' => [
                        'Admin' => 'ROLE_ADMIN',
                        'Copiatek' => 'ROLE_COPIA',
                        'User' => 'ROLE_USER'
                    ]])
                ->add('save', SubmitType::class, ['label' => 'Save'])
                ->getForm();
        
        $form->handleRequest($request);
        
        if($form->isSubmitted()) {
            $role = $form->get('role')->getData();
            $user->setRoles([$role]);
            
            $em->persist($user);
            $em->flush($user);
            
            return $this->redirectToRoute('admin_user_index');
        }
        
        return $this->render('admin/user/edit.html.twig',
                ['form' => $form->createView()]);
    }
    
    /**
     * Currently not used because of lack of password encrypting support
     * 
     * @Route("/user/new", name="admin_user_new")
     * @Method({"GET", "POST"})
     */
    public function newUserAction(Request $request, UserPasswordEncoderInterface $encoder) {
        $em = $this->getDoctrine()->getManager();
        
        $user = new User();
        
        $user->setUsername("New User");
        $user->setLocation("New Location");
        $user->setEmail("nvt@nvt.nl");
        $user->setPassword("0000");
        $user->setEnabled(1);
        
        $form = $this->createFormBuilder($user)
                ->add('username', TextType::class)
                ->add('firstname', TextType::class, ['required' => false])
                ->add('lastname', TextType::class, ['required' => false])
                ->add('location',  EntityType::class, array(
                    'class' => 'TrackBundle:Location',
                    'choice_label' => 'name'
                ))
                ->add('email', EmailType::class, ['required' => false])
                ->add('save', SubmitType::class, ['label' => 'Save'])
                ->getForm();
        
        $form->handleRequest($request);
        
        if($form->isSubmitted()) {
            // encrypt password
            $encoded = $encoder->encodePassword($user, $user->getPassword());
            $user->setPassword($encoded);
            
            $em->persist($user);
            $em->flush($user);
            
            return $this->redirectToRoute('admin_user_index');
        }
        
        return $this->render('admin/user/new.html.twig',
                ['form' => $form->createView()]);
    }
    
    /**
     * @Route("/sales", name="admin_sales")
     */
    public function viewSalesAction()
    {
        return $this->redirectToRoute('track_index', ['only' => 'sold']);
    }
}
