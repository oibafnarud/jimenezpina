<?php
/**
 * Clase Database - Manejo de base de datos con MySQLi
 * Jiménez & Piña Survey Instruments
 */

class Database {
    private static $instance = null;
    private $connection;
    private $last_query;
    private $affected_rows;
    private $insert_id;
    
    /**
     * Constructor privado (patrón Singleton)
     */
    private function __construct() {
        $this->connect();
    }
    
    /**
     * Obtener instancia de la base de datos
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Conectar a la base de datos
     */
    private function connect() {
        $this->connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($this->connection->connect_error) {
            $this->error("Error de conexión: " . $this->connection->connect_error);
        }
        
        $this->connection->set_charset(DB_CHARSET);
    }
    
    /**
     * Ejecutar consulta SQL
     */
    public function query($sql, $params = []) {
        $this->last_query = $sql;
        
        if (empty($params)) {
            $result = $this->connection->query($sql);
        } else {
            $stmt = $this->prepare($sql, $params);
            $result = $stmt->get_result();
            $stmt->close();
        }
        
        if ($this->connection->error) {
            $this->error("Error en consulta: " . $this->connection->error);
        }
        
        $this->affected_rows = $this->connection->affected_rows;
        $this->insert_id = $this->connection->insert_id;
        
        return $result;
    }
    
    /**
     * Preparar y ejecutar consulta con parámetros
     */
    private function prepare($sql, $params) {
        $stmt = $this->connection->prepare($sql);
        
        if (!$stmt) {
            $this->error("Error al preparar consulta: " . $this->connection->error);
        }
        
        if (!empty($params)) {
            $types = '';
            $values = [];
            
            foreach ($params as $param) {
                if (is_int($param)) {
                    $types .= 'i';
                } elseif (is_float($param)) {
                    $types .= 'd';
                } else {
                    $types .= 's';
                }
                $values[] = $param;
            }
            
            $stmt->bind_param($types, ...$values);
        }
        
        $stmt->execute();
        
        return $stmt;
    }
    
    /**
     * Obtener todos los registros
     */
    public function fetchAll($sql, $params = []) {
        $result = $this->query($sql, $params);
        $data = [];
        
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        
        $result->free();
        
        return $data;
    }
    
    /**
     * Obtener un solo registro
     */
    public function fetchOne($sql, $params = []) {
        $result = $this->query($sql, $params);
        $data = $result->fetch_assoc();
        $result->free();
        
        return $data;
    }
    
    /**
     * Obtener valor de una columna
     */
    public function fetchColumn($sql, $params = [], $column = 0) {
        $result = $this->query($sql, $params);
        $row = $result->fetch_array();
        $result->free();
        
        return $row ? $row[$column] : null;
    }
    
    /**
     * Insertar registro
     */
    public function insert($table, $data) {
        $fields = array_keys($data);
        $values = array_values($data);
        $placeholders = array_fill(0, count($fields), '?');
        
        $sql = "INSERT INTO `$table` (`" . implode('`, `', $fields) . "`) VALUES (" . implode(', ', $placeholders) . ")";
        
        $this->query($sql, $values);
        
        return $this->insert_id;
    }
    
    /**
     * Actualizar registro(s)
     */
    public function update($table, $data, $where, $whereParams = []) {
        $fields = [];
        $values = [];
        
        foreach ($data as $key => $value) {
            $fields[] = "`$key` = ?";
            $values[] = $value;
        }
        
        $sql = "UPDATE `$table` SET " . implode(', ', $fields) . " WHERE $where";
        
        // Agregar parámetros del WHERE
        $values = array_merge($values, $whereParams);
        
        $this->query($sql, $values);
        
        return $this->affected_rows;
    }
    
    /**
     * Eliminar registro(s)
     */
    public function delete($table, $where, $params = []) {
        $sql = "DELETE FROM `$table` WHERE $where";
        
        $this->query($sql, $params);
        
        return $this->affected_rows;
    }
    
    /**
     * Contar registros
     */
    public function count($table, $where = '1=1', $params = []) {
        $sql = "SELECT COUNT(*) FROM `$table` WHERE $where";
        
        return (int) $this->fetchColumn($sql, $params);
    }
    
    /**
     * Verificar si existe un registro
     */
    public function exists($table, $where, $params = []) {
        return $this->count($table, $where, $params) > 0;
    }
    
    /**
     * Escapar string para SQL
     */
    public function escape($value) {
        return $this->connection->real_escape_string($value);
    }
    
    /**
     * Iniciar transacción
     */
    public function beginTransaction() {
        $this->connection->autocommit(false);
        $this->connection->begin_transaction();
    }
    
    /**
     * Confirmar transacción
     */
    public function commit() {
        $this->connection->commit();
        $this->connection->autocommit(true);
    }
    
    /**
     * Revertir transacción
     */
    public function rollback() {
        $this->connection->rollback();
        $this->connection->autocommit(true);
    }
    
    /**
     * Obtener último ID insertado
     */
    public function lastInsertId() {
        return $this->insert_id;
    }
    
    /**
     * Obtener filas afectadas
     */
    public function affectedRows() {
        return $this->affected_rows;
    }
    
    /**
     * Obtener última consulta ejecutada
     */
    public function lastQuery() {
        return $this->last_query;
    }
    
    /**
     * Ejecutar múltiples consultas
     */
    public function multiQuery($sql) {
        return $this->connection->multi_query($sql);
    }
    
    /**
     * Obtener información del servidor
     */
    public function serverInfo() {
        return $this->connection->server_info;
    }
    
    /**
     * Cerrar conexión
     */
    public function close() {
        if ($this->connection) {
            $this->connection->close();
        }
    }
    
    /**
     * Manejar errores
     */
    private function error($message) {
        if (DEVELOPMENT_MODE) {
            die("<h3>Error de Base de Datos</h3><p>$message</p><p>Consulta: {$this->last_query}</p>");
        } else {
            // En producción, registrar el error y mostrar mensaje genérico
            error_log("Database Error: $message - Query: {$this->last_query}");
            die("Ha ocurrido un error. Por favor, inténtelo más tarde.");
        }
    }
    
    /**
     * Destructor
     */
    public function __destruct() {
        $this->close();
    }
    
    /**
     * Prevenir clonación
     */
    private function __clone() {}
    
    /**
     * Prevenir deserialización
     */
    public function __wakeup() {
        throw new Exception("No se puede deserializar un singleton");
    }
}

// Función helper para acceso rápido
function db() {
    return Database::getInstance();
}