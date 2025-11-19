<?php

header('Content-Type: application/json');
require __DIR__ . '/db.php';


$managerUserId = 'mgr01';

$code = trim($_POST['purchaseId'] ?? '');
if ($code === '') {
  echo json_encode(['success' => false, 'message' => 'Purchase Request ID is required.']);
  exit;
}

try {
  
  $stmt = $pdo->prepare("SELECT id FROM users WHERE userID = ? AND role = 'MANAGER' AND status='ACTIVE'");
  $stmt->execute([$managerUserId]);
  $mgr = $stmt->fetch();
  if (!$mgr) {
    echo json_encode(['success' => false, 'message' => 'Manager user not found or inactive.']);
    exit;
  }
  $managerId = (int)$mgr['id'];

  
  $upd = $pdo->prepare("
    UPDATE purchase_requests
    SET status = 'APPROVED',
        approved_by_user_id = ?,
        approved_at = NOW()
    WHERE request_code = ? AND status = 'PENDING'
  ");
  $upd->execute([$managerId, $code]);

  if ($upd->rowCount() === 0) {
    echo json_encode(['success' => false, 'message' => "No pending purchase found for '$code' or it is already processed."]);
  } else {
    echo json_encode(['success' => true, 'message' => "Purchase Request '$code' approved successfully."]);
  }
} catch (Throwable $e) {
  echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
