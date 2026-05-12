<?php
require_once 'condb.php';
session_start();
if (empty($_SESSION['user_id'])) { header('Location: login.php'); exit; }

$user_id = (int)$_SESSION['user_id'];

$old = $_POST['old_password'] ?? '';
$new = $_POST['new_password'] ?? '';
$cfm = $_POST['confirm_password'] ?? '';

if ($old === '' || $new === '' || $cfm === '') {
  $_SESSION['flash_err'] = 'กรอกข้อมูลให้ครบ';
  header('Location: index.php'); exit;
}
if ($new !== $cfm) {
  $_SESSION['flash_err'] = 'รหัสผ่านใหม่ไม่ตรงกัน';
  header('Location: index.php'); exit;
}

// ดึงรหัสปัจจุบันมาเช็ค
$stmt = $conn->prepare("SELECT password FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$hash = ($stmt->get_result()->fetch_assoc())['password'] ?? '';

if (!$hash || !password_verify($old, $hash)) {
  $_SESSION['flash_err'] = 'รหัสผ่านเดิมไม่ถูกต้อง';
  header('Location: index.php'); exit;
}

// อัปเดตรหัสผ่านใหม่
$new_hash = password_hash($new, PASSWORD_DEFAULT);
$upd = $conn->prepare("UPDATE users SET password=? WHERE id=?");
$upd->bind_param("si", $new_hash, $user_id);
$upd->execute();

$_SESSION['flash_ok'] = 'เปลี่ยนรหัสผ่านสำเร็จ';
header('Location: index.php'); exit;
