<?php

namespace App\Controller;

use App\Service\TestCheckerService;
use Symfony\Component\HttpFoundation\Request;

class CheckTestController
{

    public function checkTest(Request $request) {

        // get sended answer and question ids

        // call service to check how many questions are correct
        $check = new TestCheckerService([
           '1' => 'a',
            '5' => 'd,e'
        ]);

        // call service method and check test answers
        $check->checkTest();

        // return template to show if user passed or not passed test
        die("render template");
    }
}