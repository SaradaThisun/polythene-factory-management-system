<?php
header('Content-Type: application/json');
require __DIR__ . '/db.php';

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) { echo json_encode(['success'=>false,'message'=>'Invalid id']); exit; }

try {
  $stmt = $pdo->prepare("SELECT id, userID, userName, NIC as nic, email, phone, role, status FROM users WHERE id=?");
  $stmt->execute([$id]);
  $row = $stmt->fetch();
  if (!$row) { echo json_encode(['success'=>false,'message'=>'User not found']); exit; }
  echo json_encode(['success'=>true,'row'=>$row]);
} catch (Throwable $e) {
  echo json_encode(['success'=>false,'message'=>'Error: '.$e->getMessage()]);
}
