<?php
header('Content-Type: application/json');
require __DIR__ . '/db.php';

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) { echo json_encode(['success'=>false,'message'=>'Invalid id']); exit; }

try {
  $del = $pdo->prepare("DELETE FROM users WHERE id=?");
  $del->execute([$id]);
  if ($del->rowCount() === 0) {
    echo json_encode(['success'=>false,'message'=>'User not found or already deleted.']);
  } else {
    echo json_encode(['success'=>true,'message'=>'User deleted successfully.']);
  }
} catch (Throwable $e) {
  echo json_encode(['success'=>false,'message'=>'Error: '.$e->getMessage()]);
}
