<?php

namespace App\Controller;

use App\Model\DatabaseCommunicator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class UpdateAssociationInfoController
{

    public function editAssociationInfo(Request $request, SessionInterface $session) {

        // get sended data
        $data = json_decode($request->getContent(), true);
        $email = trim($data['email']);
        $id = trim($data['id']);
        $text = trim($data['text']);

        // set regex for email and id values
        $emailRegexPattern = '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/';
        $idRegexPattern = '/^[a-zA-Z0-9]{5,}$/';

        // check if data is set and well formatted
        if (preg_match($emailRegexPattern, $email) && preg_match($idRegexPattern, $id) && isset($text)) {

            // save old session id value
            $oldId = $session->get('id');

            // set new id into session
            $session->set('id', $id);

            // create database Communicator object
            $dc = new DatabaseCommunicator();
            // call its appropriete method to handle updating Association basic info
            $dc->updateAssociationInfo($email, $id, $text, $oldId);

        } else {
            return new JsonResponse("Bad formatted Association data");
        }

        return new JsonResponse("info data updated");

    }
}