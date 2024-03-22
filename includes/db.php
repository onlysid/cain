<?php
include 'db-config.php';

class DB {
    public $conn;

    public function connect() {
        $this->conn = null;

        try {
            $this->conn = new PDO("mysql:host=" . DB_SERVER .";dbname=" . DB_NAME, DB_USER, DB_PASS);
            // Set PDO to throw exceptions on errors
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $exception) {
            // Handle database connection error
            // header("Location: " . BASE_DIR . "/views/db-error");
            die;
        }

        return $this->conn;
    }

    // Execute a SELECT query and return a single result
    public function select($query, $params = []) {
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $exception) {
            // Handle query execution error
            // header("Location: " . BASE_DIR . "/views/db-error");
            die;
        }
    }

    // Execute a SELECT query and return all results
    public function selectAll($query, $params = []) {
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $exception) {
            // Handle query execution error
            // header("Location: " . BASE_DIR . "/views/db-error");
            die;
        }
    }

    // General queries
    public function query($query, $params = []) {
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            return $stmt->rowCount(); // Number of affected rows
        } catch (PDOException $exception) {
            // Handle query execution error
            // header("Location: " . BASE_DIR . "/views/db-error");
            die;
        }
    }


    // Begin a transaction
    public function beginTransaction() {
        return $this->conn->beginTransaction();
    }

    // Commit a transaction
    public function commit() {
        return $this->conn->commit();
    }

    // Roll back a transaction
    public function rollBack() {
        return $this->conn->rollBack();
    }
}


// Create a database connection
$cainDB = new DB();

// Get the database connection
$cainDB->connect();
