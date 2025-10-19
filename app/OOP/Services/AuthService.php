<?php
namespace App\OOP\Services;

class AuthService {
    /** 1:1 wie dein legacy loginUser($user) */
    public static function loginUser(array $user): void {
        $_SESSION['id']       = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['admin']    = $user['admin'];
        $_SESSION['message']  = 'Du bist Eingeloggt';
        $_SESSION['type']     = 'success';

        if ($_SESSION['admin']) {
            header('location: ' . BASE_URL . '/admin/dashboard.php');
        } else {
            header('location: ' . BASE_URL . '/index.php');
        }
        exit();
    }
}
