<?php
$hash = '$2y$10$vOZ4Me.zt6EDzSWBDF9PMetxjDY8YNBZwXNBTYxomvn9IaQSvNv8S';
foreach (['admin', 'admin123', '123456', 'password', '12345678', 'creono'] as $pwd) {
    if (password_verify($pwd, $hash)) {
        echo "Password is: $pwd\n";
        exit;
    }
}
echo "Unknown, let's reset to 123456\n";
require 'config/config.php';
require 'app/Core/Database.php';
$db = new Database();
$newHash = password_hash('123456', PASSWORD_DEFAULT);
$db->query("UPDATE users SET password = :p WHERE id = 1");
$db->bind(':p', $newHash);
$db->execute();
echo "Admin password reset to 123456\n";
