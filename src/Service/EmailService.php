<?php

namespace App\Service;

use App\Model\DatabaseCommunicator;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Symfony\Component\Yaml\Yaml;

class EmailService
{
    private $mail;

    /**
     * EmailService constructor.
     */
    public function __construct()
    {
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
            $this->mail = new PHPMailer(true);

            // if Association code is not null fetch Gutscheincode from database to send via email
            if (!is_null($associationCode)) {
                // get valid Gutscheincode from database
                $dc = new DatabaseCommunicator();
                $gutscheinData = $dc->getGutscheinCode($associationCode);
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
            $this->mail->Body =  (is_null($associationCode) or !isset($gutscheinData['code'])) ? $emailText : ($emailText . (' <br/> Gutscheincode is: ' . $gutscheinData['code']) );

            // send email
            $this->mail->send();

            // if script execution comes to this line it means that mail is successfully sent (if email address is valid),
            // check if Gutscheincode is sent and set that code as used (false) in database
            if (!is_null($associationCode) && isset($gutscheinData['code'])) {
                // update used gutscheinCode in database
                $dc->setCodeAsUsed($gutscheinData['id']);

                // check if number of valid codes for specific Association dropped below the limit - demo 10
                if (($gutscheinData['left'] - 1) == 10) {
                    // if yes, send alert email
                    $this->sendAlertEmail($senderEmail);
                }
            }

        } catch (Exception $e) {
            die($this->mail->ErrorInfo);
        }

    }


    /**
     * Set email parameters before sending email
     *
     * @param bool $isAssociationCodeValid
     * @param DatabaseCommunicator $dc
     * @param string|null $associationCode
     * @return array
     */
    public function setEmailParameters(bool $isAssociationCodeValid, DatabaseCommunicator $dc, string $associationCode = null):array {
        // set if code is valid and set appropriete email data
        if ($isAssociationCodeValid) {
            // get email data for the specific Association according to valid value of Association code
            $emailData = $dc->getEmailData($associationCode);
        } else {
            // read default email data from configuration file
            $yaml = Yaml::parse(file_get_contents('../config/configuration/developer-info.yml'));
            $yamlData = $yaml['info'];

            // set email parametars into $emailData array
            $emailData['email'] = $yamlData['email'];
            $emailData['email_password'] = $yamlData['password'];
            $emailData['email_text'] = file_get_contents('../uploaded_resources/thanks_email.txt');
            $emailData['name'] = $yamlData['name'];
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
        // load developer configuration data
        $yaml = Yaml::parse(file_get_contents('../config/configuration/developer-info.yml'));
        $developerConfig = $yaml['info'];

        // load email template for alert emails
        $emailAlertText = file_get_contents('../uploaded_resources/alert_email.txt');

        // clear addresses before sending another email
        $this->mail->clearAddresses();

        // send alert email
        $this->sendEmail($developerConfig['email'], $receivingEmail, $emailAlertText, $developerConfig['password'], $developerConfig['name'], null);
    }

}