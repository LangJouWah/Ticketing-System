<?php
include '../includes/config.php';
include '../includes/functions.php';

// Auto-setup demo users on first access
setupDemoUsers($pdo);

// Check if already logged in
if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
        header('Location: dashboard.php');
    } else {
        header('Location: department.php');
    }
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validate input
    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password!";
    } else {
        $user = getDepartmentUser($pdo, $username);
        
        if ($user) {
            // Verify password with hashed version
            if (verifyPassword($password, $user['password'])) {
                // Login successful - set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['department_id'] = $user['department_id'];
                $_SESSION['department_name'] = $user['department_name'] ?? 'Administration';
                $_SESSION['is_admin'] = ($user['department_id'] === null); // Admin has no department
                
                // Redirect based on user type
                if ($_SESSION['is_admin']) {
                    header('Location: dashboard.php');
                } else {
                    header('Location: department.php');
                }
                exit();
            } else {
                $error = "Invalid password!";
            }
        } else {
            $error = "User not found!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Helport</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, var(--dark-bg) 0%, var(--darker-bg) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Inter', sans-serif;
        }
        .login-card {
            background: var(--card-bg);
            border: 1px solid var(--border-dark);
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.6);
            border-radius: 16px;
            overflow: hidden;
        }
        .login-header {
            background: linear-gradient(135deg, var(--dark-green) 0%, #047857 100%);
            padding: 3rem 2rem;
            text-align: center;
            color: white;
        }
        .login-body {
            padding: 3rem 2rem;
            background: var(--card-bg);
        }
        .btn-close {
            filter: invert(1);
        }
        .department-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            justify-content: center;
            margin-top: 1rem;
        }
        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
        }
    </style>
</head>
<body class="login-dark">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card login-card">
                    <div class="login-header">
                        <h2 class="mb-3">💚 Helport</h2>
                        <p class="mb-0 opacity-90">Secure Department Login</p>
                        <div class="department-badges">
                            <span class="badge-green">🔒 Secure Login</span>
                            <span class="badge-green">🚀 Auto-Setup</span>
                            <span class="badge-green">💚 Password Hashed</span>
                        </div>
                    </div>
                    <div class="login-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <strong>❌ Error:</strong> <?php echo $error; ?>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" id="loginForm">
                            <div class="mb-4">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required autofocus>
                            </div>
                            <div class="mb-4 position-relative">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <button type="button" class="password-toggle" id="togglePassword">
                                    👁️
                                </button>
                            </div>
                            <button type="submit" class="btn btn-green w-100 py-3 fw-bold" id="loginButton">
                                <span class="spinner-border spinner-border-sm d-none me-2" id="loginSpinner"></span>
                                🔐 Login to Dashboard
                            </button>
                        </form>
                        
                        <div class="text-center mt-4 pt-3 border-top border-secondary">
                            <h6 class="text-green mb-3">Demo Credentials</h6>
                            <div class="row text-start">
                                <div class="col-12 mb-2">
                                    <small class="text-muted">
                                        <strong>👑 Admin:</strong> admin / admin123
                                    </small>
                                </div>
                                <div class="col-12 mb-2">
                                    <small class="text-muted">
                                        <strong>🛠️ Tech Support:</strong> tech_support / dept123
                                    </small>
                                </div>
                                <div class="col-12 mb-2">
                                    <small class="text-muted">
                                        <strong>💰 Billing:</strong> billing / dept123
                                    </small>
                                </div>
                                <div class="col-12 mb-2">
                                    <small class="text-muted">
                                        <strong>ℹ️ General:</strong> general / dept123
                                    </small>
                                </div>
                                <div class="col-12">
                                    <small class="text-muted">
                                        <strong>📈 Sales:</strong> sales / dept123
                                    </small>
                                </div>
                            </div>
                            <div class="mt-3 p-2 bg-darker-bg rounded">
                                <small class="text-muted">
                                    <strong>💡 Note:</strong> Passwords are automatically hashed for security
                                </small>
                            </div>
                        </div>
                        
                        <div class="text-center mt-3">
                            <a href="../index.php" class="text-green text-decoration-none fw-medium">
                                ← Back to Public Site
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Password visibility toggle
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');
            
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                this.textContent = type === 'password' ? '👁️' : '🔒';
            });

            // Form submission loading state
            const loginForm = document.getElementById('loginForm');
            const loginButton = document.getElementById('loginButton');
            const loginSpinner = document.getElementById('loginSpinner');

            loginForm.addEventListener('submit', function() {
                loginSpinner.classList.remove('d-none');
                loginButton.disabled = true;
                loginButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Logging in...';
            });

            // Auto-focus on username field
            document.getElementById('username').focus();
        });
    </script>
</body>
</html>