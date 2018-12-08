<?php

namespace App\Controller;

use App\Model\DatabaseCommunicator;
use App\Service\EmailService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class EmailController extends AbstractController
{

    /**
     * Check sending email to the user
     * Inject Request, SessionInterface, EmailService and DatabaseCommunicator services
     *
     * @param Request $request
     * @param SessionInterface $session
     * @param EmailService $emailHandler
     * @param DatabaseCommunicator $dc
     * @return JsonResponse
     */
    public function checkEmail(Request $request, SessionInterface $session, EmailService $emailHandler, DatabaseCommunicator $dc) {
        // first get email from user
        $data = json_decode($request->getContent(), true);
        $email = $data['email'];

        // set email regex pattern
        $emailRegexPattern = '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/';

        // create response object
        $response = new JsonResponse("Response");

        // check if email is right formatted
        if (preg_match($emailRegexPattern, $email)) {
            // get Association code from session if exists - this is necessary so that we take correct Gutscheincode from the database
            $associationCode = $session->get('code');

            // first check if Association code is valid
            $isAssociationCodeValid = false;
            if (!is_null($associationCode)) {
                $isAssociationCodeValid = $dc->checkCode($associationCode);
            }

            // set email parameteres
            $emailData = $emailHandler->setEmailParameters($isAssociationCodeValid, $associationCode);

            // check returned $emailData and make appropriate action
            if (!empty($emailData)) {
                // send email by calling EmailService sendEmail function and passing correct parameters
                $emailHandler->sendEmail(
                    $emailData['email'],
                    $email,
                    $emailData['email_text'],
                    $emailData['email_password'],
                    $emailData['name'],
                    $associationCode
                );

                // TODO clear session and check implications

                // set response status code
                $response->setStatusCode(200);

            } else {
                // set response status code
                $response->setStatusCode(200);
            }

        } else {
            // set response status code
            $response->setStatusCode(404);
        }

        // return response
        return $response;

    }
}