<?php

namespace App\Controller;

// set these headers so that system page is never cached and to avoid eventual misuse
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

use App\Model\DatabaseCommunicator;
use App\Service\AuthorizationCheckerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class SystemController extends AbstractController
{

    /**
     * Show system page to the user after user successfully logged in
     * Inject SessionInterface, AuthorizationCheckerService and DatabaseCommunicator services
     *
     * @param SessionInterface $session
     * @param DatabaseCommunicator $dc
     * @param AuthorizationCheckerService $authChecker
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showSystemData (SessionInterface $session, DatabaseCommunicator $dc, AuthorizationCheckerService $authChecker) {
        // check if user is logged in (authorized for making this request)
        $authChecker->checkAuthorization();

        // if yes, call appropriate method from DC for specific user data
        $systemData = $dc->getUserSystemData($session->get('email'));

        // check if systemData is set
        if (!empty($systemData)) {
            // if yes, first set association id as session variable to use across all pages, we need it when updating Association system basic info
            $session->set('id', $systemData['id']);

            // then render template to show to the user and fill with appropriate data
            return $this->render('/system/system.html.twig', [
                'name' => $systemData['name'],
                'associationId' => $systemData['id'],
                'associationEmail' => $systemData['email'],
                'emailText' => $systemData['email_text'],
                'used' => $systemData['used_codes'],
                'unused' => $systemData['unused_codes'],
            ]);

        } else {
            // if no, render appropriate error template
            return $this->render('/error_pages/error_default.html.twig', [
                "message" => "Couldnt fetch data"
            ]);
        }

    }
}