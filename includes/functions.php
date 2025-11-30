<?php
function generateTicketNumber() {
    return 'TICKET-' . date('Ymd') . '-' . rand(1000, 9999);
}

function getAllTickets($pdo) {
    $stmt = $pdo->prepare("
        SELECT t.*, d.name as department_name 
        FROM tickets t 
        LEFT JOIN departments d ON t.department_id = d.id 
        ORDER BY t.created_at DESC
    ");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTicketById($pdo, $ticket_id) {
    $stmt = $pdo->prepare("
        SELECT t.*, d.name as department_name 
        FROM tickets t 
        LEFT JOIN departments d ON t.department_id = d.id 
        WHERE t.id = ?
    ");
    $stmt->execute([$ticket_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getTicketReplies($pdo, $ticket_id) {
    $stmt = $pdo->prepare("SELECT * FROM ticket_replies WHERE ticket_id = ? ORDER BY created_at ASC");
    $stmt->execute([$ticket_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getDepartments($pdo) {
    $stmt = $pdo->prepare("SELECT * FROM departments ORDER BY name");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTicketsByEmail($pdo, $email) {
    $stmt = $pdo->prepare("
        SELECT t.*, d.name as department_name 
        FROM tickets t 
        LEFT JOIN departments d ON t.department_id = d.id 
        WHERE t.customer_email = ? 
        ORDER BY t.created_at DESC
    ");
    $stmt->execute([$email]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getDepartmentTickets($pdo, $department_id) {
    $stmt = $pdo->prepare("
        SELECT t.*, d.name as department_name 
        FROM tickets t 
        LEFT JOIN departments d ON t.department_id = d.id 
        WHERE t.department_id = ?
        ORDER BY t.created_at DESC
    ");
    $stmt->execute([$department_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getDepartmentUser($pdo, $username) {
    $stmt = $pdo->prepare("
        SELECT du.*, d.name as department_name 
        FROM department_users du 
        LEFT JOIN departments d ON du.department_id = d.id 
        WHERE du.username = ? AND du.is_active = TRUE
    ");
    $stmt->execute([$username]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getDepartmentById($pdo, $department_id) {
    $stmt = $pdo->prepare("SELECT * FROM departments WHERE id = ?");
    $stmt->execute([$department_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getAllDepartmentUsers($pdo) {
    $stmt = $pdo->prepare("
        SELECT du.*, d.name as department_name 
        FROM department_users du 
        LEFT JOIN departments d ON du.department_id = d.id 
        ORDER BY d.name, du.username
    ");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// PASSWORD HASHING FUNCTIONS
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

function createDepartmentUser($pdo, $username, $password, $full_name, $email, $department_id, $is_active = true) {
    $hashed_password = hashPassword($password);
    
    $stmt = $pdo->prepare("INSERT INTO department_users (username, password, full_name, email, department_id, is_active) VALUES (?, ?, ?, ?, ?, ?)");
    return $stmt->execute([$username, $hashed_password, $full_name, $email, $department_id, $is_active]);
}

function updateUserPassword($pdo, $user_id, $new_password) {
    $hashed_password = hashPassword($new_password);
    
    $stmt = $pdo->prepare("UPDATE department_users SET password = ? WHERE id = ?");
    return $stmt->execute([$hashed_password, $user_id]);
}

// AUTO-SETUP DEMO USERS
function setupDemoUsers($pdo) {
    $demo_users = [
        [
            'username' => 'admin',
            'password' => 'admin123',
            'full_name' => 'System Administrator',
            'email' => 'admin@helport.com',
            'department_id' => null
        ],
        [
            'username' => 'tech_support',
            'password' => 'dept123',
            'full_name' => 'Tech Support Agent',
            'email' => 'tech@helport.com',
            'department_id' => 1
        ],
        [
            'username' => 'billing',
            'password' => 'dept123',
            'full_name' => 'Billing Department',
            'email' => 'billing@helport.com',
            'department_id' => 2
        ],
        [
            'username' => 'general',
            'password' => 'dept123',
            'full_name' => 'General Support',
            'email' => 'general@helport.com',
            'department_id' => 3
        ],
        [
            'username' => 'sales',
            'password' => 'dept123',
            'full_name' => 'Sales Department',
            'email' => 'sales@helport.com',
            'department_id' => 4
        ]
    ];

    $results = [];
    foreach ($demo_users as $user_data) {
        // Check if user exists
        $stmt = $pdo->prepare("SELECT id FROM department_users WHERE username = ?");
        $stmt->execute([$user_data['username']]);
        
        if (!$stmt->fetch()) {
            // Create user if doesn't exist
            createDepartmentUser($pdo, $user_data['username'], $user_data['password'], $user_data['full_name'], $user_data['email'], $user_data['department_id']);
            $results[] = "Created user: {$user_data['username']}";
        }
    }
    return $results;
}
?>