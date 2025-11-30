<?php
include '../includes/config.php';
requireAdmin();
include '../includes/functions.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'];
    $new_password = $_POST['new_password'];
    
    if (updateUserPassword($pdo, $user_id, $new_password)) {
        $success = "Password updated successfully!";
    } else {
        $error = "Failed to update password.";
    }
}

$users = getAllDepartmentUsers($pdo);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Helport Admin</title>
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
            <div class="col-md-6">
                <div class="card card-green">
                    <div class="card-header-green">
                        <h4 class="mb-0">🔑 Reset User Password</h4>
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
                            <div class="mb-3">
                                <label for="user_id" class="form-label">Select User</label>
                                <select class="form-select" id="user_id" name="user_id" required>
                                    <option value="">Choose a user...</option>
                                    <?php foreach($users as $user): ?>
                                        <option value="<?php echo $user['id']; ?>">
                                            <?php echo $user['full_name']; ?> (<?php echo $user['username']; ?>)
                                            - <?php echo $user['department_name'] ?: 'Admin'; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-4">
                                <label for="new_password" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="new_password" name="new_password" required>
                                <div class="form-text">Password will be automatically hashed for security</div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-green">
                                    🔄 Reset Password
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