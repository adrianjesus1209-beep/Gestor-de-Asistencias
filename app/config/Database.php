<?php

namespace Config;

use PDO;
use PDOException;

class Database {
    private static $instance = null;
    private $conn;
    
    // Configuración del servidor
    private function __construct() {
        /**
         * CONFIGURACIÓN PARA PRODUCCIÓN (INFINITYFREE)
         * Debes obtener estos datos desde tu Panel de Control de InfinityFree -> MySQL Databases
         */
        $host     = 'localhost';            // Ejemplo: sql123.infinityfree.com
        $dbname   = 'unefa_attendance_db';  // Ejemplo: if0_12345678_unefa_db
        $username = 'root';                 // Ejemplo: if0_12345678
        $password = '';                     // Tu contraseña de la cuenta
        
        try {
            $this->conn = new PDO(
                "mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['api'])) {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => 'Error de base de datos intermitente. Intente luego.']);
                exit;
            }
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
