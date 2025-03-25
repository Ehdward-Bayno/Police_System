<?php
require_once 'includes/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

// Connect to database
$db = new Database();

// Get recent cases with last access information
$recent_cases = $db->fetchAll(
    "SELECT c.id, c.case_number, c.case_title, c.status, c.created_at,
            o.name AS officer_name, o.rank,
            u.name AS last_accessed_by,
            MAX(al.accessed_at) AS last_accessed_at
     FROM cases c
     LEFT JOIN officers o ON c.officer_id = o.id
     LEFT JOIN case_access_logs al ON c.id = al.case_id
     LEFT JOIN users u ON al.user_id = u.id
     GROUP BY c.id
     ORDER BY c.created_at DESC
     LIMIT 20"
) ?: [];

// Include header
require_once 'includes/header.php';
?>

<div class="container py-4">
    <a href="dashboard.php" class="text-primary mb-4 d-inline-block">
        <i class="bi bi-arrow-left me-1"></i> Back to Home
    </a>
    
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Recent Cases</h5>
            <span class="badge bg-primary"><?php echo count($recent_cases); ?> cases</span>
        </div>
        <div class="card-body">
            <?php if ($recent_cases && count($recent_cases) > 0): ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Case Number</th>
                            <th>Case Title</th>
                            <th>Officer</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Last Accessed By</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_cases as $case): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($case['case_number']); ?></td>
                            <td><?php echo htmlspecialchars($case['case_title']); ?></td>
                            <td>
                                <?php if ($case['officer_name']): ?>
                                    <?php echo htmlspecialchars($case['officer_name']); ?>
                                    <?php if ($case['rank']): ?>
                                        <small class="text-muted d-block"><?php echo htmlspecialchars($case['rank']); ?></small>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">Not assigned</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="status-<?php echo strtolower($case['status']); ?>">
                                    <?php echo htmlspecialchars($case['status']); ?>
                                </span>
                            </td>
                            <td>
                                <?php echo date('M d, Y', strtotime($case['created_at'])); ?>
                                <small class="text-muted d-block"><?php echo date('h:i A', strtotime($case['created_at'])); ?></small>
                            </td>
                            <td>
                                <?php if ($case['last_accessed_by']): ?>
                                    <?php echo htmlspecialchars($case['last_accessed_by']); ?>
                                    <small class="text-muted d-block"><?php echo date('M d, Y h:i A', strtotime($case['last_accessed_at'])); ?></small>
                                <?php else: ?>
                                    <span class="text-muted">Not accessed yet</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="case.php?id=<?php echo htmlspecialchars($case['case_number']); ?>" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-file-text me-1"></i> View
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="text-center py-5">
                <i class="bi bi-folder-x fs-1 text-muted mb-3"></i>
                <p class="mb-0">No cases found. Start by uploading case files.</p>
                <a href="upload.php" class="btn btn-primary mt-3">Upload Files</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

