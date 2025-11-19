<?php

header('Content-Type: application/json');
session_start();
require __DIR__ . '/db.php';


$employeeUserIdCode = $_SESSION['user']['userID'] ?? 'emp001';

$from = trim($_POST['fromDate'] ?? '');
$to   = trim($_POST['toDate']   ?? '');
$reason = trim($_POST['reason'] ?? '');

if ($from === '' || $to === '') {
  echo json_encode(['success'=>false,'message'=>'From and To dates are required.']); exit;
}
if ($to < $from) {
  echo json_encode(['success'=>false,'message'=>'To Date must be after or equal to From Date.']); exit;
}

try {
  
  $pdo->exec("
    CREATE TABLE IF NOT EXISTS leave_requests (
      id                  INT AUTO_INCREMENT PRIMARY KEY,
      request_code        VARCHAR(50) NOT NULL UNIQUE,
      employee_user_id    INT NOT NULL,
      from_date           DATE NOT NULL,
      to_date             DATE NOT NULL,
      reason              VARCHAR(200),
      status              ENUM('PENDING','APPROVED','REJECTED') DEFAULT 'PENDING',
      created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      approved_by_user_id INT NULL,
      approved_at         DATETIME NULL
    )
  ");

  
  $u = $pdo->prepare("SELECT id FROM users WHERE userID=? AND status='ACTIVE'");
  $u->execute([$employeeUserIdCode]);
  $user = $u->fetch();
  if (!$user) {
    echo json_encode(['success'=>false,'message'=>"User '$employeeUserIdCode' not found or inactive."]); exit;
  }
  $empId = (int)$user['id'];

  
  $y = date('Y');
  $seqRow = $pdo->query("SELECT LPAD(COALESCE(MAX(id)+1,1),3,'0') AS seq FROM leave_requests")->fetch();
  $seq = $seqRow ? $seqRow['seq'] : '001';
  $code = "LR-$y-$seq";

  
  $ins = $pdo->prepare("
    INSERT INTO leave_requests (request_code, employee_user_id, from_date, to_date, reason)
    VALUES (?, ?, ?, ?, ?)
  ");
  $ins->execute([$code, $empId, $from, $to, $reason]);

  echo json_encode(['success'=>true,'message'=>"Leave request submitted. Code: $code"]);
} catch (Throwable $e) {
  echo json_encode(['success'=>false,'message'=>'Error: '.$e->getMessage()]);
}
