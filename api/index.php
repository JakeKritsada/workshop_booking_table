<?php
session_start();

require_once 'condb.php';

/* SESSION */
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$ok  = $_SESSION['flash_ok']  ?? '';
$err = $_SESSION['flash_err'] ?? '';

unset($_SESSION['flash_ok'], $_SESSION['flash_err']);

$user_id = $_SESSION['user_id'] ?? 0;
$phone   = $_SESSION['phone'] ?? '';
$role    = $_SESSION['role'] ?? 'user';

/* ---------- HANDLE POST ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_SESSION['last_try']) && time() - $_SESSION['last_try'] < 2) {
    $_SESSION['flash_err'] = 'ลองใหม่อีกครั้งในไม่กี่วินาที';
    header('Location: index.php');
    exit;
  }

  $_SESSION['last_try'] = time();
  $csrf = $_POST['csrf_token'] ?? '';

  if (!hash_equals($_SESSION['csrf_token'], $csrf)) {
    $_SESSION['flash_err'] = 'เซสชันหมดอายุ';
    header('Location: index.php');
    exit;
  }

  $action = $_POST['action'] ?? '';

  if ($action === 'change_password') {
    $current = $_POST['cp_current'] ?? '';
    $new1    = $_POST['cp_new'] ?? '';
    $new2    = $_POST['cp_new2'] ?? '';

    if ($current === '' || $new1 === '' || $new2 === '') {
      $_SESSION['flash_err'] = 'กรอกข้อมูลให้ครบ';
      header('Location: index.php');
      exit;
    }

    if ($new1 !== $new2) {
      $_SESSION['flash_err'] = 'รหัสผ่านใหม่ไม่ตรงกัน';
      header('Location: index.php');
      exit;
    }

    if (strlen($new1) < 4) {
      $_SESSION['flash_err'] = 'รหัสผ่านขั้นต่ำ 4 ตัว';
      header('Location: index.php');
      exit;
    }

    $stmt = $conn->prepare("SELECT password FROM users WHERE id=? LIMIT 1");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $user = $res->fetch_assoc();
    $stmt->close();

    if (!$user || !password_verify($current, $user['password'])) {
      $_SESSION['flash_err'] = 'รหัสผ่านเดิมไม่ถูกต้อง';
      header('Location: index.php');
      exit;
    }

    $hash = password_hash($new1, PASSWORD_BCRYPT);
    $stmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
    $stmt->bind_param("si", $hash, $user_id);

    if ($stmt->execute()) {
      $_SESSION['flash_ok'] = 'เปลี่ยนรหัสผ่านเรียบร้อย';
    } else {
      $_SESSION['flash_err'] = 'เกิดข้อผิดพลาด';
    }

    $stmt->close();
    header('Location: index.php');
    exit;
  }
}
?>

<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Deep D House - Prototype</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <style>
    body {
      display: flex;
      flex-direction: column;
      min-height: 100vh;
      margin: 0;
      background-color: #0e0e0e;
      color: #fff;
    }
    .main-content { flex: 1; }
    footer {
      background: #000;
      color: #555;
      padding: 25px 0;
      text-align: center;
      border-top: 1px solid rgba(245, 165, 36, 0.1);
    }
    .hero {
      background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&q=80&w=1000');
      background-size: cover;
      background-position: center;
      border-radius: 20px;
      margin-top: 2rem;
    }
    .prototype-badge {
      background-color: #f5a524;
      color: #000;
      padding: 5px 15px;
      border-radius: 50px;
      font-size: 0.9rem;
      font-weight: bold;
      display: inline-block;
      margin-bottom: 1rem;
    }
  </style>
</head>

<body>
  <?php include 'navbar.php'; ?>

  <main class="main-content">
    <div class="container py-3" style="max-width:980px;">
      <?php if (!empty($ok)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($ok) ?></div>
      <?php endif; ?>

      <?php if (!empty($err)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($err) ?></div>
      <?php endif; ?>
    </div>

    <header class="container">
      <div class="hero text-center py-5 px-3">
        <div class="prototype-badge text-uppercase">UI/UX Prototype</div>
        <h1 class="display-4 fw-bold mb-3">ระบบจองโต๊ะของร้านดิบดี</h1>
        <p class="lead mb-4 mx-auto" style="max-width: 700px; color: #ccc;">
          เว็บไซต์นี้จัดทำขึ้นเพื่อ **ยกตัวอย่างและรูปแบบการใช้งาน (UI/UX Prototype)** เท่านั้น<br>
          <span class="small text-warning">* ข้อมูลการทำงานจำลองขึ้นเพื่อแสดงผลในส่วนของโครงสร้างระบบ</span>
        </p>

        <div class="d-grid gap-3 d-sm-flex justify-content-sm-center">
          <a class="btn btn-warning btn-lg px-4 fw-bold" href="detailtable.php">ดูรายละเอียดโต๊ะ</a>
          <a class="btn btn-outline-light btn-lg px-4" href="index2.php">จองโต๊ะ (Demo)</a>
        </div>
      </div>
    </header>
  </main>

  <footer>
    © 2025 Deep D House · ระบบจำลองการจองโต๊ะออนไลน์ (Prototype)
  </footer>

  <div class="modal fade" id="changeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <form class="modal-content bg-dark text-white" method="post" action="index.php">
        <div class="modal-header border-secondary">
          <h5 class="modal-title">เปลี่ยนรหัสผ่าน</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
          <input type="hidden" name="action" value="change_password">
          <div class="mb-3">
            <label class="form-label">รหัสผ่านเดิม</label>
            <input type="password" name="cp_current" class="form-control bg-secondary text-white border-0" required>
          </div>
          <div class="mb-3">
            <label class="form-label">รหัสผ่านใหม่</label>
            <input type="password" name="cp_new" class="form-control bg-secondary text-white border-0" required>
          </div>
          <div class="mb-3">
            <label class="form-label">ยืนยันรหัสผ่านใหม่</label>
            <input type="password" name="cp_new2" class="form-control bg-secondary text-white border-0" required>
          </div>
        </div>
        <div class="modal-footer border-secondary">
          <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">ยกเลิก</button>
          <button type="submit" class="btn btn-warning fw-bold">บันทึก</button>
        </div>
      </form>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>