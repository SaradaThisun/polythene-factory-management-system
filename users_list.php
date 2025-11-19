<?php
header('Content-Type: application/json');
require __DIR__ . '/db.php';

$q = trim($_POST['q'] ?? '');

try {
  if ($q === '') {
    $stmt = $pdo->query("SELECT id, userID, userName, role, email, phone, status FROM users ORDER BY created_at DESC");
  } else {
    $stmt = $pdo->prepare("
      SELECT id, userID, userName, role, email, phone, status
      FROM users
      WHERE userID LIKE ? OR userName LIKE ? OR email LIKE ?
      ORDER BY created_at DESC
    ");
    $like = "%$q%";
    $stmt->execute([$like,$like,$like]);
  }
  echo json_encode(['success'=>true, 'rows'=>$stmt->fetchAll()]);
} catch (Throwable $e) {
  echo json_encode(['success'=>false, 'message'=>'Error: '.$e->getMessage()]);
}
