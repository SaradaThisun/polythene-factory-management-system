<?php
header('Content-Type: application/json');
session_start();
require __DIR__ . '/db.php';


$empUserCode = $_SESSION['user']['userID'] ?? 'emp001';
$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) { echo json_encode(['success'=>false,'message'=>'Invalid task id']); exit; }

try {
  
  $u = $pdo->prepare("SELECT id FROM users WHERE userID=? AND status='ACTIVE'");
  $u->execute([$empUserCode]);
  $usr = $u->fetch();
  if(!$usr){ echo json_encode(['success'=>false,'message'=>"User '$empUserCode' not found or inactive"]); exit; }
  $empId = (int)$usr['id'];

  
  $upd = $pdo->prepare("
    UPDATE tasks
    SET status='ACCEPTED',
        accepted_at = NOW(),
        accepted_by_user_id = ?
    WHERE id=? AND assigned_user_id=? AND status='ASSIGNED'
  ");
  $upd->execute([$empId, $id, $empId]);

  if ($upd->rowCount() === 0) {
    echo json_encode(['success'=>false,'message'=>'Task not found, not assigned to you, or already accepted.']);
  } else {
    echo json_encode(['success'=>true,'message'=>'Task accepted.']);
  }
} catch (Throwable $e) {
  echo json_encode(['success'=>false,'message'=>'Error: '.$e->getMessage()]);
}
