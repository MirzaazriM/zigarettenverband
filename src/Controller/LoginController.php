<?php

namespace App\Controller;

use App\Model\DatabaseCommunicator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class LoginController extends AbstractController
{

    public function login(SessionInterface $session) {

        // first check if user is already logged in
        if ($session->get('logged') == 'yes') {
            // if yes redirect to system page
            header('Location: /system');
            // then exit current script
            exit();
        }

        // second check request method to decide if user wants to log in into system
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            // if yes, first trim values and set patterns for checking their format
            $email = trim($_POST['email']);
            $password = trim($_POST['password']);
            $emailRegexPattern = '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/';
            $passwordRegexPassword = '/^[a-zA-Z0-9]{8,}$/';
            $possibleErrorMessage = '';

            // check if formats of sended data suit to the patterns
            if (preg_match($emailRegexPattern, $email) && preg_match($passwordRegexPassword, $password)) {
                // call DatabaseCommunicator to check if credentials are valid
                $dc = new DatabaseCommunicator();
                $possibleErrorMessage = $dc->chechkUserCredentials($email, $password);

                // check errorMessage, if it is empty credentials are valid - open session and redirect user to the /system page
                if ($possibleErrorMessage == '') {
                    $session->set('logged', 'yes');
                    header('Location: /system');
                    exit;
                }

            } else {
                $possibleErrorMessage = 'Invalid email or password format';
            }

        }

        // call appropriete template to render and set its necessary data
        return $this->render('/login/login.html.twig', [
            'error' => isset($possibleErrorMessage) ? (' * ' . $possibleErrorMessage) : '',
            'email' => isset($_POST['email']) ? $_POST['email'] : '',
            'password' => isset($_POST['password']) ? $_POST['password'] : ''
        ]);
    }
}