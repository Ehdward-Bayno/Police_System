<?php
class Database {
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
}
?>

