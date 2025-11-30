<?php
include '../includes/config.php';
include '../includes/functions.php';
requireLogin();

$ticket_id = $_GET['id'] ?? 0;
$ticket = getTicketById($pdo, $ticket_id);

if (!$ticket) {
    header('Location: ' . (isAdmin() ? 'index.php' : 'department.php'));
    exit();
}

// Check if department user has access to this ticket
if (!isAdmin() && $ticket['department_id'] != getDepartmentId()) {
    header('Location: department.php?error=access_denied');
    exit();
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $stmt = $pdo->prepare("UPDATE tickets SET status = ? WHERE id = ?");
    $stmt->execute([$_POST['status'], $ticket_id]);
    header('Location: update_ticket.php?id=' . $ticket_id . '&updated=1');
    exit();
}

// Handle reply
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_reply'])) {
    $reply_text = trim($_POST['reply_text']);
    if (!empty($reply_text)) {
        $replied_by = isAdmin() ? 'Admin' : $_SESSION['full_name'];
        $stmt = $pdo->prepare("INSERT INTO ticket_replies (ticket_id, reply_text, replied_by) VALUES (?, ?, ?)");
        $stmt->execute([$ticket_id, $reply_text, $replied_by]);
        header('Location: update_ticket.php?id=' . $ticket_id . '&replied=1');
        exit();
    }
}

