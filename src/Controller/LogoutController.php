<?php

namespace App\Controller;

class LogoutController
{

    /**
     * Handle
     */
    public function logout() {

        // check if user is logged in
        if (isset($_SESSION)) {
            // unset logged variable
            unset($_SESSION['logged_in']);
            // destroy session
            session_destroy();
            // redirect to login page after logout
            header("Location: /login");
            // exit current script
            exit();
        }

    }
}