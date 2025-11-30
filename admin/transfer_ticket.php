<?php
include '../includes/config.php';
include '../includes/functions.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ticket_id = $_POST['ticket_id'] ?? 0;
    $new_department_id = $_POST['new_department_id'] ?? 0;
    
    // Update ticket department
    $stmt = $pdo->prepare("UPDATE tickets SET department_id = ? WHERE id = ?");
    $stmt->execute([$new_department_id, $ticket_id]);
    
    // Add transfer note
    $department = getDepartmentById($pdo, $new_department_id);
    $transfer_note = "Ticket transferred to " . $department['name'] . " department by Admin.";
    $stmt = $pdo->prepare("INSERT INTO ticket_replies (ticket_id, reply_text, replied_by) VALUES (?, ?, ?)");
    $stmt->execute([$ticket_id, $transfer_note, 'System']);
    
    header('Location: update_ticket.php?id=' . $ticket_id . '&transferred=1');
    exit();
} else {
    header('Location: index.php');
    exit();
}
?>