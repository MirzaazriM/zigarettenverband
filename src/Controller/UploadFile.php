<?php

namespace App\Controller;

use App\Service\Upload;
use Symfony\Component\HttpFoundation\Request;

class UploadFile
{

    /**
     * Upload file controller
     *
     * @param Request $request
     */
    public function upload(Request $request) {

        // check if there is sended file in the request object
        if (count($request->files->all()) > 0) {
            // if yes, set file to the fileRaw variable
            $fileRaw = $_FILES['file'];

            // get uploaded file extension
            $name = $fileRaw['name'][0];
            $extension = strtolower(substr($name, strpos($name, '.') + 1));

            // check if extension of uploaded file is .csv
            if ($extension === 'csv') {
                // create service object for calling its method which will upload file, read its values, insert values into database and delete uploaded file afterwards
                $uploadService = new Upload($name, $fileRaw['tmp_name'][0]);
                $uploadService->handleCSV();

            } else {
                die("file is not .csv");
            }

        // if no, return appropriete message
        } else {
            die("no file uploaded");
        }

        die("EEE");
    }
}