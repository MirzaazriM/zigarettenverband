<?php

namespace App\Service;

use App\Model\DatabaseCommunicator;
use Psr\Log\LoggerInterface;

class TestCheckerService
{

    private $answers;
    private $dc;

    /**
     * When constructing TestCheckerService object set answers to check and create connection to the database
     *
     * TestCheckerService constructor.
     * @param array $answers
     */
    public function __construct(array $answers)
    {
        $this->answers = $answers;
        $this->dc = new DatabaseCommunicator();
    }


    /**
     * Check test
     */
    public function checkTest() {

        try {
            // call appropriate method from DC to fetch correct answers on sended question ids
            $answers = $this->dc->getAnswers();

            // call new method inside this class which will compare given and correct answers and return how many questions user answered correctly
            $numberOfCorrectAnswers = $this->checkCorrectAnswers($answers);

            // upon data returned from previous function call function which decides if user passed the test
            $passed = $this->checkIfPassed($numberOfCorrectAnswers);

        } catch (\Exception $e) {
            // TODO handle exception
        }

        // return if user passed the test or not
        return $passed;
    }


    /**
     * Check how many questions has been answered correct
     *
     * @param array $answers
     * @return int
     */
    public function checkCorrectAnswers(array $answers):int {

        // TODO call database and fetch correct answers

        // TODO compare given and correct answers

        // return number of correct answers
        return 12;
    }


    /**
     * Check if user passed the test
     *
     * @param $numberOfCorrectAnswers
     * @return bool
     */
    public function checkIfPassed($numberOfCorrectAnswers):bool {

        // TODO check implementation

        // compare number of correct answers to number of total questions - demo
        $totalNumberOfQuestions = 15;

        // check if user passed
        if (($totalNumberOfQuestions - $numberOfCorrectAnswers) < 5) {
            // user passed the test
            $userPassed = true;

        } else {
            // user not passed
            $userPassed = false;
        }

        // return data
        return $userPassed;
    }
}