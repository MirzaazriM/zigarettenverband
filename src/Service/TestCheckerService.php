<?php

namespace App\Service;


use App\Model\DatabaseCommunicator;
use PHPMailer\PHPMailer\PHPMailer;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Yaml\Yaml;

class TestCheckerService
{

    private $answers;
    private $dc;

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

            // call appropriete method from DC to fetch correct answers on sended question ids
            $answers = $this->dc->getAnswers();

            // call new method inside this class which will compare given and correct answers and return how many questions user answered correct and if user passed the test or not
            $numberOfCorrectAnswers = $this->checkCorrectAnswers();

            // upon data returned from previous function call function which decides if user passed the test
            // and sends appropriete email according to the test result
            $passed = $this->checkIfPassed($numberOfCorrectAnswers);

        } catch (\Exception $e) {
            die($e->getMessage());
        }

        return true; // $passed;
    }


    /**
     * Check how many questions has been answered correct
     *
     * @return bool
     */
    public function checkCorrectAnswers() {

        // call database and fetch correct answers

        // compare given and correct answers


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

        // compare number of correct answers to number of total questions
        // demo
        $totalNumberOfQuestions = 15;

        // check if user passed
        if (($totalNumberOfQuestions - $numberOfCorrectAnswers) < 5) {

            // create session object and get VerbandID if present
            $session = new Session();
            $verbandId = $session->get('code');

            // check if user camed from Verbands page - if yes call DC for Gutscheincode to send
            if (isset($verbandId)) {

                // if yes - call DC for getting appropriete Gutscheincode
                $codeData = $this->dc->getCode($session->get('code'));

                // check if number of left codes is for equal to lowest alert number (for example 10), if yes alert Association about number of valid codes
                $this->checkSendingAlertEmail($codeData['left']);

            } else {
                // if no - set codeData to null
                $codeData = null;
            }

            $userPassed = true;

        } else {
            // user not passed
            $userPassed = false;
        }

        // return data
        return $userPassed;
    }


    /**
     * Check if number of valid codes for specific Association is under limit and send email if it is
     *
     * @param $remainingCodes
     */
    public function checkSendingAlertEmail($remainingCodes) {

        if ($remainingCodes == 9) {
            // load developer info
            $yaml = Yaml::parse(file_get_contents('../config/configuration/developer-info.yml'));
            $developerInfo = $yaml['info'];

            // create emailService object and provide its constructor data
            $mail = new EmailService(
                $developerInfo['email'],
                $developerInfo['password'],
                'oglecevacmirza@gmail.com',
                'Low number of valid codes',
                'You have only ' . $remainingCodes . ' valid codes left.',
                new PHPMailer(true),
                null
            );

            // send email
            $mail->sendEmail();
        }

    }
}