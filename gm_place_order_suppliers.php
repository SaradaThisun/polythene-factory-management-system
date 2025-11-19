<?php
header('Content-Type: application/json');
require __DIR__ . '/db.php';

try {
  $stmt = $pdo->query("SELECT id, company_name FROM suppliers ORDER BY company_name");
  echo json_encode(['success'=>true,'rows'=>$stmt->fetchAll()]);
} catch (Throwable $e) {
  echo json_encode(['success'=>false,'message'=>'Error: '.$e->getMessage(),'rows'=>[]]);
}
