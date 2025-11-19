<?php

header('Content-Type: application/json');
require __DIR__ . '/db.php';


$gmUserId = 'gm01';

$orderCode  = trim($_POST['orderCode']  ?? '');
$supplierId = (int)($_POST['supplierId'] ?? 0);
$itemName   = trim($_POST['itemName']   ?? '');
$qty        = (float)($_POST['qty']      ?? 0);
$unitPrice  = (float)($_POST['unitPrice']?? 0);

if ($orderCode==='' || $supplierId<=0 || $itemName==='' || $qty<=0 || $unitPrice<0) {
  echo json_encode(['success'=>false,'message'=>'All fields are required (and must be valid).']); exit;
}

try {
  
  $stmt = $pdo->prepare("SELECT id FROM users WHERE userID=? AND role='GENERAL_MANAGER' AND status='ACTIVE'");
  $stmt->execute([$gmUserId]);
  $gm = $stmt->fetch();
  if (!$gm) { echo json_encode(['success'=>false,'message'=>'General Manager not found or inactive.']); exit; }
  $gmId = (int)$gm['id'];

  
  $chk = $pdo->prepare("SELECT id FROM purchase_orders WHERE order_code=?");
  $chk->execute([$orderCode]);
  if ($chk->fetch()) { echo json_encode(['success'=>false,'message'=>"Order Code '$orderCode' already exists."]); exit; }

  
  $total = $qty * $unitPrice;
  $budQ = $pdo->prepare("SELECT annualBudget FROM gm_profiles WHERE user_ref=?");
  $budQ->execute([$gmId]);
  $row = $budQ->fetch();
  if ($row) {
    $annualBudget = (float)$row['annualBudget'];
    
    $sumQ = $pdo->prepare("SELECT COALESCE(SUM(total_amount),0) AS used FROM purchase_orders
                           WHERE ordered_by_user_id=? AND YEAR(created_at)=YEAR(CURDATE())");
    $sumQ->execute([$gmId]);
    $used = (float)$sumQ->fetch()['used'];
    if ($used + $total > $annualBudget) {
      echo json_encode(['success'=>false,'message'=>'Budget exceeded for this year.']); exit;
    }
  }

  
  $ins = $pdo->prepare("
    INSERT INTO purchase_orders (order_code, supplier_id, item_name, qty, unit_price, total_amount, status, ordered_by_user_id)
    VALUES (?, ?, ?, ?, ?, ?, 'PLACED', ?)
  ");
  $ins->execute([$orderCode, $supplierId, $itemName, $qty, $unitPrice, $total, $gmId]);

  echo json_encode(['success'=>true,'message'=>"Order '$orderCode' placed successfully. Total LKR ".number_format($total,2)]);
} catch (Throwable $e) {
  echo json_encode(['success'=>false,'message'=>'Error: '.$e->getMessage()]);
}
