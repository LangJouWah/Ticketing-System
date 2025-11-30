<?php
include '../includes/config.php';
include '../includes/functions.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Redirect department users to department panel
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: department.php');
    exit();
}

$tickets = getAllTickets($pdo);
$department_users = getAllDepartmentUsers($pdo);

// Count tickets by status
$open_count = count(array_filter($tickets, fn($t) => $t['status'] === 'Open'));
$progress_count = count(array_filter($tickets, fn($t) => $t['status'] === 'In Progress'));
$resolved_count = count(array_filter($tickets, fn($t) => $t['status'] === 'Resolved'));
$closed_count = count(array_filter($tickets, fn($t) => $t['status'] === 'Closed'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Helport</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-dark">
    <nav class="navbar navbar-expand-lg navbar-dark navbar-green">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">💚 Helport Admin</a>
            <div class="navbar-nav ms-auto">
                <span class="nav-link">👑 Welcome, <?php echo $_SESSION['full_name']; ?></span>
                <a class="nav-link" href="../index.php">🌐 Public Site</a>
                <a class="nav-link" href="logout.php">🚪 Logout</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="text-light mb-1">System Administration</h2>
                        <p class="text-muted mb-0">Manage all departments and system-wide tickets</p>
                    </div>
                    <div class="text-end">
                        <div class="badge-green">Admin Access - All Departments</div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="card card-green stats-card h-100">
                            <div class="card-body text-center p-4">
                                <div class="text-green mb-2">🟢</div>
                                <h3 class="text-green mb-1"><?php echo $open_count; ?></h3>
                                <p class="text-muted mb-0">Open Tickets</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card card-green stats-card h-100">
                            <div class="card-body text-center p-4">
                                <div class="text-warning mb-2">🟡</div>
                                <h3 class="text-warning mb-1"><?php echo $progress_count; ?></h3>
                                <p class="text-muted mb-0">In Progress</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card card-green stats-card h-100">
                            <div class="card-body text-center p-4">
                                <div class="text-info mb-2">🔵</div>
                                <h3 class="text-info mb-1"><?php echo $resolved_count; ?></h3>
                                <p class="text-muted mb-0">Resolved</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card card-green stats-card h-100">
                            <div class="card-body text-center p-4">
                                <div class="text-secondary mb-2">👥</div>
                                <h3 class="text-secondary mb-1"><?php echo count($department_users); ?></h3>
                                <p class="text-muted mb-0">Department Users</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Two Column Layout -->
                <div class="row">
                    <div class="col-md-8">
                        <!-- Tickets Table -->
                        <div class="card card-green mb-4">
                            <div class="card-header-green">
                                <h4 class="mb-0">All System Tickets (<?php echo count($tickets); ?>)</h4>
                            </div>
                            <div class="card-body p-0">
                                <?php if (count($tickets) > 0): ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Ticket #</th>
                                                    <th>Customer</th>
                                                    <th>Subject</th>
                                                    <th>Department</th>
                                                    <th>Status</th>
                                                    <th>Date</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach($tickets as $ticket): 
                                                    $status_class = [
                                                        'Open' => 'badge-success',
                                                        'In Progress' => 'badge-warning',
                                                        'Resolved' => 'badge-info',
                                                        'Closed' => 'badge-secondary'
                                                    ];
                                                ?>
                                                <tr>
                                                    <td><strong class="text-green"><?php echo $ticket['ticket_number']; ?></strong></td>
                                                    <td>
                                                        <div class="fw-bold text-light"><?php echo htmlspecialchars($ticket['customer_name']); ?></div>
                                                        <small class="text-muted"><?php echo $ticket['customer_email']; ?></small>
                                                    </td>
                                                    <td class="text-light"><?php echo htmlspecialchars($ticket['subject']); ?></td>
                                                    <td class="text-light"><?php echo $ticket['department_name']; ?></td>
                                                    <td>
                                                        <span class="badge <?php echo $status_class[$ticket['status']]; ?>">
                                                            <?php echo $ticket['status']; ?>
                                                        </span>
                                                    </td>
                                                    <td class="text-muted"><?php echo date('M j, Y g:i A', strtotime($ticket['created_at'])); ?></td>
                                                    <td>
                                                        <a href="update_ticket.php?id=<?php echo $ticket['id']; ?>" class="btn btn-sm btn-green">Manage</a>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-5">
                                        <div class="text-muted mb-3" style="font-size: 3rem;">📭</div>
                                        <h5 class="text-muted">No tickets in the system</h5>
                                        <p class="text-muted">When customers submit tickets, they will appear here.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <!-- Department Users -->
                        <div class="card card-green mb-4">
                            <div class="card-header-green">
                                <h5 class="mb-0">👥 Department Users</h5>
                            </div>
                            <div class="card-body">
                                <?php foreach($department_users as $user): ?>
                                    <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom border-secondary">
                                        <div>
                                            <div class="fw-bold text-light"><?php echo $user['full_name']; ?></div>
                                            <small class="text-muted">@<?php echo $user['username']; ?></small>
                                            <br>
                                            <small class="text-green">
                                                <?php echo $user['department_name'] ?: 'System Admin'; ?>
                                            </small>
                                        </div>
                                        <span class="badge <?php echo $user['is_active'] ? 'badge-success' : 'badge-secondary'; ?>">
                                            <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- System Info -->
                        <div class="card card-green">
                            <div class="card-header-green">
                                <h5 class="mb-0">⚙️ System Information</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <strong class="text-green">Total Tickets:</strong>
                                    <span class="text-light float-end"><?php echo count($tickets); ?></span>
                                </div>
                                <div class="mb-3">
                                    <strong class="text-green">Departments:</strong>
                                    <span class="text-light float-end">4</span>
                                </div>
                                <div class="mb-3">
                                    <strong class="text-green">Department Users:</strong>
                                    <span class="text-light float-end"><?php echo count($department_users); ?></span>
                                </div>
                                <div class="mb-0">
                                    <strong class="text-green">System Status:</strong>
                                    <span class="badge badge-success float-end">Operational</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/script.js"></script>
</body>
</html>