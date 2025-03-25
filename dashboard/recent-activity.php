<?php
require_once '../includes/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Connect to database
$db = new Database();

try {
    // Get recent access logs
    $recent_activity = $db->fetchAll(
        "SELECT al.accessed_at, u.name AS user_name, c.case_number, c.case_title
         FROM case_access_logs al
         JOIN users u ON al.user_id = u.id
         JOIN cases c ON al.case_id = c.id
         ORDER BY al.accessed_at DESC
         LIMIT 10"
    ) ?: [];
    
    echo json_encode($recent_activity);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}

$db->close();
?>

