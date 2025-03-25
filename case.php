<?php
require_once 'includes/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

// Check if case ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    redirect('search.php');
}

$case_number = $_GET['id'];
$error = '';
$success = '';

// Connect to database
$db = new Database();

// Get case details
$case = $db->fetch(
    "SELECT c.*, o.name AS officer_name, o.rank 
     FROM cases c 
     LEFT JOIN officers o ON c.officer_id = o.id 
     WHERE c.case_number = :case_number",
    [':case_number' => $case_number]
);

// If case not found, redirect to search
if (!$case) {
    $db->close();
    redirect('search.php');
}

// Initialize respondents as an empty array
$respondents = [];

// Get respondents
if (isset($case['id'])) {
    $respondents = $db->fetchAll(
        "SELECT * FROM respondents WHERE case_id = :case_id",
        [':case_id' => $case['id']]
    ) ?: [];
}

// Initialize access_logs as an empty array
$access_logs = [];

// Log this access
if (isset($case['id'])) {
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
    
    // Get access history
    $access_logs = $db->fetchAll(
        "SELECT al.accessed_at, u.name, u.badge_number
         FROM case_access_logs al
         JOIN users u ON al.user_id = u.id
         WHERE al.case_id = :case_id
         ORDER BY al.accessed_at DESC
         LIMIT 10",
        [':case_id' => $case['id']]
    ) ?: [];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!checkCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid form submission. Please try again.';
    } else {
        try {
            // Start transaction
            $db->query('BEGIN TRANSACTION');
            
            // Update case
            $db->query(
                "UPDATE cases SET 
                 case_title = :case_title, 
                 description = :description,
                 status = :status
                 WHERE id = :case_id",
                [
                    ':case_title' => $_POST['caseTitle'] ?? $case['case_title'],
                    ':description' => $_POST['caseDescription'] ?? $case['description'],
                    ':status' => $_POST['status'] ?? $case['status'],
                    ':case_id' => $case['id']
                ]
            );
            
            // Update respondents
            if (isset($_POST['respondent_name']) && is_array($_POST['respondent_name'])) {
                $respondent_names = $_POST['respondent_name'];
                $respondent_ranks = $_POST['respondent_rank'] ?? [];
                $respondent_units = $_POST['respondent_unit'] ?? [];
                $respondent_justifications = $_POST['respondent_justification'] ?? [];
                $respondent_remarks = $_POST['respondent_remarks'] ?? [];
                
                // Delete existing respondents
                $db->query(
                    "DELETE FROM respondents WHERE case_id = :case_id",
                    [':case_id' => $case['id']]
                );
                
                // Insert new respondents
                for ($i = 0; $i < count($respondent_names); $i++) {
                    if (!empty($respondent_names[$i])) {
                        $db->insert(
                            "INSERT INTO respondents (case_id, name, rank, unit, justification, remarks) 
                             VALUES (:case_id, :name, :rank, :unit, :justification, :remarks)",
                            [
                                ':case_id' => $case['id'],
                                ':name' => $respondent_names[$i],
                                ':rank' => $respondent_ranks[$i] ?? '',
                                ':unit' => $respondent_units[$i] ?? '',
                                ':justification' => $respondent_justifications[$i] ?? '',
                                ':remarks' => $respondent_remarks[$i] ?? ''
                            ]
                        );
                    }
                }
            }
            
            // Commit transaction
            $db->query('COMMIT');
            
            $success = 'Case updated successfully!';
            
            // Refresh case data
            $case = $db->fetch(
                "SELECT c.*, o.name AS officer_name, o.rank 
                 FROM cases c 
                 LEFT JOIN officers o ON c.officer_id = o.id 
                 WHERE c.case_number = :case_number",
                [':case_number' => $case_number]
            );
            
            // Refresh respondents
            if ($case) {
                $respondents = $db->fetchAll(
                    "SELECT * FROM respondents WHERE case_id = :case_id",
                    [':case_id' => $case['id']]
                ) ?: [];
            }
        } catch (Exception $e) {
            // Rollback transaction on error
            $db->query('ROLLBACK');
            $error = 'Update failed: ' . $e->getMessage();
        }
    }
}

// Generate CSRF token
$csrf_token = generateCSRFToken();

// Include header
require_once 'includes/header.php';
?>

