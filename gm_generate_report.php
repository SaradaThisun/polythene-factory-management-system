<?php

header('Content-Type: application/json');
require __DIR__ . '/db.php';

$type = trim($_POST['reportType'] ?? 'production');
$from = trim($_POST['fromDate'] ?? '');
$to   = trim($_POST['toDate'] ?? '');



try {
  if ($type === 'production') {
    
    $where = '';
    $args  = [];
    if ($from !== '') { $where .= ($where?' AND ':'WHERE ')."logged_at >= ?"; $args[] = "$from 00:00:00"; }
    if ($to   !== '') { $where .= ($where?' AND ':'WHERE ')."logged_at <= ?"; $args[] = "$to 23:59:59"; }
    if ($where === '') { 
      $where = "WHERE logged_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
    }

    $stmt = $pdo->prepare("SELECT COALESCE(SUM(quantity_kg),0) AS totalKg FROM production_log $where");
    $stmt->execute($args);
    $totalKg = (float)($stmt->fetch()['totalKg'] ?? 0);

    $stmt2 = $pdo->prepare("SELECT quantity_kg, DATE_FORMAT(logged_at, '%Y-%m-%d %H:%i') AS logged_at
                            FROM production_log $where
                            ORDER BY logged_at DESC LIMIT 10");
    $stmt2->execute($args);
    $rows = $stmt2->fetchAll();

    echo json_encode([
      'success' => true,
      'reportType' => 'production',
      'title' => 'Production Report',
      'message' => 'Summary within selected date range (or last 7 days by default).',
      'totalKg' => $totalKg,
      'rows' => $rows
    ]);
    exit;
  }

  if ($type === 'inventory') {
    
    $q1 = $pdo->query("SELECT COALESCE(SUM(qty),0) AS total FROM inventory_stock");
    $total = (int)($q1->fetch()['total'] ?? 0);

    $q2 = $pdo->query("SELECT item_name, qty FROM inventory_stock ORDER BY qty DESC LIMIT 10");
    $items = $q2->fetchAll();

    echo json_encode([
      'success' => true,
      'reportType' => 'inventory',
      'title' => 'Inventory Report',
      'message' => 'Current stock overview.',
      'totalStock' => $total,
      'items' => $items
    ]);
    exit;
  }

  if ($type === 'finance') {
    
    $where = '';
    $args  = [];
    if ($from !== '') { $where .= ($where?' AND ':'WHERE ')."occurred_at >= ?"; $args[] = "$from 00:00:00"; }
    if ($to   !== '') { $where .= ($where?' AND ':'WHERE ')."occurred_at <= ?"; $args[] = "$to 23:59:59"; }
    if ($where === '') { $where = "WHERE occurred_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)"; }

    $stmt = $pdo->prepare("
      SELECT COALESCE(SUM(CASE WHEN txn_type='INCOME' THEN amount ELSE -amount END),0) AS net
      FROM finance_txn $where
    ");
    $stmt->execute($args);
    $net = (float)($stmt->fetch()['net'] ?? 0);

    $stmt2 = $pdo->prepare("
      SELECT txn_type, amount, DATE_FORMAT(occurred_at, '%Y-%m-%d %H:%i') AS occurred_at
      FROM finance_txn $where
      ORDER BY occurred_at DESC LIMIT 10
    ");
    $stmt2->execute($args);
    $recent = $stmt2->fetchAll();

    echo json_encode([
      'success' => true,
      'reportType' => 'finance',
      'title' => 'Finance Report',
      'message' => 'Net amount within selected date range (or last 7 days by default).',
      'netAmount' => $net,
      'recent' => $recent
    ]);
    exit;
  }

  echo json_encode(['success' => false, 'message' => 'Unknown report type.']);
} catch (Throwable $e) {
  echo json_encode(['success' => false, 'message' => 'Error: '.$e->getMessage()]);
}
