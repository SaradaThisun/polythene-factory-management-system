<?php
header('Content-Type: application/json');
session_start();
require __DIR__ . '/db.php';

$employeeUserIdCode = $_SESSION['user']['userID'] ?? 'emp001';

try {
  $stmt = $pdo->prepare("
    SELECT lr.request_code, lr.from_date, lr.to_date, lr.status,
           COALESCE(u.userName, '') AS approved_by
    FROM leave_requests lr
    LEFT JOIN users u ON u.id = lr.approved_by_user_id
    WHERE lr.employee_user_id = (SELECT id FROM users WHERE userID=?)
    ORDER BY lr.created_at DESC
    LIMIT 10
  ");
  $stmt->execute([$employeeUserIdCode]);
  echo json_encode(['success'=>true,'rows'=>$stmt->fetchAll()]);
} catch (Throwable $e) {
  echo json_encode(['success'=>false,'message'=>'Error: '.$e->getMessage()]);
}
    