<?php
include 'includes/config.php';
include 'includes/functions.php';

$ticket_id = $_GET['id'] ?? 0;
$ticket = getTicketById($pdo, $ticket_id);
$replies = getTicketReplies($pdo, $ticket_id);

if (!$ticket) {
    header('Location: view_tickets.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket Details - Help Desk</title>
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
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <li class="breadcrumb-item"><a href="view_tickets.php">View Tickets</a></li>
                        <li class="breadcrumb-item active">Ticket Details</li>
                    </ol>
                </nav>

                <div class="card">
                    <div class="card-header bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="mb-0">Ticket: <?php echo $ticket['ticket_number']; ?></h4>
                            <span class="badge <?php 
                                echo [
                                    'Open' => 'bg-success',
                                    'In Progress' => 'bg-warning',
                                    'Resolved' => 'bg-info',
                                    'Closed' => 'bg-secondary'
                                ][$ticket['status']]; 
                            ?>"><?php echo $ticket['status']; ?></span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <p><strong>Customer:</strong> <?php echo htmlspecialchars($ticket['customer_name']); ?></p>
                                <p><strong>Email:</strong> <?php echo $ticket['customer_email']; ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Department:</strong> <?php echo $ticket['department_name']; ?></p>
                                <p><strong>Submitted:</strong> <?php echo date('F j, Y g:i A', strtotime($ticket['created_at'])); ?></p>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h5>Subject</h5>
                            <p class="lead"><?php echo htmlspecialchars($ticket['subject']); ?></p>
                        </div>

                        <div class="mb-4">
                            <h5>Issue Description</h5>
                            <div class="border rounded p-3 bg-light">
                                <?php echo nl2br(htmlspecialchars($ticket['description'])); ?>
                            </div>
                        </div>

                        <?php if ($ticket['file_path']): ?>
                        <div class="mb-4">
                            <h5>Attachment</h5>
                            <a href="<?php echo $ticket['file_path']; ?>" target="_blank" class="btn btn-outline-primary">
                                📎 View Attached File
                            </a>
                        </div>
                        <?php endif; ?>

                        <hr>

                        <h5 class="mb-3">Conversation</h5>
                        
                        <!-- Original Ticket -->
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <strong><?php echo htmlspecialchars($ticket['customer_name']); ?></strong>
                                <small class="text-muted float-end"><?php echo date('M j, Y g:i A', strtotime($ticket['created_at'])); ?></small>
                            </div>
                            <div class="card-body">
                                <p class="mb-0"><?php echo nl2br(htmlspecialchars($ticket['description'])); ?></p>
                            </div>
                        </div>

                        <!-- Admin Replies -->
                        <?php foreach($replies as $reply): ?>
                        <div class="card mb-3 border-primary">
                            <div class="card-header bg-primary text-white">
                                <strong>Admin</strong>
                                <small class="float-end"><?php echo date('M j, Y g:i A', strtotime($reply['created_at'])); ?></small>
                            </div>
                            <div class="card-body">
                                <p class="mb-0"><?php echo nl2br(htmlspecialchars($reply['reply_text'])); ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>

                        <?php if (empty($replies)): ?>
                        <div class="alert alert-info text-center">
                            <p class="mb-0">No replies yet. Our support team will respond soon.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mt-3 text-center">
                    <a href="view_tickets.php" class="btn btn-outline-primary">← Back to Tickets</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>