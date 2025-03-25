<?php
require_once 'includes/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

// Connect to database
$db = new Database();

// Get recent case count
$case_count = $db->fetch("SELECT COUNT(*) as count FROM cases");
$case_count = $case_count ? $case_count['count'] : 0;

// Get recent access logs
$recent_access = $db->fetchAll(
    "SELECT al.accessed_at, u.name AS user_name, c.case_number, c.case_title
     FROM case_access_logs al
     JOIN users u ON al.user_id = u.id
     JOIN cases c ON al.case_id = c.id
     ORDER BY al.accessed_at DESC
     LIMIT 5"
) ?: [];

// Include header
require_once 'includes/header.php';
?>

<div class="container py-4">
    <h1 class="mb-4 fw-bold">Dashboard</h1>
    
    <!-- Stats Row -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-0">Total Cases</h5>
                        <p class="card-text fs-2 fw-bold mb-0"><?php echo $case_count; ?></p>
                    </div>
                    <i class="bi bi-folder fs-1"></i>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Activity -->
    <?php if ($recent_access && count($recent_access) > 0): ?>
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Recent Activity</h5>
        </div>
        <div class="card-body p-0">
            <ul class="list-group list-group-flush">
                <?php foreach ($recent_access as $access): ?>
                <li class="list-group-item">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-0 fw-medium">
                                <a href="case.php?id=<?php echo htmlspecialchars($access['case_number']); ?>" class="text-decoration-none">
                                    <?php echo htmlspecialchars($access['case_title']); ?>
                                </a>
                            </p>
                            <p class="mb-0 small text-muted">
                                Accessed by <?php echo htmlspecialchars($access['user_name']); ?>
                            </p>
                        </div>
                        <small class="text-muted"><?php echo date('M d, Y h:i A', strtotime($access['accessed_at'])); ?></small>
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0 d-flex align-items-center">
                        <i class="bi bi-search text-primary me-2"></i>
                        Officer Search
                    </h5>
                </div>
                <div class="card-body">
                    <p class="card-text small mb-4">Search for officers by name, badge number, or case details.</p>
                    <a href="search.php" class="btn btn-primary w-100">Go to Search</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0 d-flex align-items-center">
                        <i class="bi bi-upload text-primary me-2"></i>
                        Upload Files
                    </h5>
                </div>
                <div class="card-body">
                    <p class="card-text small mb-4">Upload Excel files and other documents to the system.</p>
                    <a href="upload.php" class="btn btn-primary w-100">Go to Upload</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0 d-flex align-items-center">
                        <i class="bi bi-file-text text-primary me-2"></i>
                        Recent Cases
                    </h5>
                </div>
                <div class="card-body">
                    <p class="card-text small mb-4">Access the most recent case files and documents.</p>
                    <a href="cases.php" class="btn btn-primary w-100">View Cases</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

