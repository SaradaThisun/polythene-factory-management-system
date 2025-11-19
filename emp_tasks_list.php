<?php
header('Content-Type: application/json');
session_start();
require __DIR__ . '/db.php';


$empUserCode = $_SESSION['user']['userID'] ?? 'emp001';

try {

  $u = $pdo->prepare("SELECT id FROM users WHERE userID=?");
  $u->execute([$empUserCode]);
  $row = $u->fetch();
  if(!$row){ echo json_encode(['success'=>false,'message'=>"User '$empUserCode' not found"]); exit; }
  $empId = (int)$row['id'];

  
  $q1 = $pdo->prepare("
    SELECT id, task_code, title, DATE_FORMAT(due_date,'%Y-%m-%d') AS due_date
    FROM tasks
    WHERE assigned_user_id = ? AND status = 'ASSIGNED'
    ORDER BY due_date IS NULL, due_date ASC, id DESC
  ");
  $q1->execute([$empId]);
  $assigned = $q1->fetchAll();

  
  $q2 = $pdo->prepare("
    SELECT id, task_code, title, status,
           DATE_FORMAT(due_date,'%Y-%m-%d') AS due_date,
           DATE_FORMAT(accepted_at,'%Y-%m-%d %H:%i') AS accepted_at
    FROM tasks
    WHERE assigned_user_id = ?
    ORDER BY COALESCE(accepted_at, created_at) DESC
    LIMIT 10
  ");
  $q2->execute([$empId]);
  $mine = $q2->fetchAll();

  echo json_encode(['success'=>true, 'assigned'=>$assigned, 'mine'=>$mine]);
} catch (Throwable $e) {
  echo json_encode(['success'=>false,'message'=>'Error: '.$e->getMessage()]);
}
