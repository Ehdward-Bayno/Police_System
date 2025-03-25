<?php
require_once '../includes/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Get case ID
$caseId = isset($_GET['id']) ? $_GET['id'] : '';

if (empty($caseId)) {
    http_response_code(400);
    echo json_encode(['error' => 'Case ID is required']);
    exit;
}

// Connect to database
$db = new Database();

try {
    // Get case details
    $case = $db->fetch(
        "SELECT c.id, c.case_number, c.case_title, c.description, c.status,
                o.id as officer_id, o.name as officer_name, o.rank as officer_rank
         FROM cases c
         LEFT JOIN officers o ON c.officer_id = o.id
         WHERE c.case_number = :case_id",
        [':case_id' => $caseId]
    );
    
    if (!$case) {
        http_response_code(404);
        echo json_encode(['error' => 'Case not found']);
        exit;
    }
    
    // Log this access
    try {
        $db->insert(
            "INSERT INTO case_access_logs (case_id, user_id) VALUES (:case_id, :user_id)",
            [
                ':case_id' => $case['id'],
                ':user_id' => $_SESSION['user_id']
            ]
        );
    } catch (Exception $e) {
        // Log error but continue execution
        error_log("Failed to log case access: " . $e->getMessage());
    }
    
    // Get respondents for this case
    $respondents = $db->fetchAll(
        "SELECT id, name, rank, unit, justification, remarks
         FROM respondents
         WHERE case_id = :case_id",
        [':case_id' => $case['id']]
    ) ?: [];
    
    // Get access logs for this case
    $access_logs = $db->fetchAll(
        "SELECT al.accessed_at, u.name, u.badge_number
         FROM case_access_logs al
         JOIN users u ON al.user_id = u.id
         WHERE al.case_id = :case_id
         ORDER BY al.accessed_at DESC
         LIMIT 10",
        [':case_id' => $case['id']]
    ) ?: [];
    
    // Add respondents and access logs to case data
    $case['respondents'] = $respondents;
    $case['access_logs'] = $access_logs;
    
    echo json_encode($case);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}

$db->close();
?>

