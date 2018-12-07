<?php

namespace App\Model;

use App\Service\EmailService;
use PDO;
use PDOException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Yaml\Yaml;

class DatabaseCommunicator
{

    private $configuration;
    private $emailConfiguration;
    private $connection;
    private $logger;
    private $emailSender;

    /**
     * Each time object of DatabaseCommunicator is created create connection to database
     *
     * DatabaseCommunicator constructor.
     */
    public function __construct()
    {
        // when constructing DatabaseCommunicator object load necessary data for connecting to the database
        $yaml = Yaml::parse(file_get_contents('../config/configuration/database-dev.yml'));
        $this->configuration = $yaml['config'];

        // connect to the database
        $this->connection = new PDO($this->configuration['dsn'], $this->configuration['user'], $this->configuration['password']);
        // set connection to throw exceptions
        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }


    /**
     * Inject LoggerInterface and EmailService as optional
     *
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger, EmailService $emailService)
    {
        $this->logger = $logger;
        $this->emailSender = $emailService;

        // load developer info data needed for sending email
        $yaml = Yaml::parse(file_get_contents('../config/configuration/developer-info.yml'));
        $this->emailConfiguration = $yaml['info'];
    }


    /**
     * Check if Associations code is correct
     *
     * @param string $id
     * @return bool
     */
    public function checkCode(string $id):bool {

        try {
            // set database instructions
            $sql = "SELECT id FROM associations WHERE id = ?";
            $statement = $this->connection->prepare($sql);
            $statement->execute([
                $id
            ]);

            // check if there is Association with the given id
            if ($statement->rowCount()) {
                $isValid = true;
            } else {
                $isValid = false;
            }

        } catch (\PDOException $e) {
            // log message
            $this->logger->error("CheckCode function: " . $e->getMessage());
        }

        // return value
        return $isValid;
    }


    /**
     * TODO Fetch test questions
     *
     * @return array
     */
    public function getQuestions():array {

        try {

        } catch (\PDOException $e) {

        }

        return [];
    }


    /**
     * Insert new Gutscheincodes uploaded via CSV file
     *
     * @param array $codes
     * @param string $associationId
     */
    public function insertNewCodes(array $codes, string $associationId) {

        try {
            // write database instructions
            $sql = "INSERT INTO 
                        association_codes 
                        (code, code_valid, associations_id)
                    VALUES (?, ?, ?)";
            $statement = $this->connection->prepare($sql);

            // loop through all values of codes array and insert them into database
            foreach ($codes as $code) {
                $statement->execute([
                    trim($code),
                    true,
                    $associationId
                ]);
            }

        } catch (\PDOException $e) {
            // log message
            $this->logger->error("InsertNewCodes function: " . $e->getMessage());
        }
    }


    public function getAnswers():array {

        try {

            // call database for answers

        } catch (\PDOException $e) {
            // log message
            $this->logger->error("GetAnswers function: " . $e->getMessage());
        }

        // return correct answers
        return [];
    }


    /**
     * Check user credentials function
     *
     * @param string $email
     * @param string $password
     * @return int
     */
    public function checkUserCredentials (string $email, string $password):int {

        try {
            // set database instructions
            $sql = "SELECT email, password FROM associations WHERE email = :email";
            $statement = $this->connection->prepare($sql);
            $statement->bindParam(':email', $email, PDO::PARAM_STR);
            $statement->execute();

            // by default code is 200
            $code = 200;

            // check if anything returned from database
            if ($statement->rowCount() > 0) {
                // if yes fetch it
                $userData = $statement->fetch(PDO::FETCH_ASSOC);

                // get password from fetched data and verify if it equals to the entered one
                if (!password_verify($password, $userData['password'])) {
                    $code = 401;
                }

            } else {
                $code = 401; // email
            }

        } catch (\PDOException $e) {
            // log message
            $this->logger->error("CheckUserCredentials function: " . $e->getMessage());

            // send email

        }

        // return code value
        return $code;
    }


