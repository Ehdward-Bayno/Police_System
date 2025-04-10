<?php
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
?>

