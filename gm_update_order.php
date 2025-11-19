<?php
header('Content-Type: application/json');
require __DIR__ . '/db.php';

$id          = (int)($_POST['id'] ?? 0);
$order_code  = trim($_POST['order_code'] ?? '');
$supplier_id = (int)($_POST['supplier_id'] ?? 0);
$item_name   = trim($_POST['item_name'] ?? '');
$qty         = (float)($_POST['qty'] ?? 0);
$unit_price  = (float)($_POST['unit_price'] ?? 0);
$status      = trim($_POST['status'] ?? 'PLACED');

if ($id<=0 || $order_code==='' || $supplier_id<=0 || $item_name==='' || $qty<=0 || $unit_price<0) {
  echo json_encode(['success'=>false,'message'=>'All fields are required and must be valid.']); exit;
}

try {
  
  $chk = $pdo->prepare("SELECT id FROM purchase_orders WHERE order_code=? AND id<>?");
  $chk->execute([$order_code, $id]);
  if ($chk->fetch()) { echo json_encode(['success'=>false,'message'=>"Order code '$order_code' already exists."]); exit; }

  $total = $qty * $unit_price;

  $upd = $pdo->prepare("
    UPDATE purchase_orders
    SET order_code=?, supplier_id=?, item_name=?, qty=?, unit_price=?, total_amount=?, status=?
    WHERE id=?
  ");
  $upd->execute([$order_code,$supplier_id,$item_name,$qty,$unit_price,$total,$status,$id]);

  echo json_encode(['success'=>true,'message'=>'Order updated successfully.']);
} catch (Throwable $e) {
  echo json_encode(['success'=>false,'message'=>'Error: '.$e->getMessage()]);
}
