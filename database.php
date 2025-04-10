<?php
class Database {
<<<<<<< HEAD
   private $db;
   private static $instance = null;
   
   // PostgreSQL connection parameters
   private $host = 'localhost';
   private $port = '5432';
   private $dbname = 'police_system';
   private $user = 'police_user';
   private $password = 'ward123'; // Change this to your actual password
   
   // Constructor - connects to PostgreSQL database
   private function __construct() {
       try {
           // Connect to PostgreSQL
           $dsn = "pgsql:host={$this->host};port={$this->port};dbname={$this->dbname}";
           $this->db = new PDO($dsn, $this->user, $this->password);
           $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
       } catch (PDOException $e) {
           die("Database connection failed: " . $e->getMessage());
       }
   }
   
   // Singleton pattern to ensure only one database connection
   public static function getInstance() {
       if (self::$instance === null) {
           self::$instance = new self();
       }
       return self::$instance;
   }
   
   // Get the database connection
   public function getConnection() {
       return $this->db;
   }
=======
    private $db;
    private static $instance = null;
    
    // Database file path
    private $dbFile = __DIR__ . '/police_system.db';
    
    // Constructor - creates SQLite database if it doesn't exist
    private function __construct() {
        try {
            // Create database file if it doesn't exist
            if (!file_exists($this->dbFile)) {
                $this->createDatabase();
            }
            
            // Connect to the SQLite database
            $$this->db = new PDO('sqlite:' . $this->dbFile);
            $this->db->setAttribute(attribute: PDO::ATTR_ERRMODE, value: PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    
    // Singleton pattern to ensure only one database connection
    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    // Get the database connection
    public function getConnection(): SQLite3 {
        return $this->db;
    }
    
    // Create the database schema
    private function createDatabase(): bool {
        try {
            // Create the database file
            $db = new PDO(dsn: 'sqlite:' . $this->dbFile);
            $db->setAttribute(attribute: PDO::ATTR_ERRMODE, value: PDO::ERRMODE_EXCEPTION);
            
            // Enable foreign keys
            $db->exec(statement: 'PRAGMA foreign_keys = ON;');
            
            // Create users table
            $db->exec(statement: 'CREATE TABLE users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                email TEXT UNIQUE NOT NULL,
                badge_number TEXT UNIQUE NOT NULL,
                password TEXT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )');
            
            // Create officers table
            $db->exec(statement: 'CREATE TABLE officers (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                rank TEXT,
                badge_number TEXT UNIQUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )');
            
            // Create cases table
            $db->exec(statement: 'CREATE TABLE cases (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                case_number TEXT UNIQUE NOT NULL,
                case_title TEXT NOT NULL,
                officer_id INTEGER,
                description TEXT,
                status TEXT DEFAULT "Open",
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (officer_id) REFERENCES officers(id)
            )');
            
            // Create respondents table
            $db->exec(statement: 'CREATE TABLE respondents (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                case_id INTEGER NOT NULL,
                name TEXT NOT NULL,
                rank TEXT,
                unit TEXT,
                justification TEXT,
                remarks TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (case_id) REFERENCES cases(id)
            )');
            
            // Create documents table
            $db->exec(statement: 'CREATE TABLE documents (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                case_id INTEGER NOT NULL,
                file_name TEXT NOT NULL,
                file_path TEXT NOT NULL,
                document_type TEXT,
                uploaded_by INTEGER,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (case_id) REFERENCES cases(id),
                FOREIGN KEY (uploaded_by) REFERENCES users(id)
            )');
            
            return true;
        } catch (PDOException $e) {
            die("Database creation failed: " . $e->getMessage());
        }
    }
>>>>>>> fb3bf7cf9b3167aad1cfc0ab7d9b91837188eb8b
}
?>

