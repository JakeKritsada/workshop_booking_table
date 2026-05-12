<?php
require_once 'condb.php'; // เรียกไฟล์เชื่อมฐานข้อมูล เพื่อให้ใช้ตัวแปร $conn ได้
session_start(); // เริ่มต้น session สำหรับเก็บข้อความแจ้งเตือนหรือสถานะ

// ดึงข้อความจาก session (flash message)
$err = $_SESSION['flash_err'] ?? ''; // ถ้ามีข้อความ error เดิมเก็บไว้
$ok  = $_SESSION['flash_ok']  ?? ''; // ถ้ามีข้อความ success เดิมเก็บไว้
unset($_SESSION['flash_err'], $_SESSION['flash_ok']); // ล้างข้อความหลังดึงมาแล้ว

// ✅ ตรวจสอบว่าผู้ใช้กดปุ่ม Submit หรือไม่ (มีการส่งข้อมูลแบบ POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $phone_in = trim($_POST['phone'] ?? ''); // รับเบอร์โทรจากฟอร์ม
  $password = $_POST['password'] ?? '';    // รับรหัสผ่าน
  $confirm  = $_POST['confirm'] ?? '';     // รับรหัสยืนยันอีกครั้ง

  // ✅ ทำความสะอาดเบอร์โทร เอาเฉพาะตัวเลข 0–9
  $phone = preg_replace('/[^0-9]/', '', $phone_in);

  // ✅ ตรวจสอบว่ากรอกครบทุกช่องหรือไม่
  if ($phone === '' || $password === '' || $confirm === '') {
    $_SESSION['flash_err'] = 'กรอกข้อมูลให้ครบ'; 
    header('Location: register.php'); exit; // ถ้าไม่ครบ redirect กลับไปหน้าเดิม
  }

  // ✅ ตรวจสอบว่ารหัสผ่านตรงกันไหม
  if ($password !== $confirm) {
    $_SESSION['flash_err'] = 'รหัสผ่านและยืนยันรหัสไม่ตรงกัน';
    header('Location: register.php'); exit;
  }

  // ✅ ตรวจสอบเบอร์โทรซ้ำในระบบ (ห้ามสมัครซ้ำ)
  $sql = "SELECT id FROM users WHERE phone = ?";
  $stmt = $conn->prepare($sql);         // เตรียมคำสั่ง SQL แบบปลอดภัย
  $stmt->bind_param("s", $phone);       // ผูกค่าเบอร์โทรเข้ากับ ?
  $stmt->execute();                     // รันคำสั่ง
  $rs = $stmt->get_result();            // ดึงผลลัพธ์
  if ($rs->fetch_assoc()) {             // ถ้ามีแถวข้อมูล แปลว่าเบอร์นี้เคยสมัครแล้ว
    $_SESSION['flash_err'] = 'เบอร์นี้มีในระบบแล้ว';
    header('Location: register.php'); exit;
  }

  // ✅ ถ้าเบอร์ยังไม่ซ้ำ → ทำการเข้ารหัสรหัสผ่านก่อนบันทึก
  $hash = password_hash($password, PASSWORD_DEFAULT); 
  // password_hash() จะสร้างรหัสแบบ Bcrypt ปลอดภัย

  // ✅ เพิ่มข้อมูลลงตาราง users (เบอร์โทร, รหัส, role=user)
  $ins  = $conn->prepare("INSERT INTO users (phone, password, role) VALUES (?,?, 'user')");
  $ins->bind_param("ss", $phone, $hash);
  $ins->execute();

  // ✅ บันทึกสำเร็จ — แจ้งข้อความสำเร็จใน session
  $_SESSION['flash_ok'] = 'สมัครสมาชิกสำเร็จ! เข้าสู่ระบบได้เลย';
  header('Location: register.php'); exit; // กลับมาหน้าเดิมเพื่อโชว์ข้อความ
}
?>

<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>สมัครสมาชิก</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>body{background:#111;color:#fff}</style>
</head>
<body class="d-flex align-items-center" style="min-height:100vh;">
  <div class="container" style="max-width:460px;">
    <h3 class="mb-3">สมัครสมาชิก</h3>
    <?php if (!empty($err)): ?>
      <div class="alert alert-danger"><?=htmlspecialchars($err)?></div>
    <?php endif; ?>
    <?php if (!empty($ok)): ?>
      <div class="alert alert-success"><?=htmlspecialchars($ok)?></div>
    <?php endif; ?>
    <form method="post" action="register.php" autocomplete="off">
      <div class="mb-3">
        <label class="form-label">เบอร์โทรศัพท์</label>
        <input type="tel" name="phone" class="form-control" placeholder="เช่น 0642064899" required maxlength="10" pattern="[0-9]{10}">
        <div class="form-text text-secondary">ใส่เฉพาะตัวเลข 10 หลัก</div>
      </div>
      <div class="mb-3">
        <label class="form-label">รหัสผ่าน</label>
        <input type="password" name="password" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">ยืนยันรหัสผ่าน</label>
        <input type="password" name="confirm" class="form-control" required>
      </div>
      <button class="btn btn-success w-100" type="submit" style="font-weight:600;">สมัครสมาชิก</button>
      <div class="mt-3">
        มีบัญชีแล้ว? <a href="login.php">เข้าสู่ระบบ</a>
      </div>
    </form>
  </div>
</body>
</html>
