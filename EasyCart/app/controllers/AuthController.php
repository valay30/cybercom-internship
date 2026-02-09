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
        $this->redirectUrl = $_GET['redirect'] ?? 'index';
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
        $isAjax = isset($_POST['ajax']);

        if (!empty($email) && !empty($password)) {
            $customer = $this->customerModel->authenticate($email, $password);

            if ($customer) {
                // Login successful

                // Clear any pre-existing errors (e.g. from forced redirects)
                if (isset($_SESSION['error'])) {
                    unset($_SESSION['error']);
                }

                $this->createSession($customer);

                if ($isAjax) {
                    echo json_encode([
                        'success' => true,
                        'redirect' => $this->redirectUrl,
                        'message' => 'Login successful! Redirecting...'
                    ]);
                    exit;
                }

                // Redirect to intended page
                header('Location: ' . $this->redirectUrl);
                exit;
            } else {
                if ($isAjax) {
                    echo json_encode([
                        'success' => false,
                        'error' => 'Invalid email or password'
                    ]);
                    exit;
                }

                header('Location: login?error=invalid_credentials');
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
        $isAjax = isset($_POST['ajax']);

        if (!empty($fullname) && !empty($email) && !empty($password) && $password === $confirmPassword) {

            // Create customer in DB
            $customerId = $this->customerModel->createCustomer($email, $password, $fullname);

            if ($customerId) {
                // Auto-login after signup
                $customer = $this->customerModel->getCustomerById($customerId);
                if ($customer) {
                    $this->createSession($customer);
                }

                if ($isAjax) {
                    echo json_encode(['success' => true, 'redirect' => $this->redirectUrl, 'message' => 'Account created successfully!']);
                    exit;
                }

                // Redirect to intended page
                header('Location: ' . $this->redirectUrl);
                exit;
            } else {
                if ($isAjax) {
                    echo json_encode(['success' => false, 'error' => 'Email already registered. Please login.']);
                    exit;
                }
                header('Location: login?error=email_exists');
                exit;
            }
        } else {
            if ($isAjax) {
                echo json_encode(['success' => false, 'error' => 'Please check all fields.']);
                exit;
            }
        }
    }

    /**
     * Create session and cookies for logged in user
     */
    private function createSession($customer)
    {
        $fullname = $customer['full_name'];

        // Set login cookie
        // Set login cookie
        setcookie('user_logged_in', 'true', time() + (86400 * 30), '/');
        setcookie('user_id', $customer['entity_id'], time() + (86400 * 30), '/');

        // Set session
        $_SESSION['user_id'] = $customer['entity_id'];

        // --- NEW: Merge Guest Cart from Database ---
        require_once __DIR__ . '/../models/CartModel.php';
        $cartModel = new CartModel();
        $sessionId = session_id();

        // Merge guest cart (by session_id) into user cart
        $cartModel->mergeGuestCart($customer['entity_id'], $sessionId);

        // --- Fetch merged cart from DB back to Session ---
        $dbItems = $cartModel->getCartItems($customer['entity_id']);

        // Rebuild session cart
        foreach ($dbItems as $dbItem) {
            $sku = $dbItem['sku'];
            $_SESSION['cart'][$sku] = [
                'product_id' => $dbItem['entity_id'],
                'qty' => $dbItem['qty']
            ];
        }

        // --- NEW: Sync Wishlist with Database ---
        require_once __DIR__ . '/../models/WishlistModel.php';
        $wishlistModel = new WishlistModel();

        // Merge guest wishlist (session_id) into customer wishlist (customer_id)
        $sessionId = session_id();
        $wishlistModel->mergeGuestWishlist($customer['entity_id'], $sessionId);

        // Refresh session wishlist
        require_once __DIR__ . '/../models/ProductModel.php'; // Ensure ProductModel is loaded
        // We use WishlistController logic manually here to avoid instantiating the whole controller which might start session again

        $dbIds = $wishlistModel->getWishlistProductIds($customer['entity_id'], null);
        $productModel = new ProductModel();

        $skus = [];
        foreach ($dbIds as $eid) {
            $product = $productModel->getProductById($eid);
            if ($product) {
                $skus[] = $product['id'];
            }
        }
        $_SESSION['wishlist'] = $skus;
    }

    /**
     * Handle logout
     */
    private function handleLogout()
    {
        // Clear cookies
        // Clear cookies
        setcookie('user_logged_in', '', time() - 3600, '/');
        setcookie('user_id', '', time() - 3600, '/');

        // Clear session
        unset($_SESSION['user_id']);

        // Redirect to home
        header('Location: index');
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
