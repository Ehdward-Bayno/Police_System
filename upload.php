<?php
require_once 'includes/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!checkCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid form submission. Please try again.';
    } else {
        $case_number = $_POST['caseNumber'] ?? '';
        $case_title = $_POST['caseTitle'] ?? '';
        $officer_name = $_POST['officerName'] ?? '';
        $rank = $_POST['rank'] ?? '';
        $case_description = $_POST['caseDescription'] ?? '';
        $document_type = $_POST['documentType'] ?? '';
        
        // Validate required fields
        if (empty($case_number) || empty($case_title) || empty($officer_name)) {
            $error = 'Case number, title, and officer name are required.';
        } elseif (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            $error = 'Please select a file to upload.';
        } else {
            // Process uploaded file
            $file = $_FILES['file'];
            $file_name = $file['name'];
            $file_tmp_path = $file['tmp_name'];
            $file_size = $file['size'];
            $file_type = $file['type'];
            
            // Check file size (max 10MB)
            if ($file_size > 10 * 1024 * 1024) {
                $error = 'File size exceeds the limit (10MB).';
            } else {
                // Check file type
                $allowed_types = [
                    'application/pdf', 
                    'application/msword', 
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'image/jpeg', 
                    'image/png', 
                    'application/vnd.ms-excel', 
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                ];
                
                if (!in_array($file_type, $allowed_types)) {
                    $error = 'Invalid file type. Allowed types: PDF, DOC, DOCX, JPG, PNG, XLS, XLSX.';
                } else {
                    // Generate unique filename
                    $new_file_name = uniqid() . '_' . $file_name;
                    $upload_path = UPLOADS_DIR . '/' . $new_file_name;
                    
                    // Connect to database
                    $db = new Database();
                    
                    try {
                        // Start transaction
                        $db->query('BEGIN TRANSACTION');
                        
                        // Check if officer exists, create if not
                        $officer = $db->fetch(
                            "SELECT id FROM officers WHERE name = :name AND rank = :rank",
                            [':name' => $officer_name, ':rank' => $rank]
                        );
                        
                        if (!$officer) {
                            // Create new officer
                            $officer_id = $db->insert(
                                "INSERT INTO officers (name, rank, badge_number) VALUES (:name, :rank, :badge_number)",
                                [
                                    ':name' => $officer_name,
                                    ':rank' => $rank,
                                    ':badge_number' => $case_number // Using case number as badge number for simplicity
                                ]
                            );
                        } else {
                            $officer_id = $officer['id'];
                        }
                        
                        // Check if case exists, create if not
                        $case = $db->fetch(
                            "SELECT id FROM cases WHERE case_number = :case_number",
                            [':case_number' => $case_number]
                        );
                        
                        if (!$case) {
                            // Create new case
                            $case_id = $db->insert(
                                "INSERT INTO cases (case_number, case_title, officer_id, description, status) 
                                 VALUES (:case_number, :case_title, :officer_id, :description, 'Open')",
                                [
                                    ':case_number' => $case_number,
                                    ':case_title' => $case_title,
                                    ':officer_id' => $officer_id,
                                    ':description' => $case_description
                                ]
                            );
                        } else {
                            $case_id = $case['id'];
                        }
                        
                        // Move uploaded file
                        if (move_uploaded_file($file_tmp_path, $upload_path)) {
                            // Save document in database
                            $db->insert(
                                "INSERT INTO documents (case_id, file_name, file_path, document_type, uploaded_by) 
                                 VALUES (:case_id, :file_name, :file_path, :document_type, :uploaded_by)",
                                [
                                    ':case_id' => $case_id,
                                    ':file_name' => $file_name,
                                    ':file_path' => $new_file_name,
                                    ':document_type' => $document_type,
                                    ':uploaded_by' => $_SESSION['user_id']
                                ]
                            );
                            
                            // Commit transaction
                            $db->query('COMMIT');
                            
                            $success = 'File uploaded successfully!';
                        } else {
                            throw new Exception('Failed to move uploaded file.');
                        }
                    } catch (Exception $e) {
                        // Rollback transaction on error
                        $db->query('ROLLBACK');
                        $error = 'Upload failed: ' . $e->getMessage();
                    }
                    
                    $db->close();
                }
            }
        }
    }
}

// Generate CSRF token
$csrf_token = generateCSRFToken();

// Include header
require_once 'includes/header.php';
?>

<div class="container py-4">
    <a href="dashboard.php" class="text-primary mb-4 d-inline-block">
        <i class="bi bi-arrow-left me-1"></i> Back to Home
    </a>
    
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Upload Case Files</h5>
        </div>
        <div class="card-body">
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="upload.php" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <div class="row mb-3">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <label for="caseNumber" class="form-label">Case Number *</label>
                        <input type="text" class="form-control" id="caseNumber" name="caseNumber" placeholder="Enter case number" required>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="caseTitle" class="form-label">Case Title *</label>
                        <input type="text" class="form-control" id="caseTitle" name="caseTitle" placeholder="Enter case title" required>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <label for="officerName" class="form-label">Officer Name *</label>
                        <input type="text" class="form-control" id="officerName" name="officerName" placeholder="Enter officer name" required>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="rank" class="form-label">Rank</label>
                        <select class="form-select" id="rank" name="rank">
                            <option value="">Select rank</option>
                            <option value="Officer">Officer</option>
                            <option value="Sergeant">Sergeant</option>
                            <option value="Lieutenant">Lieutenant</option>
                            <option value="Captain">Captain</option>
                            <option value="Inspector">Inspector</option>
                            <option value="Chief">Chief</option>
                        </select>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="caseDescription" class="form-label">Case Description</label>
                    <textarea class="form-control" id="caseDescription" name="caseDescription" rows="4" placeholder="Enter a brief description of the case"></textarea>
                </div>
                
                <div class="mb-3">
                    <label for="documentType" class="form-label">Document Type</label>
                    <select class="form-select" id="documentType" name="documentType">
                        <option value="">Select document type</option>
                        <option value="Case Document">Case Document</option>
                        <option value="Evidence">Evidence</option>
                        <option value="Report">Report</option>
                        <option value="Statement">Statement</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label class="form-label">Upload Files *</label>
                    <div class="file-drop-area">
                        <div class="file-info"></div>
                        <input type="file" class="file-input d-none" name="file" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.xlsx,.xls">
                        <div class="text-center">
                            <i class="bi bi-upload fs-1 text-muted mb-2"></i>
                            <p class="text-muted mb-1">Click to upload or drag and drop</p>
                            <p class="small text-muted">PDF, DOC, DOCX, JPG, PNG, XLSX (max 10MB each)</p>
                            <button type="button" class="btn btn-outline-primary mt-2" onclick="document.querySelector('.file-input').click()">
                                Select File
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex justify-content-end gap-2">
                    <button type="reset" class="btn btn-outline-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary">Upload Files</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

