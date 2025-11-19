<?php
header('Content-Type: application/json');
require __DIR__ . '/db.php';

$id       = (int)($_POST['id'] ?? 0);
$userID   = trim($_POST['userID']   ?? '');
$userName = trim($_POST['userName'] ?? '');
$nic      = trim($_POST['nic']      ?? '');
$email    = trim($_POST['email']    ?? '');
$phone    = trim($_POST['phone']    ?? '');
$role     = trim($_POST['role']     ?? 'EMPLOYEE');
$status   = trim($_POST['status']   ?? 'ACTIVE');

if ($id<=0 || $userID==='' || $userName==='' || $nic==='' || $email==='' || $phone==='') {
  echo json_encode(['success'=>false,'message'=>'All fields are required.']); exit;
}

try {
  
  $chk = $pdo->prepare("SELECT id FROM users WHERE userID=? AND id<>?");
  $chk->execute([$userID,$id]);
  if ($chk->fetch()) {
    echo json_encode(['success'=>false,'message'=>"UserID '$userID' already exists."]); exit;
  }

  $upd = $pdo->prepare("
    UPDATE users
    SET userID=?, userName=?, NIC=?, email=?, phone=?, role=?, status=?
    WHERE id=?
  ");
  $upd->execute([$userID,$userName,$nic,$email,$phone,$role,$status,$id]);

  echo json_encode(['success'=>true,'message'=>'User updated successfully.']);
} catch (Throwable $e) {
  echo json_encode(['success'=>false,'message'=>'Error: '.$e->getMessage()]);
}
