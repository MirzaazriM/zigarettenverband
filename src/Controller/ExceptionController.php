<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 07/12/2018
 * Time: 11:31
 */

namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Debug\Exception\FlattenException;

class ExceptionController extends AbstractController
{

    public function showException(FlattenException $exception) {
        // get exception code
        $errorCode = $exception->getStatusCode();

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