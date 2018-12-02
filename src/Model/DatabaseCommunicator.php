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


    public function checkCode(string $id):bool {

        try {

        } catch (PDOException $e) {

        }

        return true;
    }


    public function getQuestions():array {

        try {

        } catch (PDOException $e) {

        }

        return [];

    }


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


    public function getCode(string $code):array {

        // initialize codeData variable
        $codeData = [];

        try {

            // set database instructions
            $sql = "SELECT SQL_CALC_FOUND_ROWS
                        id, code 
                    FROM association_codes 
                    WHERE code_valid = 'true' AND associations_id = ? 
                    LIMIT 1";
            $statement = $this->connection->prepare($sql);
            $statement->execute([
                $code
            ]);

            // fetch data
            if ($statement->rowCount() > 0) {
                $codeData = $statement->fetch(PDO::FETCH_ASSOC);
            }

            // get total number of valid codes for specific Association
            $sqlTotal = "SELECT FOUND_ROWS()";
            $statementTotal = $this->connection->prepare($sqlTotal);
            $statementTotal->execute();
            $totalLeft = $statementTotal->fetch();

            // integrate totalLeft value into codeData
            $codeData['left'] = $totalLeft[0];

        } catch (PDOException $e) {

        }


        return $codeData;
    }

}