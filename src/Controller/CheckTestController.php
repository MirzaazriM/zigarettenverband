<?php

namespace App\Controller;

use App\Service\TestCheckerService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CheckTestController extends AbstractController
{

    /**
     * Function for showing appropriate page depending if user passed the test or not
     * Inject SessionInterface service
     *
     * @param Request $request
     * @param SessionInterface $session
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function checkTest(Request $request, SessionInterface $session) {
        // TODO get sended answers and question ids from the request

        // create TestChecker object and pass necessary constructor arguments
        $check = new TestCheckerService([
            '1' => 'a',
            '5' => 'd,e'
        ]);

        // TODO call service method and check test answers - currently dummy response
        $passed = $check->checkTest();

        // get session Association code if exits
        $sessionCode = $session->get('code');

        // check $passed value and existence of Association code in session to decide which template to render
        if ($passed == true && isset($sessionCode)) {
            // user passed the test after coming from one of the Associations page - set appropriate template and data to render
            $template = 'test_passed.html.twig';
            $templateData = [
                'message' => 'Test passed, enter email for Gutscheincode'
            ];

            // TODO check destroying session and its implications

        } else if ($passed == true && !isset($sessionCode)) {
            // user passed but he/she didnt came from one of the Associations page - set appropriate template and data to render
            $template = 'test_passed.html.twig';
            $templateData = [
                'message' => 'Test passed, enter email for Thanks email'
            ];
        } else {
            // user failed test regardless if he/she came from one of the Associations page - set appropriate template and data to render
            $template = 'test_failed.html.twig';
            $templateData = [];
        }

        // return template to show if user passed or not passed the test, and set data for the template
        return $this->render('test_finished/' . $template, $templateData);
    }
}