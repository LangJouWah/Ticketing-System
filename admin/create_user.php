<?php
include '../includes/config.php';
include '../includes/functions.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $department_id = $_POST['department_id'] ?: null;
    
    try {
        // Check if username already exists
        $stmt = $pdo->prepare("SELECT id FROM department_users WHERE username = ?");
        $stmt->execute([$username]);
        
        if ($stmt->fetch()) {
            $error = "Username already exists!";
        } else {
            // Create user with automatic password hashing
            if (createDepartmentUser($pdo, $username, $password, $full_name, $email, $department_id)) {
                $success = "User created successfully!";
            } else {
                $error = "Failed to create user.";
            }
        }
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
}

$departments = getDepartments($pdo);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create User - Helport Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-dark">
    <nav class="navbar navbar-expand-lg navbar-dark navbar-green">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">💚 Helport Admin</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="dashboard.php">← Back to Dashboard</a>
                <a class="nav-link" href="../index.php">🌐 Public Site</a>
                <a class="nav-link" href="logout.php">🚪 Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card card-green">
                    <div class="card-header-green">
                        <h4 class="mb-0">👥 Create Department User</h4>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($success): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                ✅ <?php echo $success; ?>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                ❌ <?php echo $error; ?>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="username" class="form-label">Username *</label>
                                        <input type="text" class="form-control" id="username" name="username" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="password" class="form-label">Password *</label>
                                        <input type="password" class="form-control" id="password" name="password" required>
                                        <div class="form-text">Password will be automatically hashed</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="full_name" class="form-label">Full Name *</label>
                                        <input type="text" class="form-control" id="full_name" name="full_name" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="department_id" class="form-label">Department</label>
                                <select class="form-select" id="department_id" name="department_id">
                                    <option value="">System Administrator (No Department)</option>
                                    <?php foreach($departments as $dept): ?>
                                        <option value="<?php echo $dept['id']; ?>"><?php echo $dept['name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text">Leave empty for system administrator access</div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-green py-3">
                                    👤 Create User
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>