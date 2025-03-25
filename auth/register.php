<?php
require_once '../config.php';

// Handle POST request for registration
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get JSON data from request body
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    if (!isset($data['name']) || !isset($data['email']) || !isset($data['badgeNumber']) || !isset($data['password'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Name, email, badge number, and password are required']);
        exit;
    }
    
    $name = $data['name'];
    $email = $data['email'];
    $badgeNumber = $data['badgeNumber'];
    $password = password_hash($data['password'], PASSWORD_DEFAULT);
    
    try {
        // Get database connection
        $database = Database::getInstance();
        $db = $database->getConnection();
        
        // Check if email already exists
        $stmt = $db->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Email already in use']);
            exit;
        }
        
        // Check if badge number already exists
        $stmt = $db->prepare("SELECT id FROM users WHERE badge_number = :badge_number");
        $stmt->bindParam(':badge_number', $badgeNumber);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Badge number already in use']);
            exit;
        }
        
        // Insert new user
        $stmt = $db->prepare("INSERT INTO users (name, email, badge_number, password) VALUES (:name, :email, :badge_number, :password)");
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':badge_number', $badgeNumber);
        $stmt->bindParam(':password', $password);
        $stmt->execute();
        
        $userId = $db->lastInsertId();
        
        // Set session variables
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_name'] = $name;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_badge'] = $badgeNumber;
        
        echo json_encode([
            'success' => true,
            'message' => 'Registration successful',
            'user' => [
                'id' => $userId,
                'name' => $name,
                'email' => $email,
                'badge_number' => $badgeNumber
            ]
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
?>

