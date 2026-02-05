<?php

require_once __DIR__ . '/../../config/database.php';

/**
 * Customer Model
 * Handles all customer-related database operations
 */
class CustomerModel
{
    private $db;
    private $conn;

    public function __construct()
    {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
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

        $query = "INSERT INTO customer_entity (email, password_hash, full_name)
                  VALUES (?, ?, ?) RETURNING entity_id";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$email, $passwordHash, $fullName]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['entity_id'] ?? false;
        } catch (PDOException $e) {
            error_log("Database Error in createCustomer: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Authenticate customer
     */
    public function authenticate($email, $password)
    {
        $query = "SELECT entity_id, email, password_hash, full_name, is_active
                  FROM customer_entity
                  WHERE email = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$email]);
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);

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
        $query = "SELECT count(*) as count FROM customer_entity WHERE email = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$email]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result['count'] > 0;
    }

    /**
     * Get customer by ID
     */
    public function getCustomerById($id)
    {
        $query = "SELECT entity_id, email, full_name, is_active
                  FROM customer_entity
                  WHERE entity_id = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    /**
     * Get customer address
     */
    public function getAddress($userId)
    {
        // Try to get default address first, then most recent
        $query = "SELECT * FROM customer_address 
                  WHERE customer_id = ? 
                  ORDER BY is_default DESC, entity_id DESC 
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$userId]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    /**
     * Save or Update customer address
     */
    /**
     * Save or Update customer address
     */
    public function saveAddress($customerId, $addressData, $type = 'shipping')
    {
        // Try to SELECT by type. If column missing, add it.
        $street = $addressData['street'] ?? '';
        $city = $addressData['city'] ?? '';
        $state = $addressData['state'] ?? '';
        $postcode = $addressData['postcode'] ?? '';
        $country = $addressData['country'] ?? '';
        $telephone = $addressData['phone'] ?? $addressData['telephone'] ?? '';

        try {
            $query = "SELECT entity_id FROM customer_address WHERE customer_id = ? AND address_type = ? LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$customerId, $type]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Check for missing column error
            if (strpos($e->getMessage(), 'address_type') !== false) {
                // Add column
                $this->conn->exec("ALTER TABLE customer_address ADD COLUMN IF NOT EXISTS address_type VARCHAR(20) DEFAULT 'shipping'");

                // Retry Select
                $query = "SELECT entity_id FROM customer_address WHERE customer_id = ? AND address_type = ? LIMIT 1";
                $stmt = $this->conn->prepare($query);
                $stmt->execute([$customerId, $type]);
                $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                throw $e;
            }
        }

        if ($existing) {
            // Update
            $updateQuery = "UPDATE customer_address 
                            SET street = ?, city = ?, state = ?, postcode = ?, country = ?, telephone = ?
                            WHERE entity_id = ?";
            $updateStmt = $this->conn->prepare($updateQuery);
            return $updateStmt->execute([
                $street,
                $city,
                $state,
                $postcode,
                $country,
                $telephone,
                $existing['entity_id']
            ]);
        } else {
            // Insert
            $insertQuery = "INSERT INTO customer_address (customer_id, address_type, street, city, state, postcode, country, telephone)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $insertStmt = $this->conn->prepare($insertQuery);
            return $insertStmt->execute([
                $customerId,
                $type,
                $street,
                $city,
                $state,
                $postcode,
                $country,
                $telephone
            ]);
        }
    }
}
