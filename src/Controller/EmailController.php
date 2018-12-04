<?php

namespace App\Controller;

use App\Service\EmailService;
use PHPMailer\PHPMailer\PHPMailer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class EmailController extends AbstractController
{

    public function checkEmail(Request $request, SessionInterface $session) {

        // get email from user
        $data = json_decode($request->getContent(), true);
        $email = $data['email'];

        // check if email is right formatted
        $emailRegexPattern = '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/';
        if (preg_match($emailRegexPattern, $email)) {
            // send email with code to the user
            // create emailService object
            $email = new EmailService(
                'mirzao@smartlab.ba',
                'hqivmzmqjodlwahv',
                $email,
                'Zigaretten Gutscheincode',
                'You got Gutscheincode.',
                new PHPMailer(true),
                $session->get('code')
            );
            $email->sendEmail();

            // return email sended
            return new JsonResponse('Email sended');

        } else {
            return new JsonResponse("Email not valid, please check it and try again.");
        }

    }
}