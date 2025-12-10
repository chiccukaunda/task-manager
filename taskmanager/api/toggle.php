<?php
header('Content-Type: application/json');
require_once '../includes/db.php';

$data = json_decode(file_get_contents('php://input'), true);
$id = (int)($data['id'] ?? 0);
$completed = (int)($data['completed'] ?? 0);

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid ID']);
    exit;
}

$stmt = $pdo->prepare("UPDATE tasks SET completed = ? WHERE id = ?");
$stmt->execute([$completed, $id]);

echo json_encode(['success' => true]);
?>