<?php
require_once '../config.php';

// Handle POST request for saving case details
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    
    // Get JSON data from request body
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    if (!isset($data['caseNumber']) || !isset($data['caseTitle'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Case number and title are required']);
        exit;
    }
    
    try {
        // Get database connection
        $database = Database::getInstance();
        $db = $database->getConnection();
        
        // Start transaction
        $db->beginTransaction();
        
        // Check if case exists
        $stmt = $db->prepare("SELECT id FROM cases WHERE case_number = :case_number");
        $stmt->bindParam(':case_number', $data['caseNumber']);
        $stmt->execute();
        
        $caseId = null;
        $isNewCase = false;
        
        if ($stmt->rowCount() === 0) {
            // Create new case
            $isNewCase = true;
            
            // Check if officer exists, create if not
            $officerId = null;
            if (isset($data['officerName'])) {
                $stmt = $db->prepare("SELECT id FROM officers WHERE name = :name");
                $stmt->bindParam(':name', $data['officerName']);
                $stmt->execute();
                
                if ($stmt->rowCount() === 0) {
                    // Create new officer
                    $stmt = $db->prepare("INSERT INTO officers (name, rank, badge_number) VALUES (:name, :rank, :badge_number)");
                    $stmt->bindParam(':name', $data['officerName']);
                    $stmt->bindParam(':rank', $data['officerRank'] ?? null);
                    $stmt->bindParam(':badge_number', $data['officerBadge'] ?? null);
                    $stmt->execute();
                    
                    $officerId = $db->lastInsertId();
                } else {
                    $officer = $stmt->fetch(PDO::FETCH_ASSOC);
                    $officerId = $officer['id'];
                }
            }
            
            // Insert new case
            $stmt = $db->prepare("
                INSERT INTO cases (case_number, case_title, officer_id, description, status) 
                VALUES (:case_number, :case_title, :officer_id, :description, :status)
            ");
            $stmt->bindParam(':case_number', $data['caseNumber']);
            $stmt->bindParam(':case_title', $data['caseTitle']);
            $stmt->bindParam(':officer_id', $officerId);
            $stmt->bindParam(':description', $data['description'] ?? null);
            $stmt->bindParam(':status', $data['status'] ?? 'Open');
            $stmt->execute();
            
            $caseId = $db->lastInsertId();
        } else {
            // Update existing case
            $case = $stmt->fetch(PDO::FETCH_ASSOC);
            $caseId = $case['id'];
            
            $stmt = $db->prepare("
                UPDATE cases 
                SET case_title = :case_title, description = :description, status = :status 
                WHERE id = :id
            ");
            $stmt->bindParam(':case_title', $data['caseTitle']);
            $stmt->bindParam(':description', $data['description'] ?? null);
            $stmt->bindParam(':status', $data['status'] ?? 'Open');
            $stmt->bindParam(':id', $caseId);
            $stmt->execute();
        }
        
        // Handle respondents if provided
        if (isset($data['respondents']) && is_array($data['respondents'])) {
            // Delete existing respondents if updating
            if (!$isNewCase) {
                $stmt = $db->prepare("DELETE FROM respondents WHERE case_id = :case_id");
                $stmt->bindParam(':case_id', $caseId);
                $stmt->execute();
            }
            
            // Insert new respondents
            foreach ($data['respondents'] as $respondent) {
                $stmt = $db->prepare("
                    INSERT INTO respondents (case_id, name, rank, unit, justification, remarks) 
                    VALUES (:case_id, :name, :rank, :unit, :justification, :remarks)
                ");
                $stmt->bindParam(':case_id', $caseId);
                $stmt->bindParam(':name', $respondent['name']);
                $stmt->bindParam(':rank', $respondent['rank'] ?? null);
                $stmt->bindParam(':unit', $respondent['unit'] ?? null);
                $stmt->bindParam(':justification', $respondent['justification'] ?? null);
                $stmt->bindParam(':remarks', $respondent['remarks'] ?? null);
                $stmt->execute();
            }
        }
        
        // Commit transaction
        $db->commit();
        
        echo json_encode([
            'success' => true,
            'message' => $isNewCase ? 'Case created successfully' : 'Case updated successfully',
            'caseId' => $caseId
        ]);
    } catch (PDOException $e) {
        // Rollback transaction on error
        $db->rollBack();
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
?>

