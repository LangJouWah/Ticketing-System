<?php
include 'includes/config.php';
include 'includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Handle file upload
    $file_path = null;
    if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        // Validate file
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf', 'text/plain'];
        $file_type = $_FILES['file']['type'];
        $file_size = $_FILES['file']['size'];
        
        if (in_array($file_type, $allowed_types) && $file_size <= 5 * 1024 * 1024) {
            $file_name = time() . '_' . basename($_FILES['file']['name']);
            $target_path = $upload_dir . $file_name;
            
            if (move_uploaded_file($_FILES['file']['tmp_name'], $target_path)) {
                $file_path = $target_path;
            }
        }
    }
    
    // Insert ticket
    $ticket_number = generateTicketNumber();
    $stmt = $pdo->prepare("INSERT INTO tickets (ticket_number, customer_name, customer_email, department_id, subject, description, file_path) VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    if ($stmt->execute([
        $ticket_number,
        $_POST['customer_name'],
        $_POST['customer_email'],
        $_POST['department_id'],
        $_POST['subject'],
        $_POST['description'],
        $file_path
    ])) {
        header('Location: view_tickets.php?success=1&ticket=' . $ticket_number);
        exit();
    } else {
        header('Location: index.php?error=1');
        exit();
    }
} else {
    header('Location: index.php');
    exit();
}
?>