<?php

namespace App\Service;

class Upload
{

    private $filename;
    private $tempname;

    public function __construct(string $filename, string $tempname)
    {
        $this->filename = $filename;
        $this->tempname = $tempname;
    }

    public function handleCSV() {

        try {

            // upload csv file to the uploaded_resources folder
            $this->uploadCSV();

            // read data from csv file
            $csvData = $this->readCsvFile();

            // insert csv records into appropriete database table



        } catch (\Exception $e) {
            die($e->getMessage());
        }

    }


    public function uploadCSV() {
        // move uploaded file to destination folder
        move_uploaded_file($this->tempname, "../uploaded_resources/" . $this->filename);
    }


    public function readCsvFile():array {
        // read uploaded file and make array from it
        return explode(',', file_get_contents("../uploaded_resources/" . $this->filename));
    }

}