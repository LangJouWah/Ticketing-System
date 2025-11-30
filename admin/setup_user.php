<?php
include '../includes/config.php';
requireAdmin();
include '../includes/functions.php';

$results = [];

// Demo users data
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

foreach ($demo_users as $user_data) {
    try {
        // Check if user exists
        $stmt = $pdo->prepare("SELECT id FROM department_users WHERE username = ?");
        $stmt->execute([$user_data['username']]);
        
        if ($stmt->fetch()) {
            $results[] = "❌ User '{$user_data['username']}' already exists";
        } else {
            // Create user with automatic password hashing
            if (createDepartmentUser($pdo, $user_data['username'], $user_data['password'], $user_data['full_name'], $user_data['email'], $user_data['department_id'])) {
                $results[] = "✅ Created user '{$user_data['username']}' with password '{$user_data['password']}'";
            } else {
                $results[] = "❌ Failed to create user '{$user_data['username']}'";
            }
        }
    } catch (PDOException $e) {
        $results[] = "❌ Error creating '{$user_data['username']}': " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Users - Helport Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-dark">
    <nav class="navbar navbar-expand-lg navbar-dark navbar-green">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">💚 Helport Admin</a>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card card-green">
                    <div class="card-header-green">
                        <h4 class="mb-0">🔄 Setup Demo Users</h4>
                    </div>
                    <div class="card-body">
                        <h5 class="text-green mb-3">Setup Results:</h5>
                        <?php foreach($results as $result): ?>
                            <div class="mb-2"><?php echo $result; ?></div>
                        <?php endforeach; ?>
                        
                        <div class="mt-4">
                            <a href="dashboard.php" class="btn btn-green">Back to Dashboard</a>
                            <a href="create_user.php" class="btn btn-outline-green">Create Single User</a>
                        </div>
                        
                        <div class="mt-4 p-3 bg-darker-bg rounded">
                            <h6 class="text-green">Demo Login Credentials:</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <small class="text-muted d-block">👑 <strong>Admin:</strong> admin / admin123</small>
                                    <small class="text-muted d-block">🛠️ <strong>Tech Support:</strong> tech_support / dept123</small>
                                    <small class="text-muted d-block">💰 <strong>Billing:</strong> billing / dept123</small>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted d-block">ℹ️ <strong>General:</strong> general / dept123</small>
                                    <small class="text-muted d-block">📈 <strong>Sales:</strong> sales / dept123</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>