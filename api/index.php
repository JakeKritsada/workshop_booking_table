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

  <title>Deep D House</title>

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- CSS -->
  <link rel="stylesheet" href="style.css">

</head>

<body>

  <!-- NAVBAR -->
  <?php include 'navbar.php'; ?>

  <!-- MAIN -->
  <main class="flex-grow-1">

    <div class="container py-3" style="max-width:980px;">

      <?php if (!empty($ok)): ?>
        <div class="alert alert-success">
          <?= htmlspecialchars($ok) ?>
        </div>
      <?php endif; ?>

      <?php if (!empty($err)): ?>
        <div class="alert alert-danger">
          <?= htmlspecialchars($err) ?>
        </div>
      <?php endif; ?>

    </div>

    <!-- HERO -->
    <header class="hero text-center text-white">

      <div class="container py-5">

        <h1 class="display-4 fw-bold mb-2">
          ยินดีต้อนรับสู่ Deep D House
        </h1>

        <p class="lead mb-4">
          ร้านอาหารที่พร้อมเสิร์ฟความอร่อย
        </p>

        <a class="btn btn-primary btn-lg px-4"
           href="detailtable.php">

          ดูรายละเอียดโต๊ะ

        </a>

        <a class="btn btn-primary btn-lg px-4"
           href="index2.php">

          จองโต๊ะ

        </a>

      </div>

    </header>

  </main>

  <!-- FOOTER -->
  <footer>
© 2025 Deep D House · ระบบจองโต๊ะอาหารออนไลน์  </footer>

  <!-- MODAL -->
  <div class="modal fade" id="changeModal" tabindex="-1">

    <div class="modal-dialog modal-dialog-centered">

      <form class="modal-content bg-dark text-white"
            method="post"
            action="index.php">

        <div class="modal-header">

          <h5 class="modal-title">
            เปลี่ยนรหัสผ่าน
          </h5>

          <button type="button"
                  class="btn-close btn-close-white"
                  data-bs-dismiss="modal">
          </button>

        </div>

        <div class="modal-body">

          <input type="hidden"
                 name="csrf_token"
                 value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

          <input type="hidden"
                 name="action"
                 value="change_password">

          <div class="mb-3">

            <label class="form-label">
              รหัสผ่านเดิม
            </label>

            <input type="password"
                   name="cp_current"
                   class="form-control"
                   required>

          </div>

          <div class="mb-3">

            <label class="form-label">
              รหัสผ่านใหม่
            </label>

            <input type="password"
                   name="cp_new"
                   class="form-control"
                   required>

          </div>

          <div class="mb-3">

            <label class="form-label">
              ยืนยันรหัสผ่านใหม่
            </label>

            <input type="password"
                   name="cp_new2"
                   class="form-control"
                   required>

          </div>

        </div>

        <div class="modal-footer">

          <button type="button"
                  class="btn btn-outline-light"
                  data-bs-dismiss="modal">

            ยกเลิก

          </button>

          <button type="submit"
                  class="btn btn-warning">

            บันทึก

          </button>

        </div>

      </form>

    </div>

  </div>

  <!-- JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<style>
    /* 🚩 บทเรียนเรื่อง Sticky Footer */
    body {
      display: flex;
      flex-direction: column;
      min-height: 100vh; /* บังคับให้สูงเท่าหน้าจอเสมอ */
      margin: 0;
      background-color: #0e0e0e; /* สีพื้นหลังธีมมืด */
    }

    .main-content {
      flex: 1; /* กล่องนี้จะยืดพื้นที่เพื่อดัน footer ลงข้างล่าง */
    }

    .card {
      background: #151515 !important;
      border: 1px solid rgba(245, 165, 36, 0.1) !important;
      border-radius: 20px;
    }

    footer {
      background: #000;
      color: #555;
      padding: 25px 0;
      text-align: center;
      border-top: 1px solid rgba(245, 165, 36, 0.1);
    }
  </style>
</head>
<body>