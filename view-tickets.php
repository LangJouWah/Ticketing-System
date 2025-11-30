<?php 
include 'includes/config.php';
include 'includes/functions.php';

// Simple email-based ticket lookup
$tickets = [];
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['email'])) {
    $tickets = getTicketsByEmail($pdo, $_POST['email']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Tickets - Help Desk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">🛠️ Help Desk</a>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-10 mx-auto">
                <h2 class="mb-4">Your Support Tickets</h2>
                
                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        ✅ Ticket submitted successfully! Your ticket number: <strong><?php echo $_GET['ticket']; ?></strong>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Find Your Tickets</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" class="row g-3">
                            <div class="col-md-8">
                                <input type="email" name="email" class="form-control" placeholder="Enter your email address" value="<?php echo $_POST['email'] ?? ''; ?>" required>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary w-100">Search Tickets</button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <?php if ($_SERVER['REQUEST_METHOD'] == 'POST'): ?>
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Tickets for: <?php echo htmlspecialchars($_POST['email']); ?></h5>
                            <span class="badge bg-primary"><?php echo count($tickets); ?> tickets</span>
                        </div>
                        <div class="card-body p-0">
                            <?php if (count($tickets) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Ticket #</th>
                                                <th>Subject</th>
                                                <th>Department</th>
                                                <th>Status</th>
                                                <th>Date Submitted</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($tickets as $ticket): 
                                                $status_class = [
                                                    'Open' => 'bg-success',
                                                    'In Progress' => 'bg-warning',
                                                    'Resolved' => 'bg-info',
                                                    'Closed' => 'bg-secondary'
                                                ];
                                            ?>
                                            <tr>
                                                <td><strong><?php echo $ticket['ticket_number']; ?></strong></td>
                                                <td><?php echo htmlspecialchars($ticket['subject']); ?></td>
                                                <td><?php echo $ticket['department_name']; ?></td>
                                                <td>
                                                    <span class="badge <?php echo $status_class[$ticket['status']]; ?>">
                                                        <?php echo $ticket['status']; ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('M j, Y g:i A', strtotime($ticket['created_at'])); ?></td>
                                                <td>
                                                    <a href="ticket_details.php?id=<?php echo $ticket['id']; ?>" class="btn btn-sm btn-outline-primary">View Details</a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <p class="text-muted mb-0">No tickets found for this email address.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="mt-4 text-center">
                    <a href="index.php" class="btn btn-primary">Submit New Ticket</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>