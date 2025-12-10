<?php
header('Content-Type: application/json');
require_once '../includes/db.php';

$data = json_decode(file_get_contents('php://input'), true);
$title = trim($data['title'] ?? '');
$category = $data['category'] ?? 'personal';
$due_date = !empty($data['due_date']) ? $data['due_date'] : null;

if (!$title) {
    http_response_code(400);
    echo json_encode(['error' => 'Title is required']);
    exit;
}

$stmt = $pdo->prepare("INSERT INTO tasks (title, category, due_date) VALUES (?, ?, ?)");
$stmt->execute([$title, $category, $due_date]);

echo json_encode(['success' => true]);
?>