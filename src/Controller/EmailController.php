<?php

namespace App\Controller;

use App\Model\DatabaseCommunicator;
use App\Service\EmailService;
use PHPMailer\PHPMailer\PHPMailer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Yaml\Yaml;

class EmailController extends AbstractController
{

    public function checkEmail(Request $request, SessionInterface $session) {

        // get email from user
        $data = json_decode($request->getContent(), true);
        $email = $data['email'];

        // check if email is right formatted
        $emailRegexPattern = '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/';
        if (preg_match($emailRegexPattern, $email)) {

            // TODO get Gutscheincode from specific Association, its email, password hash, email subject and email text
            $associationCode = $session->get('code');

            $emailData = [];
            if (!is_null($associationCode)) {
                $dc = new DatabaseCommunicator();
                $emailData = $dc->getEmailData($associationCode);
            } else {
                // read default email data
                $yaml = Yaml::parse(file_get_contents('../config/configuration/developer-info.yml'));
                $yamlData = $yaml['info'];
                // set email parametars into $emailData array
                $emailData['email'] = $yamlData['email'];
                $emailData['email_password'] = $yamlData['password'];
                $emailData['email_text'] = file_get_contents('../uploaded_resources/thanks_email.txt');
                $emailData['name'] = $yamlData['name'];
            }

            // create emailService object
            $emailHandler = new EmailService(new PHPMailer(true));
            // send email
            $emailHandler->sendEmail(
                $emailData['email'],
                $email,
                $emailData['email_text'],
                $emailData['email_password'],
                $emailData['name'],
                $associationCode
            );

            // TODO clear session

            // return email sended
            return new JsonResponse('Email sended');

        } else {
            return new JsonResponse("Email not valid, please check it and try again.");
        }

    }
}