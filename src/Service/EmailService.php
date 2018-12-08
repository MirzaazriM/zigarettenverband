<?php

namespace App\Service;

use App\Model\DatabaseCommunicator;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Yaml\Yaml;

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
            $this->mail->Body =  (is_null($associationCode) or !isset($gutscheinData['code'])) ?
                $emailText : ($emailText . $this->renderView('gutscheincode_line.html.twig', [
                        'code' => $gutscheinData['code']
                    ]));

            // send email
            $this->mail->send();

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
            // log message
            $this->logger->error("SendEmail function: " . $this->mail->ErrorInfo);

            // if this service is not working we can not send log via email to developer (endless loop)
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
        // set if code is valid and set appropriete email data
        if ($isAssociationCodeValid) {
            // get email data for the specific Association according to valid value of Association code
            $emailData = $this->dc->getEmailData($associationCode);
        } else {
            // load developer info
            $this->loadDeveloperData();

            // set email parametars into $emailData array
            $emailData['email'] = $this->developerConfig['email'];
            $emailData['email_password'] = $this->developerConfig['password'];
            $emailData['email_text'] = file_get_contents('../uploaded_resources/thanks_email.txt');
            $emailData['name'] = $this->developerConfig['name'];
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
        // load developer info
        $this->loadDeveloperData();

        // load email template for alert emails
        $emailAlertText = file_get_contents('../uploaded_resources/alert_email.txt');

        // clear addresses before sending another email
        $this->mail->clearAddresses();

        // send alert email
        $this->sendEmail($this->developerConfig['email'], $receivingEmail, $emailAlertText, $this->developerConfig['password'], $this->developerConfig['name'], null);

    }


    /**
     * Load developer info from configuration if necessary
     */
    public function loadDeveloperData() {
        // load developer configuration data
        $yaml = Yaml::parse(file_get_contents('../config/configuration/developer_info-dev.yml'));
        $this->developerConfig = $yaml['info'];
    }

}