<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

class AuthorizationCheckerService
{

    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }


    /**
     * Check if user is logged in e.g. authorized for specific action
     */
    public function checkAuthorization(string $route = null) {
        // get logged value from session
        $userLogged = $this->session->get('logged');

        // check if $route is null (every route except /login)
        if (is_null($route)) {
            // if user is not logged in
            if ($userLogged != getenv("LOGGED_VALUE")) {
                // redirect to the login page
                header("Location: /login");

                // exit current script
                exit();
            }

        } else {
            // if user is looged in
            if ($userLogged == getenv("LOGGED_VALUE")) {
                // redirect to system page
                header('Location: /system');
                // then exit current script
                exit();
            }

        }

    }
}