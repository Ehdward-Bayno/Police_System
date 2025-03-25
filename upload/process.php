<?php
require_once '../config.php';

// Handle POST request for file uploads
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    
    // Check if file was uploaded
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['error' => 'No file uploaded or upload error']);
        exit;
    }
    
    // Get form data
    $caseNumber = isset($_POST['caseNumber']) ? $_POST['caseNumber'] : '';
    $caseTitle = isset($_POST['caseTitle']) ? $_POST['caseTitle'] : '';
    $officerName = isset($_POST['officerName']) ? $_POST['officerName'] : '';
    $rank = isset($_POST['rank']) ? $_POST['rank'] : '';
    $description = isset($_POST['caseDescription']) ? $_POST['caseDescription'] : '';
    $documentType = isset($_POST['documentType']) ? $_POST['documentType'] : '';
    
    if (empty($caseNumber) || empty($caseTitle) || empty($officerName)) {
        http_response_code(400);
        echo json_encode(['error' => 'Case number, title, and officer name are required']);
        exit;
    }
    
    // Process uploaded file
    $file = $_FILES['file'];
    $fileName = $file['name'];
    $fileTmpPath = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileType = $file['type'];
    
    // Check file size (max 10MB)
    if ($fileSize > 10 * 1024 * 1024) {
        http_response_code(400);
        echo json_encode(['error' => 'File size exceeds the limit (10MB)']);
        exit;
    }
    
    // Check file type
    $allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 
                    'image/jpeg', 'image/png', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
    
    if (!in_array($fileType, $allowedTypes)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid file type. Allowed types: PDF, DOC, DOCX, JPG, PNG, XLS, XLSX']);
        exit;
    }
    
    // Create upload directory if it doesn't exist
    if (!file_exists(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0777, true);
    }
    
    // Generate unique filename
    $newFileName = uniqid() . '_' . $fileName;
    $uploadPath = UPLOAD_DIR . $newFileName;
    
    // Connect to database
    $database = Database::getInstance();
    $db = $database->getConnection();
    
    try {
        // Start transaction
        $db->beginTransaction();
        
        // Check if officer exists, create if not
        $officerStmt = $db->prepare("SELECT id FROM officers WHERE name = :name AND rank = :rank");
        $officerStmt->bindParam(':name', $officerName);
        $officerStmt->bindParam(':rank', $rank);
        $officerStmt->execute();
        
        if ($officerStmt->rowCount() === 0) {
            // Create new officer
            $newOfficerStmt = $db->prepare("INSERT INTO officers (name, rank, badge_number) VALUES (:name, :rank, :badge_number)");
            $newOfficerStmt->bindParam(':name', $officerName);
            $newOfficerStmt->bindParam(':rank', $rank);
            $newOfficerStmt->bindParam(':badge_number', $caseNumber); // Using case number as badge number for simplicity
            $newOfficerStmt->execute();
            
            $officerId = $db->lastInsertId();
        } else {
            $officer = $officerStmt->fetch(PDO::FETCH_ASSOC);
            $officerId = $officer['id'];
        }
        
        // Check if case exists, create if not
        $caseStmt = $db->prepare("SELECT id FROM cases WHERE case_number = :case_number");
        $caseStmt->bindParam(':case_number', $caseNumber);
        $caseStmt->execute();
        
        if ($caseStmt->rowCount() === 0) {
            // Create new case
            $newCaseStmt = $db->prepare("
                INSERT INTO cases (case_number, case_title, officer_id, description, status) 
                VALUES (:case_number, :case_title, :officer_id, :description, 'Open')
            ");
            $newCaseStmt->bindParam(':case_number', $caseNumber);
            $newCaseStmt->bindParam(':case_title', $caseTitle);
            $newCaseStmt->bindParam(':officer_id', $officerId);
            $newCaseStmt->bindParam(':description', $description);
            $newCaseStmt->execute();
            
            $caseId = $db->lastInsertId();
        } else {
            $case = $caseStmt->fetch(PDO::FETCH_ASSOC);
            $caseId = $case['id'];
        }
        
        // Move uploaded file
        if (move_uploaded_file($fileTmpPath, $uploadPath)) {
            // Save document in database
            $docStmt = $db->prepare("
                INSERT INTO documents (case_id, file_name, file_path, document_type, uploaded_by) 
                VALUES (:case_id, :file_name, :file_path, :document_type, :uploaded_by)
            ");
            $docStmt->bindParam(':case_id', $caseId);
            $docStmt->bindParam(':file_name', $fileName);
            $docStmt->bindParam(':file_path', $newFileName);
            $docStmt->bindParam(':document_type', $documentType);
            $docStmt->bindParam(':uploaded_by', $_SESSION['user_id']);
            $docStmt->execute();
            
            // Process Excel file for data extraction
            if ($fileType === 'application/vnd.ms-excel' || $fileType === 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
                // In a real application, you would use a PHP Excel library like PhpSpreadsheet
                // to extract data from the Excel file and populate the database
            }
            
            // Commit transaction
            $db->commit();
            
            echo json_encode([
                'success' => true,
                'message' => 'File uploaded successfully',
                'file' => [
                    'name' => $fileName,
                    'path' => $newFileName,
                    'type' => $fileType,
                    'size' => $fileSize
                ]
            ]);
        } else {
            throw new Exception('Failed to move uploaded file');
        }
    } catch (Exception $e) {
        // Rollback transaction on error
        $db->rollBack();
        http_response_code(500);
        echo json_encode(['error' => 'Upload failed: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
?>

