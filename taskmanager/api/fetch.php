<?php
header('Content-Type: application/json');
require_once '../includes/db.php';

$stmt = $pdo->query("SELECT * FROM tasks ORDER BY due_date ASC, id ASC");
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($tasks);
?>