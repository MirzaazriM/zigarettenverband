<?php

namespace App\Controller;

use App\Model\DatabaseCommunicator;
use App\Service\EmailService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Yaml\Yaml;

class EmailController extends AbstractController
{

    /**
     * Check sending email - inject Request, SessionInterface and EmailService services
     * @param Request $request
     * @param SessionInterface $session
     * @param EmailService $emailHandler
     * @return JsonResponse
     */
    public function checkEmail(Request $request, SessionInterface $session, EmailService $emailHandler, DatabaseCommunicator $dc) {

        // get email from user
        $data = json_decode($request->getContent(), true);
        $email = $data['email'];

        // check if email is right formatted
        $emailRegexPattern = '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/';
        if (preg_match($emailRegexPattern, $email)) {

            // get Association code from session if exists
            $associationCode = $session->get('code');

            // set email array which will hold email data
            $emailData = [];

            // first check if Association code is set and valid
            $isAssociationCodeValid = false;
            if (!is_null($associationCode)) {
                $isAssociationCodeValid = $dc->checkCode($associationCode);
            }

            // check code and set appropriete email values
            if (!is_null($associationCode) && $isAssociationCodeValid) {
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