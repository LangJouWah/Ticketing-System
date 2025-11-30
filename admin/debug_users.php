<?php
include '../includes/config.php';
include '../includes/functions.php';

echo "<h3>Checking Department Users</h3>";

$stmt = $pdo->query("SELECT * FROM department_users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($users)) {
    echo "<p style='color: red;'>No users found in department_users table!</p>";
} else {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Username</th><th>Password</th><th>Full Name</th><th>Department</th><th>Active</th></tr>";
    foreach ($users as $user) {
        echo "<tr>";
        echo "<td>{$user['id']}</td>";
        echo "<td>{$user['username']}</td>";
        echo "<td>{$user['password']}</td>";
        echo "<td>{$user['full_name']}</td>";
        echo "<td>{$user['department_id']}</td>";
        echo "<td>{$user['is_active']}</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Test password
echo "<h3>Test Password Verification</h3>";
$test_password = 'password';
$hashed = password_hash($test_password, PASSWORD_DEFAULT);
echo "Test password hash: " . $hashed . "<br>";
echo "Password verify test: " . (password_verify($test_password, $hashed) ? 'SUCCESS' : 'FAILED');
?>