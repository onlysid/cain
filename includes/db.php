<?php

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
            throw $exception;
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
            throw $exception;
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
            throw $exception;
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

    // Get operator information
    public function getOperatorInfo($operatorId) {
        $query = "SELECT * FROM users WHERE operator_id = ?;";
        $params = [$operatorId];
        return $this->select($query, $params);
    }

    // Get current user info
    public function currentUserInfo($userId) {
        $query = "SELECT * FROM users WHERE user_id = ?;";
        $params = [$userId];
        $currentUser = $this->select($query, $params);

        if($currentUser) {
            return $this->select($query, $params);
        } else {
            Session::logout();
            Session::setNotice("You have been logged out.");
            header('Location: /');
        }

    }
}


// Create a database connection
$cainDB = new DB();

// Get the database connection
$cainDB->connect();
