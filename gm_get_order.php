<?php
header('Content-Type: application/json');
require __DIR__ . '/db.php';

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) { echo json_encode(['success'=>false,'message'=>'Invalid id']); exit; }

try {
  $stmt = $pdo->prepare("SELECT id, order_code, supplier_id, item_name, qty, unit_price, total_amount, status FROM purchase_orders WHERE id=?");
  $stmt->execute([$id]);
  $row = $stmt->fetch();
  if (!$row) { echo json_encode(['success'=>false,'message'=>'Order not found']); exit; }
  echo json_encode(['success'=>true,'row'=>$row]);
} catch (Throwable $e) {
  echo json_encode(['success'=>false,'message'=>'Error: '.$e->getMessage()]);
}
    