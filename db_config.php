<?php
// Database configuration for InfinityFree
// Replace the placeholders with your actual details from the InfinityFree Control Panel

$host = 'sql313.infinityfree.com'; // e.g. sql205.infinityfree.com
$dbname = 'if0_41753752_database'; // e.g. if0_36542123_meghna_db
$username = 'if0_41753752'; // e.g. if0_36542123
$password = 'PARTHJGI2028';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // In production, you might want to log this instead of echo
    die("Connection failed: " . $e->getMessage());
}
?>
