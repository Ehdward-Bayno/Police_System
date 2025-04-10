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
    // Get total case count
    $total = $db->fetch("SELECT COUNT(*) as count FROM cases");
    $total = $total ? $total['count'] : 0;
    
    // Get open case count
    $open = $db->fetch("SELECT COUNT(*) as count FROM cases WHERE status = 'Open'");
    $open = $open ? $open['count'] : 0;
    
    // Get closed case count
    $closed = $db->fetch("SELECT COUNT(*) as count FROM cases WHERE status = 'Closed'");
    $closed = $closed ? $closed['count'] : 0;
    
    // Get pending case count
    $pending = $db->fetch("SELECT COUNT(*) as count FROM cases WHERE status = 'Pending' OR status = 'Under Review'");
    $pending = $pending ? $pending['count'] : 0;
    
    // Return statistics
    echo json_encode([
        'total' => $total,
        'open' => $open,
        'closed' => $closed,
        'pending' => $pending
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}

$db->close();
?>

