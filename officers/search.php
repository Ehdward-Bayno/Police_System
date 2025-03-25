<?php
require_once '../config.php';

// Handle GET request for officer search
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    
    // Get search query
    $query = isset($_GET['q']) ? $_GET['q'] : '';
    
    if (empty($query)) {
        echo json_encode(['results' => []]);
        exit;
    }
    
    try {
        // Get database connection
        $database = Database::getInstance();
        $db = $database->getConnection();
        
        // Search for officers and their cases
        $searchTerm = "%$query%";
        $stmt = $db->prepare("
            SELECT o.id, o.name as officerName, o.rank, c.id as caseId, c.case_number as caseNumber, 
                   c.case_title as caseTitle, c.status
            FROM officers o
            LEFT JOIN cases c ON o.id = c.officer_id
            WHERE o.name LIKE :query 
               OR o.badge_number LIKE :query 
               OR c.case_number LIKE :query 
               OR c.case_title LIKE :query
            ORDER BY o.name, c.case_number
        ");
        $stmt->bindParam(':query', $searchTerm);
        $stmt->execute();
        
        $results = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results[] = $row;
        }
        
        echo json_encode(['results' => $results]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
?>

