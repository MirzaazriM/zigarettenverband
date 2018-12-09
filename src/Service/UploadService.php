<?php

namespace App\Service;

use App\Model\DatabaseCommunicator;

class UploadService
{

    private $filename;
    private $tempname;
    private $id;

    public function __construct(string $filename, string $tempname, string $id)
    {
        $this->filename = $filename;
        $this->tempname = $tempname;
        $this->id = $id;
    }


    /**
     * Main function for handling upload, saving codes and deleting file
     */
    public function handleCSV() {

        try {
            // upload csv file to the uploaded_resources folder
            $this->uploadCSV();

            // read data from csv file
            $csvData = $this->readCsvFile();

            // insert csv records into appropriete database table
            $this->insertCodes($csvData);

            // delete uploaded file after codes have been inserted into database
            $this->deleteUploadedFile();

        } catch (\Exception $e) {
            // TODO handle exception
        }

    }


    /**
     * Move uploaded file to the wanted directory
     */
    public function uploadCSV() {
        // move uploaded file to destination folder
        if (!move_uploaded_file($this->tempname, "../uploaded_resources/" . $this->filename)) {
            throw new \Exception("Failed to move file to temporary folder");
        }
    }


    /**
     * Read CSV file and set its values in array
     *
     * @return array
     * @throws \Exception
     */
    public function readCsvFile():array {
        // read uploaded file and make array from it
        $csv = explode(',', file_get_contents("../uploaded_resources/" . $this->filename));

        // check if is anything red from file
        if (count($csv) === 1 && $csv[0] === '') {
            // first delete uploaded empty file
            $this->deleteUploadedFile();

            // throw exception
            throw new \Exception('Uploaded file is empty');
        }

        // return codes
        return $csv;
    }


    /**
     * Call Database Communicator and insert codes into database
     *
     * @param $codes
     */
    public function insertCodes($codes) {
        // create database communicator object and call method for inserting codes values
        $dc = new DatabaseCommunicator();
        $dc->insertNewCodes($codes, $this->id);
    }


    /**
     * Delete uploaded file after reading and inserting values into database
     */
    public function deleteUploadedFile() {
        // delete file if exists
        if (file_exists("../uploaded_resources/" . $this->filename)) {
            unlink("../uploaded_resources/" . $this->filename);
        }
    }

}