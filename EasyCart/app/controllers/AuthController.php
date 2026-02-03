<?php

require_once __DIR__ . '/../models/CustomerModel.php';

/**
 * AuthController
 * Handles authentication logic (login/signup) with Database integration
 */
class AuthController
{
    private $redirectUrl;
    private $customerModel;

    public function __construct()
    {
        // Get redirect URL from query parameter
        $this->redirectUrl = $_GET['redirect'] ?? 'index.php';
        $this->customerModel = new CustomerModel();
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
            case 'check_email':
                $this->handleCheckEmail();
                break;
        }
    }

    /**
     * Check if email exists (AJAX)
     */
    private function handleCheckEmail()
    {
        $email = $_POST['email'] ?? '';

        header('Content-Type: application/json');

        if (empty($email)) {
            echo json_encode(['error' => 'Email required']);
            exit;
        }

        try {
            $exists = $this->customerModel->emailExists($email);
            echo json_encode(['exists' => $exists]);
            exit;
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
            exit;
        }
    }

    /**
     * Handle login
     */
    private function handleLogin()
    {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (!empty($email) && !empty($password)) {
            $customer = $this->customerModel->authenticate($email, $password);

            if ($customer) {
                // Login successful
                $this->createSession($customer);

                // Redirect to intended page
                header('Location: ' . $this->redirectUrl);
                exit;
            } else {
                header('Location: login.php?error=invalid_credentials');
                exit;
            }
        }
    }

    /**
     * Handle signup
     */
    private function handleSignup()
    {
        error_log("=== SIGNUP ATTEMPT ===");

        $fullname = $_POST['fullname'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        error_log("Fullname: $fullname");
        error_log("Email: $email");
        error_log("Password length: " . strlen($password));

        if (!empty($fullname) && !empty($email) && !empty($password) && $password === $confirmPassword) {
            error_log("Validation passed, attempting to create customer...");

            // Create customer in DB (pass fullname directly)
            $customerId = $this->customerModel->createCustomer($email, $password, $fullname);

            error_log("Customer ID returned: " . ($customerId ? $customerId : 'FALSE'));

            if ($customerId) {
                error_log("Customer created successfully with ID: $customerId");

                // Auto-login after signup
                $customer = $this->customerModel->getCustomerById($customerId);
                if ($customer) {
                    error_log("Customer fetched, creating session...");
                    $this->createSession($customer);
                }

                // Redirect to intended page
                error_log("Redirecting to: " . $this->redirectUrl);
                header('Location: ' . $this->redirectUrl);
                exit;
            } else {
                // Signup failed (e.g. email exists)
                error_log("Signup failed - email may already exist");
                header('Location: login.php?error=email_exists');
                exit;
            }
        } else {
            error_log("Validation failed");
            error_log("Empty fullname: " . (empty($fullname) ? 'YES' : 'NO'));
            error_log("Empty email: " . (empty($email) ? 'YES' : 'NO'));
            error_log("Empty password: " . (empty($password) ? 'YES' : 'NO'));
            error_log("Passwords match: " . ($password === $confirmPassword ? 'YES' : 'NO'));
        }
    }

    /**
     * Create session and cookies for logged in user
     */
    private function createSession($customer)
    {
        $fullname = $customer['full_name'];

        // Set login cookie
        setcookie('user_logged_in', 'true', time() + (86400 * 30), '/');
        setcookie('user_email', $customer['email'], time() + (86400 * 30), '/');
        setcookie('user_name', $fullname, time() + (86400 * 30), '/');
        setcookie('user_id', $customer['entity_id'], time() + (86400 * 30), '/');

        // Set session
        $_SESSION['user_logged_in'] = true;
        $_SESSION['user_email'] = $customer['email'];
        $_SESSION['user_name'] = $fullname;
        $_SESSION['user_id'] = $customer['entity_id'];
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
        setcookie('user_id', '', time() - 3600, '/');

        // Clear session
        unset($_SESSION['user_logged_in']);
        unset($_SESSION['user_email']);
        unset($_SESSION['user_name']);
        unset($_SESSION['user_id']);

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