<div class="container py-4">
    <a href="search.php" class="text-primary mb-4 d-inline-block">
        <i class="bi bi-arrow-left me-1"></i> Back to Search
    </a>
    
    <div class="bg-white p-4 rounded shadow-sm mb-4">
        <div class="text-center mb-4">
            <p class="small mb-0">Republic of the Philippines</p>
            <h1 class="h4 fw-bold text-uppercase mb-0">National Police Commission</h1>
            <p class="small mb-0">IMCI Building, 163 Quezon Avenue</p>
            <p class="small mb-0">North Triangle, Diliman, Quezon City</p>
            <p class="small mb-0">www.napolcom.gov.ph</p>
            <div class="mt-3">
                <h2 class="h5 fw-bold text-uppercase mb-0">Inspection, Monitoring, and Investigation Service</h2>
                <h3 class="h6 fw-bold text-uppercase">Criminal Profiling Data</h3>
            </div>
        </div>
        
        <div class="d-flex justify-content-end gap-2 mb-4 no-print">
            <button type="button" class="btn btn-outline-primary" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Print Form
            </button>
            <button type="button" class="btn btn-primary" onclick="document.getElementById('caseForm').submit()">
                <i class="bi bi-save me-1"></i> Save Case
            </button>
        </div>
        
        <?php if ($success): ?>
            <div class="alert alert-success no-print"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger no-print"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <!-- Access History Section (No Print) -->
        <div class="card mb-4 no-print">
            <div class="card-header">
                <h5 class="card-title mb-0">Access History</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($access_logs)): ?>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Badge Number</th>
                                <th>Accessed On</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($access_logs as $log): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($log['name'] ?? 'Unknown'); ?></td>
                                <td><?php echo htmlspecialchars($log['badge_number'] ?? 'N/A'); ?></td>
                                <td><?php echo isset($log['accessed_at']) ? date('M d, Y h:i A', strtotime($log['accessed_at'])) : 'Unknown'; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p class="text-center py-2">No access history available.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <form id="caseForm" method="POST" action="case.php?id=<?php echo htmlspecialchars($case_number); ?>">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <input type="hidden" name="caseNumber" value="<?php echo htmlspecialchars($case['case_number']); ?>">
            <input type="hidden" name="caseTitle" value="<?php echo htmlspecialchars($case['case_title'] ?? ''); ?>">
            
            <div class="row mb-4">
                <div class="col-md-8">
                    <h4 class="mb-3"><?php echo htmlspecialchars($case['case_title'] ?? 'Case Details'); ?></h4>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="status" class="form-label">Case Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="Open" <?php echo ($case['status'] ?? '') === 'Open' ? 'selected' : ''; ?>>Open</option>
                            <option value="Closed" <?php echo ($case['status'] ?? '') === 'Closed' ? 'selected' : ''; ?>>Closed</option>
                            <option value="Pending" <?php echo ($case['status'] ?? '') === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="Under Review" <?php echo ($case['status'] ?? '') === 'Under Review' ? 'selected' : ''; ?>>Under Review</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="mb-4">
                <div class="table-responsive">
                    <table class="table table-bordered respondent-table">
                        <thead class="table-light">
                            <tr>
                                <th>Case No.</th>
                                <th>Respondent</th>
                                <th>Rank</th>
                                <th>Unit</th>
                                <th>Justification of Offense</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($respondents)): ?>
                                <?php foreach ($respondents as $respondent): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($case['case_number'] ?? ''); ?></td>
                                    <td>
                                        <input type="text" name="respondent_name[]" class="form-control form-control-sm" value="<?php echo htmlspecialchars($respondent['name'] ?? ''); ?>" required>
                                    </td>
                                    <td>
                                        <select name="respondent_rank[]" class="form-select form-select-sm">
                                            <option value="Officer" <?php echo ($respondent['rank'] ?? '') === 'Officer' ? 'selected' : ''; ?>>Officer</option>
                                            <option value="Sergeant" <?php echo ($respondent['rank'] ?? '') === 'Sergeant' ? 'selected' : ''; ?>>Sergeant</option>
                                            <option value="Lieutenant" <?php echo ($respondent['rank'] ?? '') === 'Lieutenant' ? 'selected' : ''; ?>>Lieutenant</option>
                                            <option value="Captain" <?php echo ($respondent['rank'] ?? '') === 'Captain' ? 'selected' : ''; ?>>Captain</option>
                                            <option value="Inspector" <?php echo ($respondent['rank'] ?? '') === 'Inspector' ? 'selected' : ''; ?>>Inspector</option>
                                            <option value="Civilian" <?php echo ($respondent['rank'] ?? '') === 'Civilian' ? 'selected' : ''; ?>>Civilian</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select name="respondent_unit[]" class="form-select form-select-sm">
                                            <option value="Headquarters" <?php echo ($respondent['unit'] ?? '') === 'Headquarters' ? 'selected' : ''; ?>>Headquarters</option>
                                            <option value="Patrol" <?php echo ($respondent['unit'] ?? '') === 'Patrol' ? 'selected' : ''; ?>>Patrol</option>
                                            <option value="Investigation" <?php echo ($respondent['unit'] ?? '') === 'Investigation' ? 'selected' : ''; ?>>Investigation</option>
                                            <option value="N/A" <?php echo ($respondent['unit'] ?? '') === 'N/A' ? 'selected' : ''; ?>>N/A</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select name="respondent_justification[]" class="form-select form-select-sm">
                                            <option value="Primary Suspect" <?php echo ($respondent['justification'] ?? '') === 'Primary Suspect' ? 'selected' : ''; ?>>Primary Suspect</option>
                                            <option value="Accomplice" <?php echo ($respondent['justification'] ?? '') === 'Accomplice' ? 'selected' : ''; ?>>Accomplice</option>
                                            <option value="Witness" <?php echo ($respondent['justification'] ?? '') === 'Witness' ? 'selected' : ''; ?>>Witness</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" name="respondent_remarks[]" class="form-control form-control-sm" value="<?php echo htmlspecialchars($respondent['remarks'] ?? ''); ?>">
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($case['case_number'] ?? ''); ?></td>
                                    <td>
                                        <input type="text" name="respondent_name[]" class="form-control form-control-sm" required>
                                    </td>
                                    <td>
                                        <select name="respondent_rank[]" class="form-select form-select-sm">
                                            <option value="Officer">Officer</option>
                                            <option value="Sergeant">Sergeant</option>
                                            <option value="Lieutenant">Lieutenant</option>
                                            <option value="Captain">Captain</option>
                                            <option value="Inspector">Inspector</option>
                                            <option value="Civilian" selected>Civilian</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select name="respondent_unit[]" class="form-select form-select-sm">
                                            <option value="Headquarters">Headquarters</option>
                                            <option value="Patrol">Patrol</option>
                                            <option value="Investigation">Investigation</option>
                                            <option value="N/A" selected>N/A</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select name="respondent_justification[]" class="form-select form-select-sm">
                                            <option value="Primary Suspect" selected>Primary Suspect</option>
                                            <option value="Accomplice">Accomplice</option>
                                            <option value="Witness">Witness</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" name="respondent_remarks[]" class="form-control form-control-sm">
                                    </td>
                                </tr>
                            <?php endif; ?>
                            <tr class="no-print">
                                <td colspan="6">
                                    <button type="button" class="btn btn-link text-primary add-respondent-btn">
                                        <i class="bi bi-plus-circle me-1"></i> Add Respondent
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Complainant Information</h5>
                    </div>
                    <div class="card-body">
                        <textarea name="caseDescription" class="form-control" rows="3" placeholder="Enter complainant details including name, contact information, address, and statement"><?php echo htmlspecialchars($case['description'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>
            
            <div class="row mb-4">
                <div class="col-md-4 mb-3 mb-md-0">
                    <div class="card h-100">
                        <div class="card-header text-center">
                            <h5 class="card-title mb-0">Conducted by:</h5>
                        </div>
                        <div class="card-body text-center">
                            <div class="border-bottom border-dark pb-2 mb-2 mx-auto" style="max-width: 250px;">
                                <input type="text" class="form-control text-center border-0" value="Michael Joy E. Eden">
                            </div>
                            <input type="text" class="form-control text-center border-0 small" value="Evaluator/In-Charge Investigator">
                            <div class="mt-3">
                                <label class="form-label d-block">Date:</label>
                                <input type="date" class="form-control mx-auto" style="max-width: 150px;">
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-3 mb-md-0">
                    <div class="card h-100">
                        <div class="card-header text-center">
                            <h5 class="card-title mb-0">Recommending Approval:</h5>
                        </div>
                        <div class="card-body text-center">
                            <div class="border-bottom border-dark pb-2 mb-2 mx-auto" style="max-width: 250px;">
                                <input type="text" class="form-control text-center border-0" value="Chrysostom C. Onidoan">
                            </div>
                            <input type="text" class="form-control text-center border-0 small" value="Acting Chief, Investigation Division">
                            <div class="mt-3">
                                <label class="form-label d-block">Date:</label>
                                <input type="date" class="form-control mx-auto" style="max-width: 150px;">
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-header text-center">
                            <h5 class="card-title mb-0">Approved by:</h5>
                        </div>
                        <div class="card-body text-center">
                            <div class="border-bottom border-dark pb-2 mb-2 mx-auto" style="max-width: 250px;">
                                <input type="text" class="form-control text-center border-0" value="Dr. Edman P. Pares">
                            </div>
                            <input type="text" class="form-control text-center border-0 small" value="Staff Service Chief, IMS">
                            <div class="mt-3">
                                <div class="d-flex justify-content-center gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="approval_status" id="approved" value="approved">
                                        <label class="form-check-label" for="approved">Approved</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="approval_status" id="disapproved" value="disapproved">
                                        <label class="form-check-label" for="disapproved">Disapproved</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

