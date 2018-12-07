<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Debug\Exception\FlattenException;

class ExceptionController extends AbstractController
{

    public function showException(FlattenException $exception) {
        // get exception code
        $errorCode = $exception->getMessage();

        // check code  and set appropriate response
        if ($errorCode === 404) {
            return $this->render('/error_pages/error404.html.twig', []);
        } else {
            return $this->render('/error_pages/error404.html.twig', [
                'message' => "Not recognised error " . $errorCode
            ]);
        }

    }
}