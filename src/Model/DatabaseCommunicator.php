<?php

namespace App\Model;

use PDO;
use PDOException;
use Symfony\Component\Yaml\Yaml;

class DatabaseCommunicator
{

    private $configuration;
    private $connection;

    public function __construct()
    {
        // when constructing DatabaseCommunicator object load necessary data for connecting to the database
        $yaml = Yaml::parse(file_get_contents('../config/configuration/database-dev.yml'));
        $this->configuration = $yaml['config'];

        // connect to the database
        $this->connection = new PDO($this->configuration['dsn'], $this->configuration['user'], $this->configuration['password']);
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

        } catch (PDOException $e) {
            $isValid = false;
            die($e->getMessage());
        }

        // return value
        return $isValid;
    }


    public function getQuestions():array {

        try {

        } catch (PDOException $e) {

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

        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }


    public function getAnswers():array {

        try {

            // call database for answers

        } catch (PDOException $e) {
            die($e->getMessage());
        }

        // return correct answers
        return [];
    }


    /**
     * Check user credentials function
     *
     * @param string $email
     * @param string $password
     * @return string
     */
    public function checkUserCredentials (string $email, string $password):string {

        // initialize return string
        $message = '';

        try {
            // set database instructions
            $sql = "SELECT email, password FROM associations WHERE email = :email";
            $statement = $this->connection->prepare($sql);
            $statement->bindParam(':email', $email, PDO::PARAM_STR);
            $statement->execute();

            // check if anything returned from database
            if ($statement->rowCount() > 0) {
                // if yes fetch it
                $userData = $statement->fetch(PDO::FETCH_ASSOC);

                // get password from fetched data and verify if it equals to the entered one
                if (!password_verify($password, $userData['password'])) {
                    $message = 'Entered password is not valid';
                }

            } else {
                $message = 'Entered email is not valid';
            }

        } catch (PDOException $e) {
            die($e->getMessage());
        }

        return $message;
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

        } catch (PDOException $e) {
            die($e->getMessage());
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

        // TODO check password for emails

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

        } catch (PDOException $e) {
            die($e->getMessage());
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
                    LIMIT 1";  // get id and code so that we can set the code via its id as false if email is successfully sent
            $statement = $this->connection->prepare($sql);
            $statement->execute([
                $associationCode
            ]);

            // get code
            $gutscheinCode = $statement->fetch(PDO::FETCH_ASSOC);

            // get total number of valid codes for specific Association
            $sqlTotal = "SELECT FOUND_ROWS()";
            $statementTotal = $this->connection->prepare($sqlTotal);
            $statementTotal->execute();
            $totalLeft = $statementTotal->fetch();

            // integrate totalLeft value into gutscheinCode data
            $gutscheinCode['left'] = $totalLeft[0];

        } catch (PDOException $e) {
            $gutscheinCode = [];
            die($e->getMessage());
        }

        // return data
        return $gutscheinCode;
    }


    /**
     * Set code used after sent by email and check
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

        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }
}