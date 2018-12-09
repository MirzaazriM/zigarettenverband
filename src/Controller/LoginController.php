<?php

namespace App\Controller;

use App\Service\AuthorizationCheckerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class LoginController extends AbstractController
{

    /**
     * Check if user is logged in, if no render appropriate template
     * Inject AuthorizationCheckerService service
     *
     * @param AuthorizationCheckerService $authChecker
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function login(AuthorizationCheckerService $authChecker) {
        // check if user is logged in, if yes redirect to /system page
        $authChecker->checkAuthorization("/login");

        // if user is not logged call appropriate template to render and set its necessary data
        return $this->render('/login/login.html.twig', []);
    }
}