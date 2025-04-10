<?php
class Database {
<<<<<<< HEAD
   private $db;
   
   // PostgreSQL connection parameters
   private $host = 'localhost';
   private $port = '5432';
   private $dbname = 'police_system';
   private $user = 'police_user';
   private $password = 'ward123'; // Change this to your actual password
   
   public function __construct() {
       try {
           $dsn = "pgsql:host={$this->host};port={$this->port};dbname={$this->dbname}";
           $this->db = new PDO($dsn, $this->user, $this->password);
           $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
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
       
       $stmt->execute();
       return $stmt;
   }
   
   public function fetchAll($sql, $params = []) {
       $stmt = $this->query($sql, $params);
       return $stmt->fetchAll(PDO::FETCH_ASSOC);
   }
   
   public function fetch($sql, $params = []) {
       $stmt = $this->query($sql, $params);
       return $stmt->fetch(PDO::FETCH_ASSOC);
   }
   
   public function insert($sql, $params = []) {
       // Modify the SQL to return the ID for PostgreSQL
       if (stripos($sql, 'INSERT INTO') === 0 && stripos($sql, 'RETURNING') === false) {
           $sql .= ' RETURNING id';
       }
       
       $stmt = $this->query($sql, $params);
       $result = $stmt->fetch(PDO::FETCH_ASSOC);
       
       // Return the ID from the RETURNING clause
       return $result['id'] ?? null;
   }
   
   public function close() {
       $this->db = null;
   }
=======
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
>>>>>>> fb3bf7cf9b3167aad1cfc0ab7d9b91837188eb8b
}
?>

