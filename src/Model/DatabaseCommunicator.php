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

}