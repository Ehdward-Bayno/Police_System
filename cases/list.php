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
    // Get all cases with last access information
    $cases = $db->fetchAll(
        "SELECT c.id, c.case_number, c.case_title, c.status, c.created_at,
                o.name AS officer_name, o.rank,
                u.name AS last_accessed_by,
                MAX(al.accessed_at) AS last_accessed_at
         FROM cases c
         LEFT JOIN officers o ON c.officer_id = o.id
         LEFT JOIN case_access_logs al ON c.id = al.case_id
         LEFT JOIN users u ON al.user_id = u.id
         GROUP BY c.id
         ORDER BY c.created_at DESC"
    ) ?: [];
    
    echo json_encode($cases);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}

$db->close();
?>

