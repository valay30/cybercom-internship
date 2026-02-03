<?php

/**
 * AuthController
 * Handles authentication logic (login/signup)
 */

class AuthController
{
    private $redirectUrl;

    public function __construct()
    {
        // Get redirect URL from query parameter
        $this->redirectUrl = $_GET['redirect'] ?? 'index.php';
    }

    /**
     * Handle form submissions
     */
    public function handleAction()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        $action = $_POST['action'] ?? '';

        switch ($action) {
            case 'login':
                $this->handleLogin();
                break;
            case 'signup':
                $this->handleSignup();
                break;
            case 'logout':
                $this->handleLogout();
                break;
        }
    }

    /**
     * Handle login
     */
    private function handleLogin()
    {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        // Simple validation (in real app, verify against database)
        if (!empty($email) && !empty($password)) {
            // Set login cookie (expires in 30 days)
            setcookie('user_logged_in', 'true', time() + (86400 * 30), '/');
            setcookie('user_email', $email, time() + (86400 * 30), '/');

            // Set session
            $_SESSION['user_logged_in'] = true;
            $_SESSION['user_email'] = $email;

            // Redirect to intended page
            header('Location: ' . $this->redirectUrl);
            exit;
        }
    }

    /**
     * Handle signup
     */
    private function handleSignup()
    {
        $fullname = $_POST['fullname'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Simple validation
        if (!empty($fullname) && !empty($email) && !empty($password) && $password === $confirmPassword) {
            // In real app, save to database

            // Auto-login after signup
            setcookie('user_logged_in', 'true', time() + (86400 * 30), '/');
            setcookie('user_email', $email, time() + (86400 * 30), '/');
            setcookie('user_name', $fullname, time() + (86400 * 30), '/');

            $_SESSION['user_logged_in'] = true;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_name'] = $fullname;

            // Redirect to intended page
            header('Location: ' . $this->redirectUrl);
            exit;
        }
    }

    /**
     * Handle logout
     */
    private function handleLogout()
    {
        // Clear cookies
        setcookie('user_logged_in', '', time() - 3600, '/');
        setcookie('user_email', '', time() - 3600, '/');
        setcookie('user_name', '', time() - 3600, '/');

        // Clear session
        unset($_SESSION['user_logged_in']);
        unset($_SESSION['user_email']);
        unset($_SESSION['user_name']);

        // Redirect to home
        header('Location: index.php');
        exit;
    }

    /**
     * Check if user is logged in
     */
    public function isLoggedIn()
    {
        return isset($_COOKIE['user_logged_in']) && $_COOKIE['user_logged_in'] === 'true';
    }

    /**
     * Get data for view
     */
    public function getViewData()
    {
        return [
            'redirectUrl' => $this->redirectUrl,
            'isLoggedIn' => $this->isLoggedIn()
        ];
    }
}
