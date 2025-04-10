<?php
require_once 'includes/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

$search_results = [];
$has_searched = false;

// Handle search
if (isset($_GET['query']) && !empty($_GET['query'])) {
    $query = $_GET['query'];
    $has_searched = true;
    
    // Connect to database
    $db = new Database();
    
    // Search for officers and cases
    $search_results = $db->fetchAll(
        "SELECT o.id, o.name AS officer_name, o.rank, c.case_number, c.case_title, c.status
         FROM officers o
         LEFT JOIN cases c ON o.id = c.officer_id
         WHERE o.name LIKE :query OR o.badge_number LIKE :query OR c.case_number LIKE :query OR c.case_title LIKE :query
         ORDER BY o.name, c.case_number",
        [':query' => '%' . $query . '%']
    );
    
    $db->close();
}

// Include header
require_once 'includes/header.php';
?>

<div class="container py-4">
    <a href="dashboard.php" class="text-primary mb-4 d-inline-block">
        <i class="bi bi-arrow-left me-1"></i> Back to Home
    </a>
    
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Officer Search</h5>
        </div>
        <div class="card-body">
            <form action="search.php" method="GET" class="d-flex gap-2">
                <input type="text" name="query" class="form-control" placeholder="Search by officer name..." value="<?php echo isset($_GET['query']) ? htmlspecialchars($_GET['query']) : ''; ?>">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search me-1"></i> Search
                </button>
            </form>
        </div>
    </div>
    
    <?php if ($has_searched): ?>
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Search Results</h5>
        </div>
        <div class="card-body">
            <?php if (count($search_results) > 0): ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Officer Name</th>
                            <th>Rank</th>
                            <th>Case Number</th>
                            <th>Case Title</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($search_results as $result): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($result['officer_name']); ?></td>
                            <td><?php echo htmlspecialchars($result['rank']); ?></td>
                            <td><?php echo htmlspecialchars($result['case_number']); ?></td>
                            <td><?php echo htmlspecialchars($result['case_title']); ?></td>
                            <td>
                                <span class="status-<?php echo strtolower($result['status']); ?>">
                                    <?php echo htmlspecialchars($result['status']); ?>
                                </span>
                            </td>
                            <td>
                                <a href="case.php?id=<?php echo htmlspecialchars($result['case_number']); ?>" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-file-text me-1"></i> View
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <p class="text-center py-4">No results found. Try a different search term.</p>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>

