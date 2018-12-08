<?php

namespace App\Controller;

use App\Service\AuthorizationCheckerService;
use App\Service\UploadService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class UploadFileController
{

    /**
     * Upload file controller
     * Inject Request, AuthorizationCheckerService and SessionInterface services
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function upload(Request $request, SessionInterface $session, AuthorizationCheckerService $authChecker) {
        // check if user is logged in (authorizated for making this request)
        $authChecker->checkAuthorization();

        // create response object
        $response = new JsonResponse();

        // check if there is sended file in the request object
        if (count($request->files->all()) > 0) {
            // set file to the fileRaw variable
            $fileRaw = $_FILES['file'];

            // get uploaded file extension
            $name = $fileRaw['name'][0];
            $extension = strtolower(substr($name, strpos($name, '.') + 1));

            // check if extension of uploaded file is .csv
            if ($extension === 'csv') {
                // create service object for calling its method which will upload file, read its values, insert values into database and delete uploaded file afterwards
                $uploadService = new UploadService($name, $fileRaw['tmp_name'][0], $session->get('id'));
                $uploadService->handleCSV();

                // set response status code
                $response->setStatusCode(200);

            } else {
                // set response status code
                $response->setStatusCode(404);
            }

        } else {
            // set response status code
            $response->setStatusCode(404);
        }

        // return response
        return $response;
    }
}