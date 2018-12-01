<?php

namespace App\Controller;

use App\Model\DatabaseCommunicator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Landing extends AbstractController
{

    private $dc;

    public function __construct()
    {
        $this->dc = new DatabaseCommunicator();
    }

    public function checkCode(Request $request) {

        // first extract ref from the URL
        $id = $request->get('ref');
        // by default ref value is not set
        $idSet = false;

        // check if ref is approprietely set (there is ref value in URL, ref is not empty and ref follows appropriet pattern)
        if (!empty($id) && preg_match('/[^a-zA-Z0-9]*/', $id)) { // TODO adjust pattern if necessary
            // also check that ref value matches one of the codes entered by the Verbands
            $idSet = $this->dc->checkCode($id);
        }

        // call model for fetching questions data which will be used for filling template placeholders
        $questions = $this->dc->getQuestions();

        // return view for representing data
        return $this->render('/tests/test.html.twig', [
            "questions" => [
                1,2,3,4,5
            ]
        ]);

    }

}