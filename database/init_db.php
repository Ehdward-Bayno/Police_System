<?php
<<<<<<< HEAD
// Initialize PostgreSQL database using the schema.sql file
$host = 'localhost';
$port = '5432';
$dbname = 'police_system';
$user = 'postgres';
$password = 'your_password'; // Change this to your actual password

try {
    // Connect to PostgreSQL
    $dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";
    $db = new PDO($dsn, $user, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Read the schema file
    $schemaFile = __DIR__ . '/schema.sql';
    if (!file_exists($schemaFile)) {
        die("Schema file not found: {$schemaFile}");
    }
    
    $sql = file_get_contents($schemaFile);
    
    // Execute the schema SQL
    $db->exec($sql);
    
    echo "Database initialized successfully using schema.sql!\n";
    
    // Optionally add some sample data for testing
    // Add a sample user (password: admin123)
    $db->exec("INSERT INTO users (name, email, badge_number, password) 
              VALUES ('Admin User', 'admin@example.com', 'ADMIN-001', 
              '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi')
              ON CONFLICT (email) DO NOTHING");
    
    echo "Sample data added successfully!\n";
    
} catch (PDOException $e) {
    die("Database initialization failed: " . $e->getMessage());
}
=======
// Initialize SQLite database
$db_file = __DIR__ . '/police_system.db';
$db = new SQLite3(filename: $db_file);

// Enable foreign keys
$db->exec(query: 'PRAGMA foreign_keys = ON');

// Create users table
$db->exec(query: '
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT UNIQUE NOT NULL,
    badge_number TEXT UNIQUE NOT NULL,
    password TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)');

// Create officers table
$db->exec(query: '
CREATE TABLE IF NOT EXISTS officers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    rank TEXT,
    badge_number TEXT UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)');

// Create cases table
$db->exec(query: '
CREATE TABLE IF NOT EXISTS cases (
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
$db->exec(query: '
CREATE TABLE IF NOT EXISTS respondents (
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
$db->exec(query: '
CREATE TABLE IF NOT EXISTS documents (
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

// Create case access logs table
$db->exec(query: '
CREATE TABLE IF NOT EXISTS case_access_logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    case_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    accessed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (case_id) REFERENCES cases(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
)');

echo "Database initialized successfully!";
$db->close();
>>>>>>> fb3bf7cf9b3167aad1cfc0ab7d9b91837188eb8b
?>

