<?php
include '../includes/config.php';
include '../includes/functions.php';

// Check if user is logged in and is a department user
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Redirect admin users to admin panel
if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
    header('Location: index.php');
    exit();
}

// Department users can only access their own department
$department_id = $_SESSION['department_id'] ?? null;
if (!$department_id) {
    header('Location: login.php');
    exit();
}

$department = getDepartmentById($pdo, $department_id);
$tickets = getDepartmentTickets($pdo, $department_id);

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
    <title><?php echo $department['name']; ?> Dashboard - Helport</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-dark">
    <nav class="navbar navbar-expand-lg navbar-dark navbar-green">
        <div class="container">
            <a class="navbar-brand" href="department.php">
                💚 <?php echo $department['name']; ?> Dashboard
            </a>
            <div class="navbar-nav ms-auto">
                <span class="nav-link">👋 Welcome, <?php echo $_SESSION['full_name']; ?></span>
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
                        <h2 class="text-light mb-1"><?php echo $department['name']; ?> Tickets</h2>
                        <p class="text-muted mb-0">Manage tickets assigned to your department</p>
                    </div>
                    <div class="text-end">
                        <div class="badge-green">Your Department: <strong><?php echo $department['name']; ?></strong></div>
                    </div>
                </div>

                <!-- Stats Cards -->
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
                                <div class="text-secondary mb-2">⚫</div>
                                <h3 class="text-secondary mb-1"><?php echo $closed_count; ?></h3>
                                <p class="text-muted mb-0">Closed</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Search and Filter -->
                <div class="card card-green mb-4">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <input type="text" id="searchTickets" class="form-control" placeholder="🔍 Search tickets in your department...">
                            </div>
                            <div class="col-md-3">
                                <select id="statusFilter" class="form-select">
                                    <option value="all">All Statuses</option>
                                    <option value="Open">🟢 Open</option>
                                    <option value="In Progress">🟡 In Progress</option>
                                    <option value="Resolved">🔵 Resolved</option>
                                    <option value="Closed">⚫ Closed</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button id="refreshBtn" class="btn btn-outline-green w-100">🔄 Refresh</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tickets Table -->
                <div class="card card-green">
                    <div class="card-header-green">
                        <h4 class="mb-0">Your Department Tickets (<?php echo count($tickets); ?>)</h4>
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
                                <h5 class="text-muted">No tickets assigned to your department</h5>
                                <p class="text-muted">When customers select your department, tickets will appear here.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/script.js"></script>
</body>
</html>