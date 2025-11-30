<?php include 'includes/config.php';
include 'includes/functions.php' ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Help Desk Ticketing System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-dark">
    <nav class="navbar navbar-expand-lg navbar-dark navbar-green">
        <div class="container">
            <a class="navbar-brand text-white" href="index.php">Helport</a>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <!-- Hero Section -->
                <div class="hero-section">
                    <h1>Welcome to Helport Support</h1>
                    <p class="lead">Get the help you need with our efficient ticketing system</p>
                    <div class="mt-4">
                        <span class="badge-green me-2">🚀 Fast Response</span>
                        <span class="badge-green me-2">💚 24/7 Support</span>
                        <span class="badge-green">🔒 Secure</span>
                    </div>
                </div>

                <!-- Ticket Form Card -->
                <div class="card card-green">
                    <div class="card-header-green">
                        <h3 class="text-center mb-0">Submit a Support Ticket</h3>
                    </div>
                    <div class="card-body p-4">
                        <?php if (isset($_GET['success'])): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <strong>✅ Success!</strong> Ticket submitted successfully! Your ticket number: <strong><?php echo $_GET['ticket']; ?></strong>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form id="ticketForm" action="create_ticket.php" method="POST" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="customer_name" class="form-label">Your Name *</label>
                                        <input type="text" class="form-control" id="customer_name" name="customer_name" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="customer_email" class="form-label">Email Address *</label>
                                        <input type="email" class="form-control" id="customer_email" name="customer_email" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="department_id" class="form-label">Department *</label>
                                <select class="form-select" id="department_id" name="department_id" required>
                                    <option value="">Select Department</option>
                                    <?php
                                    $departments = getDepartments($pdo);
                                    foreach($departments as $dept) {
                                        echo "<option value='{$dept['id']}'>{$dept['name']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="subject" class="form-label">Subject *</label>
                                <input type="text" class="form-control" id="subject" name="subject" placeholder="Brief description of your issue" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Issue Description *</label>
                                <textarea class="form-control" id="description" name="description" rows="5" placeholder="Please describe your issue in detail..." required></textarea>
                                <div class="form-text text-end">
                                    Characters: <span id="charCount">0</span>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="file" class="form-label">Attach File (Optional)</label>
                                <input type="file" class="form-control" id="file" name="file">
                                <div class="form-text">Allowed files: JPG, PNG, GIF, PDF, TXT (Max 5MB)</div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-green">
                                    <span class="spinner-border spinner-border-sm d-none" id="submitSpinner"></span>
                                    🚀 Submit Ticket
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="text-center mt-4">
                    <div class="btn-group" role="group">
                        <a href="view_tickets.php" class="btn btn-outline-green">📋 View Your Tickets</a>
                        <a href="admin/login.php" class="btn btn-outline-green">👨‍💼 Admin Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer-green mt-5">
        <div class="container text-center">
            <p class="mb-2">&copy; 2024 Helport Support System. All rights reserved.</p>
            <p class="mb-0"><small>Built with 💚 for better customer support</small></p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>