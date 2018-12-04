<?php

namespace App\Controller;

use App\Model\DatabaseCommunicator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class LoginCheckerController
{
    public function checkUser(Request $request, SessionInterface $session) {

        // first get sended user credentials and decode them from JSON format
        $data = json_decode($request->getContent(), true);

        // if yes, first trim values and set patterns for checking their format
        $email = trim($data['email']);
        $password = trim($data['password']);
        $emailRegexPattern = '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/';
        $passwordRegexPassword = '/^[a-zA-Z0-9._]{8,}$/';
        $possibleErrorMessage = '';

        // check if formats of sended data suit to the patterns
        if (preg_match($emailRegexPattern, $email) && preg_match($passwordRegexPassword, $password)) {
            // call DatabaseCommunicator to check if credentials are valid
            $dc = new DatabaseCommunicator();
            $possibleErrorMessage = $dc->checkUserCredentials($email, $password);

            // check errorMessage, if it is empty credentials are valid - open session and redirect user to the /system page
            if ($possibleErrorMessage == '') {
                // if user logged succesfully open session and set needed session values
                $session->set('logged', 'yes');
                $session->set('email', $email);
            }

        } else {
            // if format of email or password isnt good set appropriete message to return
            $possibleErrorMessage = 'Invalid email or password format';
        }

        // return eventual error or empty string message
        return new JsonResponse($possibleErrorMessage);

    }
}