<?php
header('Content-Type: application/json');
require __DIR__ . '/db.php';

$q = trim($_POST['q'] ?? '');
$status = trim($_POST['status'] ?? '');

try {
  $sql = "SELECT po.id, po.order_code, po.supplier_id, po.item_name, po.qty, po.unit_price, po.total_amount, po.status,
                 DATE_FORMAT(po.created_at,'%Y-%m-%d %H:%i') AS created_at,
                 s.company_name
          FROM purchase_orders po
          LEFT JOIN suppliers s ON s.id=po.supplier_id";
  $where = [];
  $args  = [];
  if ($q !== '') {
    $where[] = "(po.order_code LIKE ? OR po.item_name LIKE ? OR s.company_name LIKE ?)";
    $like = "%$q%";
    array_push($args, $like, $like, $like);
  }
  if ($status !== '') { $where[] = "po.status = ?"; $args[] = $status; }
  if ($where) $sql .= " WHERE ".implode(" AND ", $where);
  $sql .= " ORDER BY po.created_at DESC";

  $stmt = $pdo->prepare($sql);
  $stmt->execute($args);
  echo json_encode(['success'=>true,'rows'=>$stmt->fetchAll()]);
} catch (Throwable $e) {
  echo json_encode(['success'=>false,'message'=>'Error: '.$e->getMessage()]);
}
