<?php

namespace App\Controller;

use App\Model\DatabaseCommunicator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class LoginController extends AbstractController
{

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