<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

class LogoutController
{

    /**
     * Logout user from session and redirect to /login page
     * Inject SessionInterface service
     *
     * @param SessionInterface $session
     */
    public function logout(SessionInterface $session) {
        // get logged variable
        $userLogged = $session->get('logged');

        // check if user is logged in
        if (isset($userLogged)) {
            // unset logged variable
            $session->set('logged', null);

            // destroy session
            $session->clear();

            // redirect to login page after logout
            header("Location: /login");

            // exit current script
            exit();
        }
    }

}