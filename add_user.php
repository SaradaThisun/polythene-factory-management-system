<?php

header('Content-Type: application/json');
require __DIR__ . '/db.php';

$userID   = trim($_POST['userID']   ?? '');
$userName = trim($_POST['userName'] ?? '');
$nic      = trim($_POST['nic']      ?? '');
$email    = trim($_POST['email']    ?? '');
$phone    = trim($_POST['phone']    ?? '');
$role     = trim($_POST['role']     ?? 'EMPLOYEE');
$password = $_POST['password']      ?? '';

if ($userID === '' || $userName === '' || $nic === '' || $email === '' || $phone === '' || $password === '') {
  echo json_encode(['success' => false, 'message' => 'All fields are required.']);
  exit;
}

try {
  
  $chk = $pdo->prepare("SELECT id FROM users WHERE userID = ?");
  $chk->execute([$userID]);
  if ($chk->fetch()) {
    echo json_encode(['success' => false, 'message' => "UserID '$userID' already exists."]);
    exit;
  }

  $ins = $pdo->prepare("
    INSERT INTO users (userID, userName, password, NIC, email, phone, role)
    VALUES (?, ?, ?, ?, ?, ?, ?)
  ");
  $ins->execute([$userID, $userName, $password, $nic, $email, $phone, $role]);

  echo json_encode([
    'success' => true,
    'message' => "User '$userName' (ID: $userID) added successfully with role $role."
  ]);
} catch (Throwable $e) {
  echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
