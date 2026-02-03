<?php

/**
 * CartController
 * Handles all cart-related business logic
 */

class CartController
{
    private $products;
    private $cart;

    public function __construct($products)
    {
        $this->products = $products;

        // Initialize cart session
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        $this->cart = &$_SESSION['cart'];
    }

    /**
     * Handle POST actions (add, update, remove)
     */
    public function handleAction()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        $action = $_POST['action'] ?? '';
        $pid = $_POST['id'] ?? '';

        switch ($action) {
            case 'add':
                $this->addToCart($pid);
                break;
            case 'update':
                $this->updateQuantity($pid, (int) $_POST['qty']);
                break;
            case 'remove':
                $this->removeFromCart($pid);
                break;
        }

        // Handle AJAX requests
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'true') {
            $this->sendAjaxResponse($pid);
            exit;
        }

        // Redirect for non-AJAX requests
        header("Location: cart.php");
        exit;
    }

    /**
     * Add product to cart
     */
    private function addToCart($pid)
    {
        if (!isset($this->products[$pid])) {
            return;
        }

        if (isset($this->cart[$pid])) {
            $this->cart[$pid]['qty']++;
        } else {
            $this->cart[$pid] = [
                'name' => $this->products[$pid]['name'],
                'price' => $this->products[$pid]['price'],
                'image' => $this->products[$pid]['image'],
                'shipping_type' => $this->products[$pid]['shipping_type'],
                'qty' => 1
            ];
        }
    }

    /**
     * Update cart item quantity
     */
    private function updateQuantity($pid, $qty)
    {
        if ($qty > 0) {
            $this->cart[$pid]['qty'] = $qty;
        } else {
            unset($this->cart[$pid]);
        }
    }

    /**
     * Remove item from cart
     */
    private function removeFromCart($pid)
    {
        unset($this->cart[$pid]);
    }

    /**
     * Calculate subtotal with discounts
     */
    public function calculateSubtotal()
    {
        $subtotal = 0;

        foreach ($this->cart as $item) {
            if (isset($item['qty']) && isset($item['price'])) {
                $discount_percent = min($item['qty'], 50); // Max 50% discount
                $discounted_price = $item['price'] * (1 - ($discount_percent / 100));
                $subtotal += ($discounted_price * $item['qty']);
            }
        }

        return $subtotal;
    }

    /**
     * Get cart data for display
     */
    public function getCartData()
    {
        return $this->cart;
    }

    /**
     * Send AJAX response
     */
    private function sendAjaxResponse($pid)
    {
        $subtotal = $this->calculateSubtotal();
        $itemTotal = 0;
        $productPrice = 0;

        // Get the original product price
        if (isset($this->products[$pid])) {
            $productPrice = $this->products[$pid]['price'];
        }

        // Calculate item total
        if (isset($this->cart[$pid])) {
            $item = $this->cart[$pid];
            $discount_percent = min($item['qty'], 50);
            $discounted_price = $item['price'] * (1 - ($discount_percent / 100));
            $itemTotal = $discounted_price * $item['qty'];
        }

        echo json_encode([
            'success' => true,
            'subtotal' => $subtotal,
            'itemTotal' => $itemTotal,
            'productPrice' => $productPrice,
            'newQty' => isset($this->cart[$pid]) ? $this->cart[$pid]['qty'] : 0
        ]);
    }

    /**
     * Get checkout link based on login status
     */
    public function getCheckoutLink()
    {
        if (isset($_COOKIE['user_logged_in']) && $_COOKIE['user_logged_in'] === 'true') {
            return "checkout.php";
        }
        return "login.php?redirect=checkout.php";
    }
}
