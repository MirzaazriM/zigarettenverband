<?php

namespace App\Controller;

use App\Model\DatabaseCommunicator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class LandingController extends AbstractController
{

    public function checkCode(Request $request, SessionInterface $session, DatabaseCommunicator $dc) {

        // first extract ref from the URL
        $id = $request->get('ref');

        // by default ref value is not set
        $idSet = false;

        // check if code is approprietely set (there is ref value in URL, ref is not empty and ref follows appropriet pattern)
        if (!empty($id) && preg_match('/[^a-zA-Z0-9]*/', $id)) { // TODO adjust pattern if necessary
            // set verbandIds to session so it can be used across all pages of the app
            // $session = new Session();
            $session->set('code', $id);
        }

        // call model for fetching questions data which will be used for filling template placeholders
        $questions = $dc->getQuestions();

        // return view for representing data
        return $this->render('/tests/test.html.twig', [
            "questions" => [
                1,2,3,4,5
            ]
        ]);

    }

}