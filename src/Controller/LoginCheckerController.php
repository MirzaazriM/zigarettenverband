<?php

namespace App\Controller;

use App\Model\DatabaseCommunicator;
use App\Service\RegexCheckerService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class LoginCheckerController
{

    /**
     * Check user credentials and return appropriete response if user is valid
     * Inject Request, SessionInterface, RegexCheckerService and DatabaseCommunicator services
     *
     * @param Request $request
     * @param SessionInterface $session
     * @param DatabaseCommunicator $dc
     * @param RegexCheckerService $regex
     * @return JsonResponse
     */
    public function checkUser(Request $request, SessionInterface $session, DatabaseCommunicator $dc, RegexCheckerService $regex) {
        // first get user credentials and decode them from JSON format
        $data = json_decode($request->getContent(), true);

        // trim values and set patterns for checking their format
        $email = trim($data['email']);
        $password = trim($data['password']);
        $emailRegexPattern = '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/';
        $passwordRegexPassword = '/^[a-zA-Z0-9._]{8,}$/';

        // create response object
        $response = new JsonResponse();

        // check if formats of sended data suit to the patterns
        if ($regex->checkRegex($emailRegexPattern, $email) && $regex->checkRegex($passwordRegexPassword, $password)) {
            // call DatabaseCommunicator to check if credentials are valid and return appropriate status code
            $response->setStatusCode($dc->checkUserCredentials($email, $password));

            // check errorMessage, if it is empty, credentials are valid
            if ($response->getStatusCode() == 200) {
                // if user logged successfully open session and set needed session values
                $session->set('logged', getenv("LOGGED_VALUE"));
                $session->set('email', $email);
            }

        } else {
            // if format of email or password isn't good set appropriate message to return
            $response->setStatusCode(404);
        }

        // return response
        return $response;
    }
}