$replies = getTicketReplies($pdo, $ticket_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Ticket - <?php echo isAdmin() ? 'Helport Admin' : $_SESSION['department_name'] . ' Dashboard'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-dark">
    <nav class="navbar navbar-expand-lg navbar-dark navbar-green">
        <div class="container">
            <a class="navbar-brand" href="<?php echo isAdmin() ? 'index.php' : 'department.php'; ?>">
                💚 <?php echo isAdmin() ? 'Helport Admin' : $_SESSION['department_name']; ?>
            </a>
            <div class="navbar-nav ms-auto">
                <span class="nav-link">
                    👋 <?php echo isAdmin() ? 'Admin' : $_SESSION['full_name']; ?>
                </span>
                <a class="nav-link" href="<?php echo isAdmin() ? 'index.php' : 'department.php'; ?>">
                    ← Back to <?php echo isAdmin() ? 'Tickets' : 'Dashboard'; ?>
                </a>
                <a class="nav-link" href="../index.php">🌐 Public Site</a>
                <a class="nav-link" href="logout.php">🚪 Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if (isset($_GET['updated'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>✅ Success!</strong> Ticket status updated successfully!
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['replied'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>✅ Success!</strong> Reply added successfully!
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error']) && $_GET['error'] == 'access_denied'): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>❌ Access Denied!</strong> You can only access tickets from your department.
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Access Info Banner -->
        <div class="alert alert-info mb-4">
            <strong>ℹ️ Access Level:</strong> 
            <?php if (isAdmin()): ?>
                👑 <strong>System Administrator</strong> - Full access to all tickets and departments
            <?php else: ?>
                🛠️ <strong><?php echo $_SESSION['department_name']; ?> Department</strong> - Access to <?php echo $_SESSION['department_name']; ?> tickets only
            <?php endif; ?>
        </div>

        <div class="row">
            <div class="col-md-8">
                <!-- Ticket Details -->
                <div class="card card-green mb-4">
                    <div class="card-header-green">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="mb-0">Ticket: <?php echo $ticket['ticket_number']; ?></h4>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge <?php 
                                    echo [
                                        'Open' => 'badge-success',
                                        'In Progress' => 'badge-warning',
                                        'Resolved' => 'badge-info',
                                        'Closed' => 'badge-secondary'
                                    ][$ticket['status']]; 
                                ?>"><?php echo $ticket['status']; ?></span>
                                <?php if (!isAdmin()): ?>
                                    <span class="badge badge-green">Your Department</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <p class="mb-2">
                                    <strong class="text-green">Customer:</strong><br>
                                    <span class="text-light"><?php echo htmlspecialchars($ticket['customer_name']); ?></span>
                                </p>
                                <p class="mb-0">
                                    <strong class="text-green">Email:</strong><br>
                                    <span class="text-muted"><?php echo $ticket['customer_email']; ?></span>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-2">
                                    <strong class="text-green">Department:</strong><br>
                                    <span class="text-light"><?php echo $ticket['department_name']; ?></span>
                                </p>
                                <p class="mb-0">
                                    <strong class="text-green">Submitted:</strong><br>
                                    <span class="text-muted"><?php echo date('F j, Y g:i A', strtotime($ticket['created_at'])); ?></span>
                                </p>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h5 class="text-green mb-3">📋 Subject</h5>
                            <p class="lead text-light bg-dark p-3 rounded border border-secondary"><?php echo htmlspecialchars($ticket['subject']); ?></p>
                        </div>

                        <div class="mb-4">
                            <h5 class="text-green mb-3">📝 Issue Description</h5>
                            <div class="border border-secondary rounded p-3 bg-darker-bg">
                                <div class="text-light"><?php echo nl2br(htmlspecialchars($ticket['description'])); ?></div>
                            </div>
                        </div>

                        <?php if ($ticket['file_path']): ?>
                        <div class="mb-3">
                            <h5 class="text-green mb-3">📎 Attachment</h5>
                            <a href="../<?php echo $ticket['file_path']; ?>" target="_blank" class="btn btn-outline-green">
                                📁 View Attached File
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Replies -->
                <div class="card card-green">
                    <div class="card-header-green">
                        <h5 class="mb-0">💬 Conversation</h5>
                    </div>
                    <div class="card-body">
                        <!-- Original Ticket -->
                        <div class="card card-green mb-3">
                            <div class="card-header bg-dark border-secondary d-flex justify-content-between align-items-center">
                                <strong class="text-green">👤 <?php echo htmlspecialchars($ticket['customer_name']); ?></strong>
                                <small class="text-muted"><?php echo date('M j, Y g:i A', strtotime($ticket['created_at'])); ?></small>
                            </div>
                            <div class="card-body">
                                <p class="mb-0 text-light"><?php echo nl2br(htmlspecialchars($ticket['description'])); ?></p>
                            </div>
                        </div>

                        <!-- Admin/Department Replies -->
                        <?php foreach($replies as $reply): ?>
                        <div class="card card-green mb-3 border-green">
                            <div class="card-header bg-green-light border-green d-flex justify-content-between align-items-center">
                                <strong class="text-dark-green">
                                    <?php if ($reply['replied_by'] === 'Admin'): ?>
                                        👑 Admin
                                    <?php else: ?>
                                        🛠️ <?php echo htmlspecialchars($reply['replied_by']); ?>
                                    <?php endif; ?>
                                </strong>
                                <small class="text-dark-green"><?php echo date('M j, Y g:i A', strtotime($reply['created_at'])); ?></small>
                            </div>
                            <div class="card-body">
                                <p class="mb-0 text-light"><?php echo nl2br(htmlspecialchars($reply['reply_text'])); ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>

                        <?php if (empty($replies)): ?>
                        <div class="alert alert-info text-center mb-4">
                            <p class="mb-0">💭 No replies yet. Add the first response to this ticket.</p>
                        </div>
                        <?php endif; ?>

                        <!-- Add Reply Form -->
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-green">
                                    ✏️ Add Reply 
                                    <?php if (!isAdmin()): ?>
                                        <small class="text-muted">(as <?php echo $_SESSION['full_name']; ?>)</small>
                                    <?php endif; ?>
                                </label>
                                <textarea name="reply_text" class="form-control" rows="5" placeholder="Type your response here... Be helpful and professional!" required></textarea>
                                <div class="form-text text-muted">
                                    Your reply will be visible to the customer. 
                                    <?php if (!isAdmin()): ?>
                                        It will be signed with your name: <strong><?php echo $_SESSION['full_name']; ?></strong>
                                    <?php else: ?>
                                        It will be signed as <strong>Admin</strong>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <button type="submit" name="add_reply" class="btn btn-green">
                                📤 Send Reply
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <!-- Status Update -->
                <div class="card card-green mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0">⚡ Ticket Actions</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-light">Update Status</label>
                                <select name="status" class="form-select">
                                    <option value="Open" <?php echo $ticket['status'] == 'Open' ? 'selected' : ''; ?>>🟢 Open</option>
                                    <option value="In Progress" <?php echo $ticket['status'] == 'In Progress' ? 'selected' : ''; ?>>🟡 In Progress</option>
                                    <option value="Resolved" <?php echo $ticket['status'] == 'Resolved' ? 'selected' : ''; ?>>🔵 Resolved</option>
                                    <option value="Closed" <?php echo $ticket['status'] == 'Closed' ? 'selected' : ''; ?>>⚫ Closed</option>
                                </select>
                            </div>
                            <button type="submit" name="update_status" class="btn btn-warning w-100 fw-bold">
                                💾 Update Status
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card card-green mb-4">
                    <div class="card-header bg-info text-dark">
                        <h5 class="mb-0">🚀 Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-outline-green text-start" onclick="document.querySelector('textarea[name=\"reply_text\"]').value = 'Hello ' + '<?php echo htmlspecialchars($ticket['customer_name']); ?>' + ',\\n\\nThank you for contacting support. We are looking into your issue and will get back to you shortly.\\n\\nBest regards,\\n<?php echo isAdmin() ? "Support Team" : $_SESSION["full_name"]; ?>'">
                                💌 Quick Response
                            </button>
                            <button type="button" class="btn btn-outline-green text-start" onclick="document.querySelector('textarea[name=\"reply_text\"]').value = 'Hello ' + '<?php echo htmlspecialchars($ticket['customer_name']); ?>' + ',\\n\\nWe need more information to help resolve your issue. Could you please provide:\\n\\n1. \\n2. \\n3. \\n\\nThank you!\\n\\n<?php echo isAdmin() ? "Support Team" : $_SESSION["full_name"]; ?>'">
                                ❓ Request Info
                            </button>
                            <button type="button" class="btn btn-outline-green text-start" onclick="document.querySelector('textarea[name=\"reply_text\"]').value = 'Hello ' + '<?php echo htmlspecialchars($ticket['customer_name']); ?>' + ',\\n\\nThis issue has been resolved. If you have any further questions, please don\\'t hesitate to contact us.\\n\\nBest regards,\\n<?php echo isAdmin() ? "Support Team" : $_SESSION["full_name"]; ?>'">
                                ✅ Mark Resolved
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Ticket Info -->
                <div class="card card-green">
                    <div class="card-header-green">
                        <h5 class="mb-0">📊 Ticket Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <strong class="text-green d-block mb-1">Ticket Number:</strong>
                            <span class="text-light"><?php echo $ticket['ticket_number']; ?></span>
                        </div>
                        
                        <div class="mb-3">
                            <strong class="text-green d-block mb-1">Current Status:</strong>
                            <span class="badge <?php 
                                echo [
                                    'Open' => 'badge-success',
                                    'In Progress' => 'badge-warning',
                                    'Resolved' => 'badge-info',
                                    'Closed' => 'badge-secondary'
                                ][$ticket['status']]; 
                            ?>"><?php echo $ticket['status']; ?></span>
                        </div>
                        
                        <div class="mb-3">
                            <strong class="text-green d-block mb-1">Replies:</strong>
                            <span class="text-light"><?php echo count($replies); ?> <?php echo count($replies) === 1 ? 'reply' : 'replies'; ?></span>
                        </div>
                        
                        <div class="mb-3">
                            <strong class="text-green d-block mb-1">Department:</strong>
                            <span class="text-light"><?php echo $ticket['department_name']; ?></span>
                        </div>
                        
                        <div class="mb-3">
                            <strong class="text-green d-block mb-1">Created:</strong>
                            <span class="text-muted small"><?php echo date('M j, Y g:i A', strtotime($ticket['created_at'])); ?></span>
                        </div>
                        
                        <div class="mb-0">
                            <strong class="text-green d-block mb-1">Last Activity:</strong>
                            <span class="text-muted small">
                                <?php 
                                if (!empty($replies)) {
                                    $last_reply = end($replies);
                                    echo date('M j, Y g:i A', strtotime($last_reply['created_at']));
                                } else {
                                    echo date('M j, Y g:i A', strtotime($ticket['created_at']));
                                }
                                ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Customer Info -->
                <div class="card card-green mt-4">
                    <div class="card-header-green">
                        <h5 class="mb-0">👤 Customer Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <strong class="text-green">Name:</strong><br>
                            <span class="text-light"><?php echo htmlspecialchars($ticket['customer_name']); ?></span>
                        </div>
                        <div class="mb-2">
                            <strong class="text-green">Email:</strong><br>
                            <span class="text-muted"><?php echo $ticket['customer_email']; ?></span>
                        </div>
                        <div class="mt-3">
                            <a href="mailto:<?php echo $ticket['customer_email']; ?>?subject=Re: Ticket <?php echo $ticket['ticket_number']; ?> - <?php echo urlencode($ticket['subject']); ?>" class="btn btn-sm btn-outline-green w-100">
                                📧 Email Customer
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Department Transfer (Admin Only) -->
                <?php if (isAdmin()): ?>
                <div class="card card-green mt-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">🔄 Transfer Ticket</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="transfer_ticket.php">
                            <input type="hidden" name="ticket_id" value="<?php echo $ticket_id; ?>">
                            <div class="mb-3">
                                <label class="form-label text-light">Transfer to Department:</label>
                                <select name="new_department_id" class="form-select">
                                    <?php
                                    $departments = getDepartments($pdo);
                                    foreach($departments as $dept) {
                                        $selected = $dept['id'] == $ticket['department_id'] ? 'selected' : '';
                                        echo "<option value='{$dept['id']}' $selected>{$dept['name']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary w-100" onclick="return confirm('Are you sure you want to transfer this ticket?')">
                                🔄 Transfer Ticket
                            </button>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-focus on reply textarea
        document.addEventListener('DOMContentLoaded', function() {
            const replyTextarea = document.querySelector('textarea[name="reply_text"]');
            if (replyTextarea) {
                replyTextarea.focus();
            }

            // Add animation to status update
            const statusForm = document.querySelector('form[method="POST"]');
            if (statusForm) {
                statusForm.addEventListener('submit', function(e) {
                    const submitBtn = this.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Updating...';
                        submitBtn.disabled = true;
                    }
                });
            }

            // Add character counter to reply textarea
            const replyTextareaWithCounter = document.querySelector('textarea[name="reply_text"]');
            if (replyTextareaWithCounter) {
                const counter = document.createElement('div');
                counter.className = 'form-text text-end text-muted mt-1';
                counter.innerHTML = 'Characters: <span id="replyCharCount">0</span>';
                replyTextareaWithCounter.parentNode.appendChild(counter);

                replyTextareaWithCounter.addEventListener('input', function() {
                    const charCount = document.getElementById('replyCharCount');
                    if (charCount) {
                        charCount.textContent = this.value.length;
                        
                        // Change color based on length
                        if (this.value.length < 10) {
                            charCount.className = 'text-danger fw-bold';
                        } else if (this.value.length < 50) {
                            charCount.className = 'text-warning fw-bold';
                        } else {
                            charCount.className = 'text-success fw-bold';
                        }
                    }
                });

                // Initial count
                replyTextareaWithCounter.dispatchEvent(new Event('input'));
            }
        });

        // Quick action button effects
        document.querySelectorAll('.btn-outline-green').forEach(btn => {
            btn.addEventListener('click', function() {
                this.style.transform = 'scale(0.98)';
                setTimeout(() => {
                    this.style.transform = 'scale(1)';
                }, 150);
            });
        });

        // Auto-save draft reply
        let draftTimer;
        const replyTextarea = document.querySelector('textarea[name="reply_text"]');
        if (replyTextarea) {
            replyTextarea.addEventListener('input', function() {
                clearTimeout(draftTimer);
                draftTimer = setTimeout(() => {
                    localStorage.setItem('ticket_reply_draft_<?php echo $ticket_id; ?>', this.value);
                }, 1000);
            });

            // Load draft on page load
            const draft = localStorage.getItem('ticket_reply_draft_<?php echo $ticket_id; ?>');
            if (draft && !replyTextarea.value) {
                if (confirm('Found a saved draft. Would you like to restore it?')) {
                    replyTextarea.value = draft;
                    replyTextarea.dispatchEvent(new Event('input'));
                }
            }

            // Clear draft when form is submitted
            document.querySelector('form').addEventListener('submit', function() {
                localStorage.removeItem('ticket_reply_draft_<?php echo $ticket_id; ?>');
            });
        }
    </script>
</body>
</html>