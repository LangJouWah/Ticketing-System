<?php
session_start();
if(!isset($_SESSION['admin'])) { 
    header("Location: login.php"); 
    die(); 
}
include '../config.php';

$id = $_GET['id'] ?? 0;
$ticket = $pdo->prepare("SELECT t.*, d.name as dept FROM tickets t LEFT JOIN departments d ON t.department_id = d.id WHERE t.id = ?");
$ticket->execute([$id]);
$ticket = $ticket->fetch();

if(!$ticket) { die("Ticket not found"); }

// Update status if changed
if(isset($_POST['status'])) {
    $newStatus = $_POST['status'];
    $pdo->prepare("UPDATE tickets SET status = ? WHERE id = ?")->execute([$newStatus, $id]);
    $ticket['status'] = $newStatus; // refresh
}

// Add admin reply
if(isset($_POST['reply'])) {
    $reply = trim($_POST['reply']);
    if($reply != "") {
        $pdo->prepare("INSERT INTO ticket_replies (ticket_id, message, is_admin) VALUES (?, ?, 1)")
            ->execute([$id, $reply]);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reply to Ticket #<?= $ticket['ticket_id'] ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-dark mb-4">
    <div class="container-fluid">
        <span class="navbar-brand">Reply to <?= $ticket['ticket_id'] ?></span>
        <a href="dashboard.php" class="btn btn-secondary btn-sm">Back to Dashboard</a>
        <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
    </div>
</nav>

<div class="container">
    <div class="card shadow mb-4">
        <div class="card-header bg-primary text-white">
            <h4>Ticket Details</h4>
        </div>
        <div class="card-body">
            <p><strong>ID:</strong> <?= $ticket['ticket_id'] ?></p>
            <p><strong>Name:</strong> <?= htmlspecialchars($ticket['name']) ?></p>
            <p><strong>Email:</strong> <?= $ticket['email'] ?></p>
            <p><strong>Department:</strong> <?= $ticket['dept'] ?></p>
            <p><strong>Subject:</strong> <?= htmlspecialchars($ticket['subject']) ?></p>
            <p><strong>Message:</strong><br><?= nl2br(htmlspecialchars($ticket['message'])) ?></p>
            <?php if($ticket['attachment']): ?>
                <p><strong>Attachment:</strong> 
                    <a href="../assets/uploads/<?= $ticket['attachment'] ?>" target="_blank" class="btn btn-sm btn-info">
                        Download File
                    </a>
                </p>
            <?php endif; ?>

            <!-- Change Status -->
            <form method="POST" class="mt-3">
                <label><strong>Status:</strong></label>
                <select name="status" onchange="this.form.submit()" class="form-select w-auto d-inline ms-2">
                    <option <?= $ticket['status']=='Open'?'selected':'' ?>>Open</option>
                    <option <?= $ticket['status']=='In Progress'?'selected':'' ?>>In Progress</option>
                    <option <?= $ticket['status']=='Resolved'?'selected':'' ?>>Resolved</option>
                    <option <?= $ticket['status']=='Closed'?'selected':'' ?>>Closed</option>
                </select>
            </form>
        </div>
    </div>

    <!-- Reply Box -->
    <div class="card shadow mb-4">
        <div class="card-header bg-success text-white">
            <h5>Send Reply</h5>
        </div>
        <div class="card-body">
            <form method="POST">
                <textarea name="reply" class="form-control" rows="4" placeholder="Type your reply here..." required></textarea>
                <button type="submit" class="btn btn-success mt-3">Send Reply</button>
            </form>
        </div>
    </div>

    <!-- Show All Replies -->
    <div class="card shadow">
        <div class="card-header bg-secondary text-white">
            <h5>Conversation</h5>
        </div>
        <div class="card-body">
            <?php
            $replies = $pdo->prepare("SELECT * FROM ticket_replies WHERE ticket_id = ? ORDER BY created_at ASC");
            $replies->execute([$id]);
            while($r = $replies->fetch()):
            ?>
                <div class="border-bottom pb-3 mb-3">
                    <strong><?= $r['is_admin'] ? 'Admin' : 'User' ?> </strong>
                    <small class="text-muted">(<?= date('d M Y H:i', strtotime($r['created_at'])) ?>)</small>
                    <p class="mt-2"><?= nl2br(htmlspecialchars($r['message'])) ?></p>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>
</body>
</html>