<?php
class Database {
    private $db;
    
    public function __construct() {
        $db_file = __DIR__ . '/../database/police_system.db';
        
        try {
            $this->db = new SQLite3($db_file);
            $this->db->exec('PRAGMA foreign_keys = ON');
        } catch (Exception $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    
    public function getConnection() {
        return $this->db;
    }
    
    public function query($sql, $params = []) {
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $param => $value) {
            $stmt->bindValue($param, $value);
        }
        
        $result = $stmt->execute();
        return $result;
    }
    
    public function fetchAll($sql, $params = []) {
        $result = $this->query($sql, $params);
        $rows = [];
        
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $rows[] = $row;
        }
        
        return $rows;
    }
    
    public function fetch($sql, $params = []) {
        $result = $this->query($sql, $params);
        return $result->fetchArray(SQLITE3_ASSOC);
    }
    
    public function insert($sql, $params = []) {
        $this->query($sql, $params);
        return $this->db->lastInsertRowID();
    }
    
    public function close() {
        $this->db->close();
    }
}
?>

