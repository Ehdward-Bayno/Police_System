<?php
require_once 'config.php';
redirectIfNotLoggedIn();

$caseId = $_GET['id'] ?? '';
$success = false;
$error = '';

if (empty($caseId)) {
    header('Location: search.php');
    exit;
}

// Get case details
try {
    $db = getDB();
    
    $stmt = $db->prepare('
        SELECT c.*, o.name AS officer_name, o.rank
        FROM cases c
        LEFT JOIN officers o ON c.officer_id = o.id
        WHERE c.case_number = :case_number
    ');
    $stmt->bindParam(':case_number', $caseId);
    $stmt->execute();
    
    $caseData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$caseData) {
        header('Location: search.php');
        exit;
    }
    
    // Get respondents
    $stmt = $db->prepare('
        SELECT * FROM respondents
        WHERE case_id = :case_id
    ');
    $stmt->bindParam(':case_id', $caseData['id']);
    $stmt->execute();
    
    $respondents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Update case details
        $stmt = $db->prepare('
            UPDATE cases
            SET status = :status, description = :description
            WHERE id = :case_id
        ');
        $status = $_POST['status'] ?? $caseData['status'];
        $description = $_POST['description'] ?? $caseData['description'];
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':case_id', $caseData['id']);
        $stmt->execute();
        
        // Update respondents
        if (isset($_POST['respondent_name']) && is_array($_POST['respondent_name'])) {
            foreach ($_POST['respondent_name'] as $index => $name) {
                $respondentId = $_POST['respondent_id'][$index] ?? null;
                $rank = $_POST['respondent_rank'][$index] ?? '';
                $unit = $_POST['respondent_unit'][$index] ?? '';
                $justification = $_POST['respondent_justification'][$index] ?? '';
                $remarks = $_POST['respondent_remarks'][$index] ?? '';
                
                if ($respondentId) {
                    // Update existing respondent
                    $stmt = $db->prepare('
                        UPDATE respondents
                        SET name = :name, rank = :rank, unit = :unit, justification = :justification, remarks = :remarks
                        WHERE id = :id
                    ');
                    $stmt->bindParam(':id', $respondentId);
                } else {
                    // Add new respondent
                    $stmt = $db->prepare('
                        INSERT INTO respondents (case_id, name, rank, unit, justification, remarks)
                        VALUES (:case_id, :name, :rank, :unit, :justification, :remarks)
                    ');
                    $stmt->bindParam(':case_id', $caseData['id']);
                }
                
                $stmt->bindParam(':name', $name);
                $stmt->bindParam(':rank', $rank);
                $stmt->bindParam(':unit', $unit);
                $stmt->bindParam(':justification', $justification);
                $stmt->bindParam(':remarks', $remarks);
                $stmt->execute();
            }
        }
        
        $success = true;
        
        // Refresh case data
        $stmt = $db->prepare('
            SELECT c.*, o.name AS officer_name, o.rank
            FROM cases c
            LEFT JOIN officers o ON c.officer_id = o.id
            WHERE c.case_number = :case_number
        ');
        $stmt->bindParam(':case_number', $caseId);
        $stmt->execute();
        
        $caseData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Refresh respondents
        $stmt = $db->prepare('
            SELECT * FROM respondents
            WHERE case_id = :case_id
        ');
        $stmt->bindParam(':case_id', $caseData['id']);
        $stmt->execute();
        
        $respondents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    $error = 'Error retrieving case details: ' . $e->getMessage();
}
?>

<?php include 'includes/header.php'; ?>

<div class="mb-3">
    <a href="search.php" class="text-decoration-none">
        <i class="fas fa-arrow-left me-1"></i> Back to Search
    </a>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php else: ?>
    <?php if ($success): ?>
        <div class="alert alert-success">Case details saved successfully!</div>
    <?php endif; ?>
    
    <div class="bg-white p-4 rounded shadow-sm mb-4">
        <div class="text-center mb-4 no-print">
            <div class="d-flex justify-content-end mb-3">
                <button id="print-button" class="btn btn-outline-primary me-2">
                    <i class="fas fa-print me-1"></i> Print Form
                </button>
                <button type="submit" form="case-form" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Save Case
                </button>
            </div>
        </div>
        
        <div class="text-center mb-4">
            <p class="small mb-0">Republic of the Philippines</p>
            <h2 class="h4 fw-bold text-uppercase">National Police Commission</h2>
            <p class="small mb-0">IMCI Building, 163 Quezon Avenue</p>
            <p class="small mb-0">North Triangle, Diliman, Quezon City</p>
            <p class="small mb-0">www.napolcom.gov.ph</p>
            <div class="mt-3">
                <h3 class="h5 fw-bold text-uppercase">Inspection, Monitoring, and Investigation Service</h3>
                <h4 class="h6 fw-bold text-uppercase">Criminal Profiling Data</h4>
            </div>
        </div>
        
        <form id="case-form" method="POST" action="case-details.php?id=<?php echo urlencode($caseId); ?>">
            <div class="table-responsive mb-4">
                <table class="table table-bordered">
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
                        <?php if (count($respondents) > 0): ?>
                            <?php foreach ($respondents as $index => $respondent): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($caseData['case_number']); ?></td>
                                    <td>
                                        <input type="hidden" name="respondent_id[]" value="<?php echo $respondent['id']; ?>">
                                        <input type="text" class="form-control form-control-sm" name="respondent_name[]" value="<?php echo htmlspecialchars($respondent['name']); ?>" required>
                                    </td>
                                    <td>
                                        <select class="form-select form-select-sm" name="respondent_rank[]">
                                            <option value="">Select rank</option>
                                            <option value="Officer" <?php echo $respondent['rank'] === 'Officer' ? 'selected' : ''; ?>>Officer</option>
                                            <option value="Sergeant" <?php echo $respondent['rank'] === 'Sergeant' ? 'selected' : ''; ?>>Sergeant</option>
                                            <option value="Lieutenant" <?php echo $respondent['rank'] === 'Lieutenant' ? 'selected' : ''; ?>>Lieutenant</option>
                                            <option value="Captain" <?php echo $respondent['rank'] === 'Captain' ? 'selected' : ''; ?>>Captain</option>
                                            <option value="Inspector" <?php echo $respondent['rank'] === 'Inspector' ? 'selected' : ''; ?>>Inspector</option>
                                            <option value="Civilian" <?php echo $respondent['rank'] === 'Civilian' ? 'selected' : ''; ?>>Civilian</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select class="form-select form-select-sm" name="respondent_unit[]">
                                            <option value="">Select unit</option>
                                            <option value="Headquarters" <?php echo $respondent['unit'] === 'Headquarters' ? 'selected' : ''; ?>>Headquarters</option>
                                            <option value="Patrol" <?php echo $respondent['unit'] === 'Patrol' ? 'selected' : ''; ?>>Patrol</option>
                                            <option value="Investigation" <?php echo $respondent['unit'] === 'Investigation' ? 'selected' : ''; ?>>Investigation</option>
                                            <option value="N/A" <?php echo $respondent['unit'] === 'N/A' ? 'selected' : ''; ?>>N/A</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select class="form-select form-select-sm" name="respondent_justification[]">
                                            <option value="">Select justification</option>
                                            <option value="Primary Suspect" <?php echo $respondent['justification'] === 'Primary Suspect' ? 'selected' : ''; ?>>Primary Suspect</option>
                                            <option value="Accomplice" <?php echo $respondent['justification'] === 'Accomplice' ? 'selected' : ''; ?>>Accomplice</option>
                                            <option value="Witness" <?php echo $respondent['justification'] === 'Witness' ? 'selected' : ''; ?>>Witness</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm" name="respondent_remarks[]" value="<?php echo htmlspecialchars($respondent['remarks'] ?? ''); ?>">
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td><?php echo htmlspecialchars($caseData['case_number']); ?></td>
                                <td>
                                    <input type="hidden" name="respondent_id[]" value="">
                                    <input type="text" class="form-control form-control-sm" name="respondent_name[]" required>
                                </td>
                                <td>
                                    <select class="form-select form-select-sm" name="respondent_rank[]">
                                        <option value="">Select rank</option>
                                        <option value="Officer">Officer</option>
                                        <option value="Sergeant">Sergeant</option>
                                        <option value="Lieutenant">Lieutenant</option>
                                        <option value="Captain">Captain</option>
                                        <option value="Inspector">Inspector</option>
                                        <option value="Civilian">Civilian</option>
                                    </select>
                                </td>
                                <td>
                                    <select class="form-select form-select-sm" name="respondent_unit[]">
                                        <option value="">Select unit</option>
                                        <option value="Headquarters">Headquarters</option>
                                        <option value="Patrol">Patrol</option>
                                        <option value="Investigation">Investigation</option>
                                        <option value="N/A">N/A</option>
                                    </select>
                                </td>
                                <td>
                                    <select class="form-select form-select-sm" name="respondent_justification[]">
                                        <option value="">Select justification</option>
                                        <option value="Primary Suspect">Primary Suspect</option>
                                        <option value="Accomplice">Accomplice</option>
                                        <option value="Witness">Witness</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="text" class="form-control form-control-sm" name="respondent_remarks[]">
                                </td>
                            </tr>
                        <?php endif; ?>
                        <tr class="no-print">
                            <td colspan="6">
                                <button type="button" class="btn btn-sm btn-outline-primary" id="add-respondent">
                                    <i class="fas fa-plus me-1"></i> Add Respondent
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Case Information</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Case Title</label>
                            <p class="form-control-plaintext"><?php echo htmlspecialchars($caseData['case_title']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <option value="Open" <?php echo $caseData['status'] === 'Open' ? 'selected' : ''; ?>>Open</option>
                                <option value="Closed" <?php echo $caseData['status'] === 'Closed' ? 'selected' : ''; ?>>Closed</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3"><?php echo htmlspecialchars($caseData['description'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>
            
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-header text-center">
                            <h5 class="card-title mb-0">Conducted by:</h5>
                        </div>
                        <div class="card-body text-center">
                            <div class="signature-line">
                                <input type="text" class="form-control text-center border-0" name="conducted_by" value="Michael Joy E. Eden">
                            </div>
                            <input type="text" class="form-control text-center border-0 small" name="conducted_by_title" value="Evaluator/In-Charge Investigator">
                            <div class="mt-3">
                                <label class="form-label">Date:</label>
                                <input type="date" class="form-control w-75 mx-auto" name="conducted_date">
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-header text-center">
                            <h5 class="card-title mb-0">Recommending Approval:</h5>
                        </div>
                        <div class="card-body text-center">
                            <div class="signature-line">
                                <input type="text" class="form-control text-center border-0" name="recommending_approval" value="Chrysostom C. Onidoan">
                            </div>
                            <input type="text" class="form-control text-center border-0 small" name="recommending_approval_title" value="Acting Chief, Investigation Division">
                            <div class="mt-3">
                                <label class="form-label">Date:</label>
                                <input type="date" class="form-control w-75 mx-auto" name="recommending_date">
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
                            <div class="signature-line">
                                <input type="text" class="form-control text-center border-0" name="approver" value="Dr. Edman P. Pares">
                            </div>
                            <input type="text" class="form-control text-center border-0 small" name="approver_title" value="Staff Service Chief, IMS">
                            <div class="mt-3">
                                <div class="d-flex justify-content-center">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="approval_status" id="approved" value="approved">
                                        <label class="form-check-label" for="approved">Approved</label>
                                    </div>
                                    <div class="form-check form-check-inline">
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
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add respondent functionality
    const addRespondentBtn = document.getElementById('add-respondent');
    if (addRespondentBtn) {
        addRespondentBtn.addEventListener('click', function() {
            const tbody = this.closest('tbody');
            const lastRow = tbody.querySelector('tr:nth-last-child(2)');
            const newRow = lastRow.cloneNode(true);
            
            // Clear input values
            newRow.querySelectorAll('input[type="text"], select').forEach(input => {
                input.value = '';
            });
            
            // Clear hidden id
            const hiddenInput = newRow.querySelector('input[name="respondent_id[]"]');
            if (hiddenInput) {
                hiddenInput.value = '';
            }
            
            // Insert before the "Add Respondent" row
            tbody.insertBefore(newRow, tbody.lastElementChild);
        });
    }
});
</script>

<?php include 'includes/footer.php'; ?>

