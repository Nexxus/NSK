<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class SecurityController extends Controller
{
    /**
     * @Route("/login", name="login")
     */
    public function loginAction(Request $request)
    {        
        if($request->getMethod() == "POST") {
            $username = $request->request->get('_username');
            $password = $request->request->get('_password');
        } 
        else {   
            return $this->render("security/login.html.twig", array(
                "error" => "",
            ));
        }
    }
}
