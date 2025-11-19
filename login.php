<?php

header('Content-Type: application/json');
session_start();
require __DIR__ . '/db.php';

$userID = trim($_POST['userID'] ?? '');
$pass   = $_POST['password'] ?? '';

if ($userID === '' || $pass === '') {
  echo json_encode(['success'=>false,'message'=>'User ID and Password are required.']);
  exit;
}

try {
  $stmt = $pdo->prepare("SELECT id, userID, userName, role, status, password FROM users WHERE userID = ? LIMIT 1");
  $stmt->execute([$userID]);
  $u = $stmt->fetch();

  if (!$u)            { echo json_encode(['success'=>false,'message'=>'User not found.']); exit; }
  if ($u['status']!=='ACTIVE') { echo json_encode(['success'=>false,'message'=>'User is inactive.']); exit; }
  if ($pass !== $u['password']){ echo json_encode(['success'=>false,'message'=>'Invalid password.']); exit; }

  $_SESSION['user'] = [
    'id' => (int)$u['id'],
    'userID' => $u['userID'],
    'userName' => $u['userName'],
    'role' => $u['role']
  ];

  
  $redirect = './employee_dashboard.html';
  switch ($u['role']) {
    case 'MANAGER':
      $redirect = './manager_dashboard.html';
      break;
    case 'GENERAL_MANAGER':
      $redirect = './gm_dashboard.html';
      break;
    case 'SUPERVISOR':
    
      $redirect = './employee_dashboard.html';
      break;
    case 'ADMIN':
      
      $redirect = './users_list.html';
      break;
  
  }

  echo json_encode(['success'=>true, 'redirect'=>$redirect]);
} catch (Throwable $e) {
  echo json_encode(['success'=>false,'message'=>'Error: '.$e->getMessage()]);
}
