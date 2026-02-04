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
}
