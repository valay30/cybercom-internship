<?php

/**
 * Database Connection Class
 * Handles PDO connection to PostgreSQL database
 */

class Database
{
    // Database credentials for PostgreSQL
    private $host = 'localhost';
    private $port = '5432';  // Default PostgreSQL port
    private $db_name = 'easycart_db';
    private $username = 'postgres';  // Your PostgreSQL username
    private $password = 'root';  // Your PostgreSQL password
    private $conn;

    /**
     * Get database connection
     * @return PDO|null
     */
    public function getConnection()
    {
        $this->conn = null;

        try {
            // PostgreSQL connection string
            $this->conn = new PDO(
                "pgsql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );

            // Set PDO attributes
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Connection Error: " . $e->getMessage();
            return null;
        }

        return $this->conn;
    }

    /**
     * Close database connection
     */
    public function closeConnection()
    {
        $this->conn = null;
    }
}
