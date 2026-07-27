<?php
require_once 'includes/config.php';

$sql = file_get_contents('database.sql');
$statements = array_filter(array_map('trim', explode(';', $sql)));

$skipped = 0;
$executed = 0;
foreach ($statements as $stmt) {
    if (empty($stmt) || strpos($stmt, 'CREATE DATABASE') !== false || strpos($stmt, 'USE ') === 0) {
        $skipped++;
        continue;
    }
    try {
        $pdo->exec($stmt);
        $executed++;
    } catch (Exception $e) {
        // skip errors on re-runs
    }
}

echo "Setup complete. Executed: $executed, Skipped: $skipped\n";

// Fix passwords
$hash = password_hash('password', PASSWORD_DEFAULT);
$pdo->prepare("UPDATE users SET password = ?")->execute([$hash]);
echo "Passwords updated to: password\n";
