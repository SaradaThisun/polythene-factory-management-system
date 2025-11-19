<?php
header('Content-Type: application/json');
session_start();
require __DIR__ . '/db.php';


$empUserCode = $_SESSION['user']['userID'] ?? 'emp001';

$q        = trim($_POST['q'] ?? '');
$status   = trim($_POST['status'] ?? '');
$fromDate = trim($_POST['fromDate'] ?? '');
$toDate   = trim($_POST['toDate'] ?? '');

try {
  
  $u = $pdo->prepare("SELECT id FROM users WHERE userID=? AND status='ACTIVE'");
  $u->execute([$empUserCode]);
  $user = $u->fetch();
  if(!$user){ echo json_encode(['success'=>false,'message'=>"User '$empUserCode' not found or inactive."]); exit; }
  $empId = (int)$user['id'];

  $sql = "SELECT task_code, title, status,
                 DATE_FORMAT(due_date,'%Y-%m-%d') AS due_date,
                 DATE_FORMAT(created_at,'%Y-%m-%d %H:%i') AS created_at,
                 DATE_FORMAT(accepted_at,'%Y-%m-%d %H:%i') AS accepted_at,
                 DATE_FORMAT(started_at,'%Y-%m-%d %H:%i')  AS started_at,
                 DATE_FORMAT(completed_at,'%Y-%m-%d %H:%i') AS completed_at
          FROM tasks
          WHERE assigned_user_id = ?";
  $args = [$empId];

  if ($q !== '') {
    $sql .= " AND (task_code LIKE ? OR title LIKE ?)";
    $like = "%$q%";
    $args[] = $like; $args[] = $like;
  }
  if ($status !== '') {
    $sql .= " AND status = ?";
    $args[] = $status;
  }
  if ($fromDate !== '') {
    $sql .= " AND (due_date IS NULL OR due_date >= ?)";
    $args[] = $fromDate;
  }
  if ($toDate !== '') {
    $sql .= " AND (due_date IS NULL OR due_date <= ?)";
    $args[] = $toDate;
  }

  $sql .= " ORDER BY 
              CASE status
                WHEN 'ASSIGNED' THEN 1
                WHEN 'ACCEPTED' THEN 2
                WHEN 'IN_PROGRESS' THEN 3
                WHEN 'COMPLETED' THEN 4
                WHEN 'CANCELLED' THEN 5
              END,
              due_date IS NULL, due_date ASC, created_at DESC";

  $stmt = $pdo->prepare($sql);
  $stmt->execute($args);

  echo json_encode(['success'=>true,'rows'=>$stmt->fetchAll()]);
} catch (Throwable $e) {
  echo json_encode(['success'=>false,'message'=>'Error: '.$e->getMessage()]);
}
