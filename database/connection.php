<?php
// Database connection utility

/**
 * Get database configuration
 * @return array Database configuration
 */
function getDatabaseConfig() {
    $config = include __DIR__ . '/config.php';
    return $config;
}

/**
 * Get database connection
 * @return PDO Database connection
 */
function getDatabaseConnection() {
    $config = getDatabaseConfig();
    
    $dsn = "pgsql:host={$config['host']};port={$config['port']};dbname={$config['dbname']}";
    $db = new PDO($dsn, $config['user'], $config['password']);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    return $db;
}
?>

