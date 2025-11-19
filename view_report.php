<?php

header('Content-Type: application/json');
require __DIR__ . '/db.php';

$type = trim($_POST['reportType'] ?? 'production');

try {
  if ($type === 'production') {
  
    $q1 = $pdo->query("SELECT COALESCE(SUM(quantity_kg),0) AS weekly_kg
                       FROM production_log
                       WHERE logged_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)");
    $weekly = (float)($q1->fetch()['weekly_kg'] ?? 0);

    
    $q2 = $pdo->query("SELECT quantity_kg, DATE_FORMAT(logged_at, '%Y-%m-%d %H:%i') AS logged_at
                       FROM production_log
                       ORDER BY logged_at DESC
                       LIMIT 5");
    $latest = $q2->fetchAll();

    echo json_encode([
      'success' => true,
      'reportType' => 'production',
      'title' => 'Production Report',
      'message' => 'Weekly production summary and latest entries.',
      'weeklyProductionKg' => $weekly,
      'latest' => $latest
    ]);
    exit;
  }

  if ($type === 'inventory') {
    
    $q1 = $pdo->query("SELECT COALESCE(SUM(qty),0) AS total_qty FROM inventory_stock");
    $total = (int)($q1->fetch()['total_qty'] ?? 0);

    
    $q2 = $pdo->query("SELECT item_name, qty
                       FROM inventory_stock
                       ORDER BY qty DESC
                       LIMIT 5");
    $items = $q2->fetchAll();

    echo json_encode([
      'success' => true,
      'reportType' => 'inventory',
      'title' => 'Inventory Report',
      'message' => 'Total stock and top items.',
      'totalStock' => $total,
      'items' => $items
    ]);
    exit;
  }

  if ($type === 'finance') {
    
    $q1 = $pdo->query("SELECT
                         COALESCE(SUM(CASE WHEN txn_type='INCOME' THEN amount ELSE -amount END),0) AS net_last_7d
                       FROM finance_txn
                       WHERE occurred_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)");
    $net = (float)($q1->fetch()['net_last_7d'] ?? 0);

    
    $q2 = $pdo->query("SELECT txn_type, amount, DATE_FORMAT(occurred_at, '%Y-%m-%d %H:%i') AS occurred_at
                       FROM finance_txn
                       ORDER BY occurred_at DESC
                       LIMIT 5");
    $recent = $q2->fetchAll();

    echo json_encode([
      'success' => true,
      'reportType' => 'finance',
      'title' => 'Finance Report',
      'message' => 'Net of last 7 days and recent transactions.',
      'netLast7d' => $net,
      'recent' => $recent
    ]);
    exit;
  }

  echo json_encode(['success' => false, 'message' => 'Unknown report type.']);
} catch (Throwable $e) {
  echo json_encode(['success' => false, 'message' => 'Error: '.$e->getMessage()]);
}
