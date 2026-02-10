<?php

require_once __DIR__ . '/../../config/database.php';

/**
 * Customer Model
 * Handles all customer-related database operations
 */
class CustomerModel
{
    private $qb;

    public function __construct($pdo = null)
    {
        require_once __DIR__ . '/../core/QueryBuilder.php';
        $this->qb = new QueryBuilder($pdo);
    }

    /**
     * Create new customer
     */
    public function createCustomer($email, $password, $fullName)
    {
        if ($this->emailExists($email)) {
            return false;
        }

        // Hash password
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        return $this->qb->table('customer_entity')->insertGetId([
            'email' => $email,
            'password_hash' => $passwordHash,
            'full_name' => $fullName
        ], 'entity_id');
    }

    /**
     * Authenticate customer
     */
    public function authenticate($email, $password)
    {
        $customer = $this->qb->table('customer_entity')
            ->select(['entity_id', 'email', 'password_hash', 'full_name', 'is_active', 'is_admin'])
            ->where('email', $email)
            ->first();

        if ($customer && password_verify($password, $customer['password_hash'])) {
            unset($customer['password_hash']); // Remove hash before returning
            return $customer;
        }

        return false;
    }

    /**
     * Check if email exists
     */
    public function emailExists($email)
    {
        return $this->qb->table('customer_entity')
            ->where('email', $email)
            ->count() > 0;
    }

    /**
     * Get customer by ID
     */
    public function getCustomerById($id)
    {
        return $this->qb->table('customer_entity')
            ->select(['entity_id', 'email', 'full_name', 'is_active'])
            ->where('entity_id', $id)
            ->first();
    }

    /**
     * Check if user is admin
     */
    public function isAdmin($userId)
    {
        $isAdmin = $this->qb->table('customer_entity')
            ->where('entity_id', $userId)
            ->value('is_admin');

        return $isAdmin === true;
    }

    /**
     * Get customer address
     */
    public function getAddress($userId)
    {
        // Try to get default address first, then most recent (highest ID)
        // using orderBy clause logic
        return $this->qb->table('customer_address')
            ->where('customer_id', $userId)
            ->orderBy('is_default', 'DESC')
            ->orderBy('entity_id', 'DESC')
            ->first();
    }

    /**
     * Save or Update customer address
     */
    public function saveAddress($customerId, $addressData, $type = 'shipping')
    {
        $street = $addressData['street'] ?? '';
        $city = $addressData['city'] ?? '';
        $state = $addressData['state'] ?? '';
        $postcode = $addressData['postcode'] ?? '';
        $country = $addressData['country'] ?? '';
        $telephone = $addressData['phone'] ?? $addressData['telephone'] ?? '';

        $pdo = $this->qb->getPdo();
        $inTransaction = $pdo->inTransaction();

        try {
            if ($inTransaction) {
                // Use SAVEPOINT to handle potential schema error without aborting main transaction
                $pdo->exec("SAVEPOINT check_address_col");
            }

            // Check if address exists
            $existing = $this->qb->table('customer_address')
                ->where('customer_id', $customerId)
                ->where('address_type', $type)
                ->first();
        } catch (Exception $e) {
            if ($inTransaction) {
                $pdo->exec("ROLLBACK TO SAVEPOINT check_address_col");
            }

            // Check for missing column error (PostgreSQL/Generic)
            $msg = $e->getMessage();
            if (strpos($msg, 'address_type') !== false || strpos($msg, 'column') !== false) {
                $pdo->exec("ALTER TABLE customer_address ADD COLUMN IF NOT EXISTS address_type VARCHAR(20) DEFAULT 'shipping'");

                // Retry Select
                $existing = $this->qb->table('customer_address')
                    ->where('customer_id', $customerId)
                    ->where('address_type', $type)
                    ->first();
            } else {
                throw $e;
            }
        }

        $data = [
            'street' => $street,
            'city' => $city,
            'state' => $state,
            'postcode' => $postcode,
            'country' => $country,
            'telephone' => $telephone
        ];

        if (!empty($existing)) {
            // Update
            return $this->qb->table('customer_address')
                ->where('entity_id', $existing['entity_id'])
                ->update($data);
        } else {
            // Insert
            $data['customer_id'] = $customerId;
            $data['address_type'] = $type;
            return $this->qb->table('customer_address')->insert($data);
        }
    }

    /**
     * Delete customer and all related data
     */
    public function deleteCustomer($customerId)
    {
        try {
            $this->qb->beginTransaction();

            // 1. Delete Wishlist Items
            $this->qb->table('customer_wishlist')->where('customer_id', $customerId)->delete();

            // 2. Delete Cart Items
            $this->qb->table('sales_cart')->where('customer_id', $customerId)->delete();

            // 3. Delete Address Book
            $this->qb->table('customer_address')->where('customer_id', $customerId)->delete();

            // 4. Delete Orders (and related items/addresses)
            // Get Order IDs first
            $orderIds = $this->qb->table('sales_order')
                ->where('customer_id', $customerId)
                ->pluck('order_id');

            if (!empty($orderIds)) {
                $this->qb->table('sales_order_product')->whereIn('order_id', $orderIds)->delete();
                $this->qb->table('sales_order_address')->whereIn('order_id', $orderIds)->delete();
                $this->qb->table('sales_order')->where('customer_id', $customerId)->delete();
            }

            // 5. Delete Customer Entity
            $result = $this->qb->table('customer_entity')->where('entity_id', $customerId)->delete();

            $this->qb->commit();
            return $result;
        } catch (Exception $e) {
            $this->qb->rollBack();
            error_log("Error deleting customer: " . $e->getMessage());
            return false;
        }
    }
}
