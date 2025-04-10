<?php
// Set proper headers for JSON response and CORS
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Create case_access_logs table if it doesn't exist
try {
<<<<<<< HEAD
   // Include database connection
   require_once '../database.php';
   
   $db = Database::getInstance()->getConnection();
   
   // Check if case_access_logs table exists
   $result = $db->query("SELECT to_regclass('public.case_access_logs')");
   $tableExists = $result->fetchColumn();
   
   if (!$tableExists) {
       // Create the table
       $db->exec('CREATE TABLE case_access_logs (
           id SERIAL PRIMARY KEY,
           case_id INTEGER NOT NULL,
           user_id INTEGER NOT NULL,
           accessed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
           FOREIGN KEY (case_id) REFERENCES cases(id),
           FOREIGN KEY (user_id) REFERENCES users(id)
       )');
       
       echo json_encode([
           'status' => 'success',
           'message' => 'case_access_logs table created successfully'
       ]);
   } else {
       echo json_encode([
           'status' => 'success',
           'message' => 'case_access_logs table already exists'
       ]);
   }
} catch (PDOException $e) {
   echo json_encode([
       'status' => 'error',
       'message' => 'Database error: ' . $e->getMessage()
   ]);
=======
    // Include database connection
    require_once '../police_system.db';
    
    $db = Database::getInstance()->getConnection();
    
    // Check if case_access_logs table exists
    $tableExists = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='case_access_logs'");
    
    if (!$tableExists->fetchColumn()) {
        // Create the table
        $db->exec('CREATE TABLE case_access_logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            case_id INTEGER NOT NULL,
            user_id INTEGER NOT NULL,
            accessed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (case_id) REFERENCES cases(id),
            FOREIGN KEY (user_id) REFERENCES users(id)
        )');
        
        echo json_encode([
            'status' => 'success',
            'message' => 'case_access_logs table created successfully'
        ]);
    } else {
        echo json_encode([
            'status' => 'success',
            'message' => 'case_access_logs table already exists'
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
>>>>>>> fb3bf7cf9b3167aad1cfc0ab7d9b91837188eb8b
}
?>

