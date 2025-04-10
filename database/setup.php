<?php
// Database initialization and setup
<<<<<<< HEAD
$host = 'localhost';
$port = '5432';
$dbname = 'police_system';
$user = 'police_user';
$password = 'ward123'; // Change this to your actual password

try {
   // Connect to PostgreSQL database
   $dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";
   $db = new PDO($dsn, $user, $password);
   $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
   
   // Check if tables exist
   $result = $db->query("SELECT to_regclass('public.users')");
   $tableExists = $result->fetchColumn();
   
   // Create tables if they don't exist
   if (!$tableExists) {
       // Users table
       $db->exec('CREATE TABLE users (
           id SERIAL PRIMARY KEY,
           name TEXT NOT NULL,
           email TEXT UNIQUE NOT NULL,
           badge_number TEXT UNIQUE NOT NULL,
           password TEXT NOT NULL,
           created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
       )');
       
       // Officers table
       $db->exec('CREATE TABLE officers (
           id SERIAL PRIMARY KEY,
           name TEXT NOT NULL,
           rank TEXT,
           badge_number TEXT UNIQUE,
           created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
       )');
       
       // Cases table
       $db->exec('CREATE TABLE cases (
           id SERIAL PRIMARY KEY,
           case_number TEXT UNIQUE NOT NULL,
           case_title TEXT NOT NULL,
           officer_id INTEGER,
           description TEXT,
           status TEXT DEFAULT \'Open\',
           created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
           FOREIGN KEY (officer_id) REFERENCES officers(id)
       )');
       
       // Respondents table
       $db->exec('CREATE TABLE respondents (
           id SERIAL PRIMARY KEY,
           case_id INTEGER NOT NULL,
           name TEXT NOT NULL,
           rank TEXT,
           unit TEXT,
           justification TEXT,
           remarks TEXT,
           created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
           FOREIGN KEY (case_id) REFERENCES cases(id)
       )');
       
       // Documents table
       $db->exec('CREATE TABLE documents (
           id SERIAL PRIMARY KEY,
           case_id INTEGER NOT NULL,
           file_name TEXT NOT NULL,
           file_path TEXT NOT NULL,
           document_type TEXT,
           uploaded_by INTEGER,
           created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
           FOREIGN KEY (case_id) REFERENCES cases(id),
           FOREIGN KEY (uploaded_by) REFERENCES users(id)
       )');
       
       // Case access logs table
       $db->exec('CREATE TABLE case_access_logs (
           id SERIAL PRIMARY KEY,
           case_id INTEGER NOT NULL,
           user_id INTEGER NOT NULL,
           accessed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
           FOREIGN KEY (case_id) REFERENCES cases(id),
           FOREIGN KEY (user_id) REFERENCES users(id)
       )');
       
       echo "Database and tables created successfully.";
   }
} catch (PDOException $e) {
   die("Database error: " . $e->getMessage());
}

/**
* Get database connection
* @return PDO Database connection
*/
function getDB() {
   global $host, $port, $dbname, $user, $password;
   $dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";
   $db = new PDO($dsn, $user, $password);
   $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
   return $db;
=======
$databaseFile = __DIR__ . '/police_system.sqlite';
$createTables = !file_exists($databaseFile);

try {
    // Connect to SQLite database
    $db = new PDO('sqlite:' . $databaseFile);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create tables if database is new
    if ($createTables) {
        // Users table
        $db->exec('CREATE TABLE users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT UNIQUE NOT NULL,
            badge_number TEXT UNIQUE NOT NULL,
            password TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )');
        
        // Officers table
        $db->exec('CREATE TABLE officers (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            rank TEXT,
            badge_number TEXT UNIQUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )');
        
        // Cases table
        $db->exec('CREATE TABLE cases (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            case_number TEXT UNIQUE NOT NULL,
            case_title TEXT NOT NULL,
            officer_id INTEGER,
            description TEXT,
            status TEXT DEFAULT "Open",
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (officer_id) REFERENCES officers(id)
        )');
        
        // Respondents table
        $db->exec('CREATE TABLE respondents (
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
        
        // Documents table
        $db->exec('CREATE TABLE documents (
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
        
        echo "Database and tables created successfully.";
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

/**
 * Get database connection
 * @return PDO Database connection
 */
function getDB() {
    global $databaseFile;
    $db = new PDO('sqlite:' . $databaseFile);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $db;
>>>>>>> fb3bf7cf9b3167aad1cfc0ab7d9b91837188eb8b
}
?>

