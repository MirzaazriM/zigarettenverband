<?php

namespace App\Controller;

use App\Model\DatabaseCommunicator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class LoginCheckerController
{

    /**
     * Check user credentials and return appropriete response if user is valid
     * Inject Request, SessionInterface and DatabaseCommunicator services
     *
     * @param Request $request
     * @param SessionInterface $session
     * @param DatabaseCommunicator $dc
     * @return JsonResponse
     */
    public function checkUser(Request $request, SessionInterface $session, DatabaseCommunicator $dc) {
        // first get sended user credentials and decode them from JSON format
        $data = json_decode($request->getContent(), true);

        // first trim values and set patterns for checking their format
        $email = trim($data['email']);
        $password = trim($data['password']);
        $emailRegexPattern = '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/';
        $passwordRegexPassword = '/^[a-zA-Z0-9._]{8,}$/';

        // create response object
        $response = new JsonResponse();

        // check if formats of sended data suit to the patterns
        if (preg_match($emailRegexPattern, $email) && preg_match($passwordRegexPassword, $password)) {
            // call DatabaseCommunicator to check if credentials are valid and return appropriate status code
            $response->setStatusCode($dc->checkUserCredentials($email, $password));

            // check errorMessage, if it is empty credentials are valid
            if ($response->getStatusCode() == 200) {
                // if user logged succesfully open session and set needed session values
                $session->set('logged', getenv("LOGGED_VALUE"));
                $session->set('email', $email);
            }

        } else {
            // if format of email or password isnt good set appropriete message to return
            $response->setStatusCode(404);
        }

        // return response
        return $response;
    }
}