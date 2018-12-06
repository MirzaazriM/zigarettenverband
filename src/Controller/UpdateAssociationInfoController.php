<?php

namespace App\Controller;

use App\Model\DatabaseCommunicator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class UpdateAssociationInfoController
{


    public function editAssociationInfo(Request $request, SessionInterface $session, DatabaseCommunicator $dc) {
        // get sended data for editing
        $data = json_decode($request->getContent(), true);
        $email = trim($data['email']);
        $id = trim($data['id']);
        $text = trim($data['text']);

        // set regex for email and id values
        $emailRegexPattern = '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/';
        $idRegexPattern = '/^[a-zA-Z0-9]{5,}$/';

        // create response object
        $response = new JsonResponse();

        // check if data is set and well formatted
        if (preg_match($emailRegexPattern, $email) && preg_match($idRegexPattern, $id) && isset($text)) {
            // if yes, first save old session id value which we will need when updating data in database
            $oldId = $session->get('id');

            // set new id into session
            $session->set('id', $id);

            // call DC appropriete method to handle updating Association basic info
            $dc->updateAssociationInfo($email, $id, $text, $oldId);

            // set response status code
            $response->setStatusCode(200);

        } else {
            // set appropriete status code
            $response->setStatusCode(404);
        }

        // return response
        return $response;

    }
}