<?php

namespace App\Service;

use App\Model\DatabaseCommunicator;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class EmailService extends AbstractController
{
    private $mail;
    private $logger;
    private $developerConfig;
    private $dc;

    public function __construct()
    {
        $this->dc = new DatabaseCommunicator();
    }


    /**
     * Send email function
     *
     * @param string $senderEmail
     * @param string $receivingEmail
     * @param string $emailText
     * @param string $password
     * @param string|null $associationName
     * @param string|null $associationCode
     */
    public function sendEmail(string $senderEmail, string $receivingEmail, string $emailText, string $password, string $associationName = null,  string $associationCode = null) {

        try {
            // create global PHPMailer object
            $this->mail = new PHPMailer();

            // if Association code is not null fetch Gutscheincode from database to send via email
            if (!is_null($associationCode)) {
                // get valid Gutscheincode from database
                // $dc = new DatabaseCommunicator();
                $gutscheinData = $this->dc->getGutscheinCode($associationCode);
            }

            // server settings
            $this->mail->isSMTP();
            $this->mail->Host = 'smtp.gmail.com';
            $this->mail->SMTPAuth = true;
            $this->mail->Username = $senderEmail;
            $this->mail->Password = $password;
            $this->mail->SMTPSecure = 'tls';
            $this->mail->Port = 587;

            // set recipient/s
            $this->mail->setFrom($senderEmail, $associationName);
            $this->mail->addAddress($receivingEmail, 'Receiver');

            // set content
            $this->mail->isHTML(true);
            $this->mail->Subject = $associationName;
            // use renderView if code is set, so that HTTP cache line is not rendered in email message
            $this->mail->Body =  $this->setEmailBody(
                $emailText,
                isset($associationCode) ? $associationCode  : null,
                isset($gutscheinData['code']) ? $gutscheinData['code'] : null
            );

            // send email
            $this->mail->send();

            // TODO handle sending multiple emails after user passed the test ? redirect to another page
            // clear session
            session_destroy();

            // if script execution comes to this line it means that mail is successfully sent (if email address is valid),
            // check if Gutscheincode is sent and set that code as used (false) in database
            if (!is_null($associationCode) && isset($gutscheinData['code'])) {
                // update used gutscheinCode in database
                $this->dc->setCodeAsUsed($gutscheinData['id']);

                // check if number of valid codes for specific Association dropped below the limit - demo 10
                if (($gutscheinData['left'] - 1) == 10) {
                    // if yes, send alert email
                    $this->sendAlertEmail($senderEmail);
                }
            }

        } catch (Exception $e) {
            // TODO handle exception
        }

    }


    /**
     * Set email parameters before sending email
     *
     * @param bool $isAssociationCodeValid
     * @param string|null $associationCode
     * @return array
     */
    public function setEmailParameters(bool $isAssociationCodeValid, string $associationCode = null):array {

        try {
            // set if code is valid and set appropriete email data
            if ($isAssociationCodeValid) {
                // get email data for the specific Association according to valid value of Association code
                $emailData = $this->dc->getEmailData($associationCode);
            } else {
                // get developer info
                $configurationLoader = new ConfigurationLoaderService('../config/configuration/config-' . getenv("APP_ENV") . '.yml');
                $this->developerConfig = $configurationLoader->getDeveloperInfo();

                // set email parametars into $emailData array
                $emailData['email'] = $this->developerConfig['email'];
                $emailData['email_password'] = $this->developerConfig['password'];
                $emailData['email_text'] = file_get_contents('../uploaded_resources/thanks_email.txt');
                $emailData['name'] = $this->developerConfig['name'];
            }

        } catch (\Exception $e) {
            // set data to return
            $emailData = [];

            // TODO handle exception
        }

        // return email data
        return $emailData;
    }


    /**
     * Send alert email
     *
     * @param $receivingEmail
     */
    public function sendAlertEmail($receivingEmail) {

        try {
            // get developer info
            $configurationLoader = new ConfigurationLoaderService('../config/configuration/config-' . getenv("APP_ENV") . '.yml');
            $this->developerConfig = $configurationLoader->getDeveloperInfo();

            // load email template for alert emails
            $emailAlertText = file_get_contents('../uploaded_resources/alert_email.txt');

            // clear addresses before sending another email
            $this->mail->clearAddresses();

            // send alert email
            $this->sendEmail($this->developerConfig['email'], $receivingEmail, $emailAlertText, $this->developerConfig['password'], $this->developerConfig['name'], null);

        } catch (\Exception $e) {
            // TODO handle exception
        }

    }


    /**
     * Set appropriate email body
     *
     * @param string $associationCode
     * @param string $code
     * @param string $emailText
     * @return string
     */
    public function setEmailBody(string $emailText, string $associationCode = null, string $code = null) {
        // check if there is $associationCode or Gutscheincode set
        if (is_null($associationCode) or !isset($code)) {
            // if nothing is set return just $emailText
            return $emailText;
        } else {
            // add Gutscheincode line to the email text and return
            return $emailText . $this->renderView('gutscheincode_line.html.twig', [
                    'code' => $code
                ]);
        }
    }

}