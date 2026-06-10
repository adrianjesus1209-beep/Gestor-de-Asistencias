<?php

namespace Config;

use PDO;
use PDOException;

class Database {
    private static $instance = null;
    private $conn;
    
    // Configuración del servidor
    private function __construct() {
        $host = 'localhost';
        $dbname = 'unefa_attendance_db';
        $username = 'root';
        $password = '';
        
        try {
            $this->conn = new PDO(
                "mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }
    
    // Obtener la instancia (Singleton)
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    // Obtener la conexión
    public function getConnection() {
        return $this->conn;
    }
    
    private function __clone() {}
    public function __wakeup() {}
}
