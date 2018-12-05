<?php

namespace App\Controller;

use App\Service\TestCheckerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CheckTestController extends AbstractController
{

    public function checkTest(Request $request, SessionInterface $session) {

        // get sended answer and question ids

        // call service to check how many questions are correct
        $check = new TestCheckerService([
            '1' => 'a',
            '5' => 'd,e'
        ]);

        // call service method and check test answers
        $passed = $check->checkTest();
        // get session Association code if exits
        $sessionCode = $session->get('code');

        // check according to $passed value and existence of Association code in session which template to return
        if ($passed == true && isset($sessionCode)) {
            // user passed after coming from one of the Associations page - set appropriete template and data to render
            $template = 'test_passed.html.twig';
            $templateData = [
                'message' => 'Test passed - with code'
            ];

            // destroy session code to prevent misuse
            // TODO check implications of destroying session
            // $session->clear();
        } else if ($passed == true && !isset($sessionCode)) {
            // user passed but he/she didnt came from one of the Associations page - set appropriete template and data to render
            $template = 'test_passed.html.twig';
            $templateData = [
                'message' => 'Test passed - no code'
            ];
        } else {
            // user failed test regardless if he/she came from one of the Associations page - set appropriete template and data to render
            $template = 'test_failed.html.twig';
            $templateData = [];
        }

        //return new JsonResponse([2,4,5]);
        // return template to show if user passed or not passed test
        return $this->render('test_finished/' . $template, $templateData);
    }
}