    /**
     * Get system data for specific user based on email address he/she logged in
     *
     * @param string $email
     * @return array
     */
    public function getUserSystemData(string $email) {

        try {
            // set database instructions
            $sql = "SELECT 
                        a.id,
                        a.name, 
                        a.email, 
                        a.email_text,
                        SUM(if(ac.code_valid = 'true' AND associations_id = a.id, 1, 0)) AS unused_codes,
                        SUM(if(ac.code_valid = 'false' AND associations_id = a.id, 1, 0)) AS used_codes
                    FROM associations AS a
                    LEFT JOIN association_codes AS ac ON a.id = ac.associations_id
                    WHERE a.email = ?
                    GROUP BY a.id";
            $statement = $this->connection->prepare($sql);
            $statement->execute([
                $email
            ]);

            // fetch data after query execution
            $data = $statement->fetch(PDO::FETCH_ASSOC);

        } catch (\PDOException $e) {
            // log message
            $this->logger->error("GetUserSystemData function: " . $e->getMessage());

            // set data to return
            $data = [];

            // send alert email to developer
            $this->emailSender->sendEmail($this->emailConfiguration['email'], $this->emailConfiguration['email'], "GetUserSystemData function: " . $e->getMessage(), $this->emailConfiguration['password'], "Zigarettenverband System Error");
        }

        // return system data for the user
        return $data;
    }


    /**
     * Update Association details function
     *
     * @param string $email
     * @param string $id
     * @param string $text
     * @param string $loggedId
     */
    public function updateAssociationInfo(string $email, string $id, string $text, string $loggedId) {

        try {
            // set database instructions
            $sql = "UPDATE 
                      associations 
                    SET 
                        id = ?,
                        email = ?,
                        email_text = ?
                    WHERE id = ?";
            $statement = $this->connection->prepare($sql);
            $statement->execute([
                $id,
                $email,
                $text,
                $loggedId
            ]);

            // check if new id and loggedId are the same
            if ($id != $loggedId) {
                // if no update all association_id in association_codes table to new Association code
                $sqlUpdate = "UPDATE 
                                association_codes 
                              SET
                                associations_id = ?
                              WHERE 
                                associations_id = ?";
                $statementUpdate = $this->connection->prepare($sqlUpdate);
                $statementUpdate->execute([
                    $id,
                    $loggedId
                ]);

                // TODO check what to do which generates after user comes from one of its pages ? send request to some API
            }

        } catch (\PDOException $e) {
            die($e->getMessage());
        }
    }


    /**
     * Get email data of specific Association
     *
     * @param string $code
     * @return mixed
     */
    public function getEmailData(string $code) {

        // TODO check passwords for emails

        try {
            // set database instructions 
            $sql = "SELECT 
                        a.id,
                        a.name,
                        a.email,
                        a.email_text,
                        a.email_password
                   FROM associations AS a 
                   WHERE id = ?";
            $statement = $this->connection->prepare($sql);
            $statement->execute([
                $code
            ]);

            // get data
            $data = $statement->fetch(PDO::FETCH_ASSOC);

        } catch (\PDOException $e) {
            // log message
            $this->logger->error("GetEmailData function: " . $e->getMessage());
        }

        // return data
        return $data;

    }


    /**
     * Get Gutscheincode from specific Association
     *
     * @param string $associationCode
     * @return mixed
     */
    public function getGutscheincode(string $associationCode) {

        try {
            // set database instructions
            $sql = "SELECT SQL_CALC_FOUND_ROWS
                        id, 
                        code
                    FROM association_codes 
                    WHERE associations_id = ? AND code_valid = 'true'
                    LIMIT 1";  // get id and code so that we can set the code via its id as used (false) if email is successfully sent
            $statement = $this->connection->prepare($sql);
            $statement->execute([
                $associationCode
            ]);

            // get code
            $gutscheinCode = $statement->fetch(PDO::FETCH_ASSOC);

            // get total number of valid codes for specific Association - to check if number of valid codes is under the limit
            $sqlTotal = "SELECT FOUND_ROWS()";
            $statementTotal = $this->connection->prepare($sqlTotal);
            $statementTotal->execute();
            $totalLeft = $statementTotal->fetch();

            // integrate totalLeft value into gutscheinCode data
            $gutscheinCode['left'] = $totalLeft[0];

        } catch (\PDOException $e) {
            // log message
            $this->logger->error("GetGutscheincode function: " . $e->getMessage());
        }

        // return data
        return $gutscheinCode;
    }


    /**
     * Set code as used after sent by email
     *
     * @param int $id
     */
    public function setCodeAsUsed(int $id) {

        try {
            // set database instructions
            $sql = "UPDATE association_codes
                    SET code_valid = 'false'
                    WHERE id = ?";
            $statement = $this->connection->prepare($sql);
            $statement->execute([
                $id
            ]);

        } catch (\PDOException $e) {
            // log message
            $this->logger->error("SetCodeAsUsed function: " . $e->getMessage());
        }
    }
}