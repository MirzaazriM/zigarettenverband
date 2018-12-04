<?php

namespace App\Controller;

// set these headers so that system page is never cached and to avoid eventual misuse
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

use App\Model\DatabaseCommunicator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;



class SystemController extends AbstractController
{

    public function showSystemData (SessionInterface $session) {

        // get logged value from session
        $userLogged = $session->get('logged');

        // check if user is logged in
        if ($userLogged != 'yes') {
            // return to login page
            header("Location: /login");
            // exit current script
            exit();
        }

        // if user is logged into system  create Database Communicator object
        $dc = new DatabaseCommunicator();
        // call appropriete method for specific user data
        $systemData = $dc->getUserSystemData($session->get('email'));

        // set association id as session variable to use accross all pages
        // we need it when updating Association system basic info
        $session->set('id', $systemData['id']);

        // render template to show to the user and populate with appropriete data
        return $this->render('/system/system.html.twig', [
            'associationId' => $systemData['id'],
            'associationEmail' => $systemData['email'],
            'emailText' => $systemData['email_text'],
            'used' => $systemData['used_codes'],
            'unused' => $systemData['unused_codes'],
        ]);
    }
}