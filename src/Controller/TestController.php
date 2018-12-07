<?php

namespace App\Controller;

use App\Model\DatabaseCommunicator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class TestController extends AbstractController
{

    /**
     * After user comes to the Test (landing) page this function check if he/she comes from one of the Associations page
     * Inject Request, SessionInterface and DatabaseCommunicator services
     *
     * @param Request $request
     * @param SessionInterface $session
     * @param DatabaseCommunicator $dc
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function checkCode(Request $request, SessionInterface $session, DatabaseCommunicator $dc) {
        // first extract ref from the URL
        $id = $request->get('ref');

        // by default ref value is not set
        $idSet = false;

        // check if code is approprietely set (there is ref value in URL, ref is not empty and ref follows appropriet pattern)
        if (!empty($id) && preg_match('/[^a-zA-Z0-9]*/', $id)) { // TODO adjust pattern if necessary
            // set Association Id to session so it can be used across all pages of the app
            $session->set('code', $id);
        }

        // TODO call model for fetching questions data which will be used for filling template placeholders
        // TODO also check if this function is necessary - there is possibility questions will be hardcoded
        $questions = $dc->getQuestions();

        // render template, give it values and return it
        return $this->render('/tests/test.html.twig', [
            "questions" => [
                1,2,3,4,5,6,7,8,9,10,11,12,13,14,15 // TODO replace dummy with real data
            ]
        ]);

    }

}