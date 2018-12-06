<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class LoginController extends AbstractController
{

    /**
     * Check if user is logged in, if no render appropriete template
     * Inject SessionInterface service
     *
     * @param SessionInterface $session
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function login(SessionInterface $session) {
        // first check if user is already logged in
        if ($session->get('logged') == 'yes') {
            // if yes redirect to system page
            header('Location: /system');
            // then exit current script
            exit();
        }

        // call appropriete template to render and set its necessary data
        return $this->render('/login/login.html.twig', []);
    }
}