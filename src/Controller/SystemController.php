<?php

namespace App\Controller;

// set these headers so that system page is never cached and to avoid eventual misuse
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;



class SystemController extends AbstractController
{

    public function showSystemData (SessionInterface $session) {

        // get logged value from session
        $userLogged = $session->get('logged');

        // if user is not logged in
        if ($userLogged != 'yes') {
            // return to login page
            header("Location: /login");
            // exit current script
            exit();
        }

        // if user is logged into system call database for appropriete user data

        // render template to show to the user and populate with appropriete data
        return $this->render('/system/system.html.twig', []);
    }
}