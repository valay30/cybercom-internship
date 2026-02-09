<?php

/**
 * CartController
 * Handles all cart-related business logic
 */

require_once __DIR__ . '/../models/ProductModel.php';
require_once __DIR__ . '/../models/CartModel.php';

class CartController
{
    private $productModel;
    private $cartModel;
    private $cart;

    public function __construct()
    {
        $this->productModel = new ProductModel();
        $this->cartModel = new CartModel();

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
        header("Location: cart");
        exit;
    }

    /**
     * Add product to cart
     */
    private function addToCart($pid)
    {
        // $pid IS THE SKU coming from frontend
        $product = $this->productModel->getProductBySku($pid);

        if (!$product) {
            return;
        }

        // Update Session Cart - Store ONLY essentials
        if (isset($this->cart[$pid])) {
            $this->cart[$pid]['qty']++;
        } else {
            $this->cart[$pid] = [
                'product_id' => $product['entity_id'],
                'qty' => 1
            ];
        }

        // DB Persistence for Logged In Users
        if (isset($_SESSION['user_id'])) {
            $this->cartModel->addItem($_SESSION['user_id'], $product['entity_id'], 1);
        } else {
            // DB Persistence for Guest Users using session_id
            $sessionId = session_id();
            $this->cartModel->addItem(null, $product['entity_id'], 1, $sessionId);
        }
    }

    /**
     * Update cart item quantity
     */
    private function updateQuantity($pid, $qty)
    {
        // DB Persistence
        if (isset($_SESSION['user_id']) && isset($this->cart[$pid])) {
            $productId = $this->cart[$pid]['product_id'] ?? null;
            if ($productId) {
                $this->cartModel->updateQty($_SESSION['user_id'], $productId, $qty);
            }
        } elseif (isset($this->cart[$pid])) {
            // Guest user - persist to DB
            $productId = $this->cart[$pid]['product_id'] ?? null;
            if ($productId) {
                $sessionId = session_id();
                $this->cartModel->updateQty(null, $productId, $qty, $sessionId);
            }
        }

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
        // DB Persistence
        if (isset($_SESSION['user_id']) && isset($this->cart[$pid])) {
            $productId = $this->cart[$pid]['product_id'] ?? null;
            if ($productId) {
                $this->cartModel->removeItem($_SESSION['user_id'], $productId);
            }
        } elseif (isset($this->cart[$pid])) {
            // Guest user - persist to DB
            $productId = $this->cart[$pid]['product_id'] ?? null;
            if ($productId) {
                $sessionId = session_id();
                $this->cartModel->removeItem(null, $productId, $sessionId);
            }
        }

        unset($this->cart[$pid]);
    }

    /**
     * Calculate subtotal via enriched data
     */
    public function calculateSubtotal()
    {
        $subtotal = 0;
        $enrichedCart = $this->getCartData(); // Fetch full details to get prices

        foreach ($enrichedCart as $item) {
            if (isset($item['qty']) && isset($item['price'])) {
                $discount_percent = min($item['qty'], 50); // Max 50% discount
                $discounted_price = $item['price'] * (1 - ($discount_percent / 100));
                $subtotal += ($discounted_price * $item['qty']);
            }
        }

        return $subtotal;
    }

    /**
     * Get cart data for display (Enriched with DB data)
     */
    public function getCartData()
    {
        $enrichedCart = [];

        foreach ($this->cart as $sku => $item) {
            $productId = $item['product_id'] ?? null;
            if ($productId) {
                $product = $this->productModel->getProductById($productId);
                if ($product) {
                    // Merge DB details with Session data (qty)
                    $enrichedItem = $product;
                    $enrichedItem['qty'] = $item['qty'];
                    // Ensure product_id is preserved/accessible
                    $enrichedItem['product_id'] = $productId;

                    $enrichedCart[$sku] = $enrichedItem;
                }
            }
        }

        return $enrichedCart;
    }

    /**
     * Send AJAX response
     */
    private function sendAjaxResponse($pid)
    {
        $subtotal = $this->calculateSubtotal();
        $itemTotal = 0;
        $productPrice = 0;

        // We need full details to calculate item total
        $enrichedCart = $this->getCartData();

        if (isset($enrichedCart[$pid])) {
            $item = $enrichedCart[$pid];
            $productPrice = $item['price'];

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
            return "checkout";
        }
        return "login?redirect=checkout";
    }
}
