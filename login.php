<?php
// ---- ตั้งค่าความปลอดภัยของ session cookie ----
session_set_cookie_params([ // ตั้งค่าการเก็บ cookie ของ session ให้ปลอดภัยขึ้น
  'lifetime' => 0, // หมายถึง cookie จะหมดอายุเมื่อปิดเบราว์เซอร์
  'path'     => '/', // ใช้ได้ทุกหน้าในเว็บไซต์
  'httponly' => true, // ป้องกันไม่ให้ JavaScript เข้าถึง cookie ได้ (ลดความเสี่ยง XSS)
  'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on', // ใช้เฉพาะตอนเว็บเป็น HTTPS เท่านั้น
  'samesite' => 'Lax', // ป้องกันการส่ง cookie ข้ามเว็บไซต์ (ลด CSRF)
]);

require_once 'condb.php'; // เรียกไฟล์เชื่อมต่อฐานข้อมูล
session_start(); // เริ่มต้น session

/* ถ้า login แล้วให้กลับหน้าแรก */
if (!empty($_SESSION['user_id'])) { // ถ้ามี user_id อยู่ใน session แสดงว่าล็อกอินแล้ว
  header('Location: index.php'); // ส่งผู้ใช้กลับหน้าแรก
  exit; // หยุดการทำงานของสคริปต์
}

/* flash message */
$err = $_SESSION['flash_err'] ?? ''; // เก็บข้อความ error ชั่วคราวจาก session (ถ้ามี)
$ok  = $_SESSION['flash_ok']  ?? ''; // เก็บข้อความ success ชั่วคราวจาก session (ถ้ามี)
unset($_SESSION['flash_err'], $_SESSION['flash_ok']); // ล้างค่า flash ออกเพื่อไม่ให้ซ้ำ

/* token ป้องกัน CSRF */
if (empty($_SESSION['csrf_token'])) { // ถ้ายังไม่มี token ใน session
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // สร้าง token แบบสุ่มใหม่ (ป้องกันการปลอมฟอร์ม)
}

// ฟังก์ชันช่วยล้างเบอร์โทร (เอาเฉพาะตัวเลข)
function clean_phone($s) {
  return preg_replace('/[^0-9]/', '', trim($s ?? '')); // ลบอักขระที่ไม่ใช่ตัวเลขออกจากเบอร์โทร
}

/* ---------- เมื่อส่งฟอร์ม ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') { // ตรวจสอบว่ามีการส่งฟอร์มด้วย POST หรือไม่
  if (isset($_SESSION['last_try']) && time() - $_SESSION['last_try'] < 2) { // ป้องกันกดซ้ำในเวลาใกล้กัน (ภายใน 2 วินาที)
    $_SESSION['flash_err'] = 'กรุณารอสักครู่ก่อนลองใหม่'; // ตั้งข้อความ error
    header('Location: login.php'); // กลับไปหน้า login
    exit;
  }
  $_SESSION['last_try'] = time(); // บันทึกเวลาล่าสุดที่กด submit

  // ตรวจสอบ token
  $csrf = $_POST['csrf_token'] ?? ''; // รับ token ที่ส่งมาจากฟอร์ม
  if (!hash_equals($_SESSION['csrf_token'], $csrf)) { // ถ้า token ไม่ตรงกับใน session
    $_SESSION['flash_err'] = 'หมดเวลาการใช้งาน กรุณารีเฟรชหน้านี้'; // แจ้งเตือนหมดอายุ
    header('Location: login.php');
    exit;
  }

  $action = $_POST['action'] ?? ''; // เก็บค่าการกระทำ เช่น login, change_password, forgot_password

  /* ===== LOGIN ===== */
  if ($action === 'login') { // ถ้าผู้ใช้ส่งฟอร์มเข้าสู่ระบบ
    $phone_in = $_POST['phone'] ?? ''; // รับเบอร์โทรที่ผู้ใช้กรอก
    $password = $_POST['password'] ?? ''; // รับรหัสผ่านที่กรอก
    $phone = clean_phone($phone_in); // ทำความสะอาดเบอร์โทร (เอาเฉพาะตัวเลข)
    $_SESSION['last_phone'] = $phone_in; // เก็บเบอร์ไว้แสดงในฟอร์มตอน reload

    if ($phone === '' || $password === '') { // ถ้าไม่ได้กรอกครบ
      $_SESSION['flash_err'] = 'กรอกข้อมูลให้ครบ';
      header('Location: login.php'); exit;
    }

    // 🔹 ใช้ prepared statement ป้องกัน SQL Injection
    $stmt = $conn->prepare("SELECT id, phone, password, role FROM users WHERE phone=? LIMIT 1"); // เตรียมคำสั่ง SQL
    $stmt->bind_param("s", $phone); // ใส่ค่าตัวแปร $phone ลงในเครื่องหมาย ?
    $stmt->execute(); // รันคำสั่ง SQL
    $res  = $stmt->get_result(); // ดึงผลลัพธ์ออกมา
    $user = $res->fetch_assoc(); // ดึงข้อมูลผู้ใช้ 1 แถว
    $stmt->close(); // ปิด statement

    // ตรวจสอบรหัสผ่าน
    if ($user && password_verify($password, $user['password'])) { // ถ้ารหัสผ่านถูกต้อง
      session_regenerate_id(true); // ป้องกัน session hijacking (เปลี่ยน session id ใหม่)
      $_SESSION['user_id'] = (int)$user['id']; // เก็บ id ผู้ใช้ไว้ใน session
      $_SESSION['phone']   = $user['phone']; // เก็บเบอร์โทร
      $_SESSION['role']    = $user['role'] ?? 'user'; // เก็บ role เช่น admin หรือ user
      unset($_SESSION['csrf_token'], $_SESSION['last_phone']); // ลบข้อมูลไม่จำเป็น
      header('Location: index.php'); exit; // ไปหน้าแรก
    } else { // ถ้าข้อมูลไม่ตรง
      $_SESSION['flash_err'] = 'เบอร์โทรหรือรหัสผ่านไม่ถูกต้อง';
      header('Location: login.php'); exit;
    }
  }

  /* ===== CHANGE PASSWORD ===== */
  if ($action === 'change_password') { // ถ้าผู้ใช้ต้องการเปลี่ยนรหัสผ่าน
    $phone_in    = $_POST['cp_phone'] ?? ''; // เบอร์โทร
    $old_pwd     = $_POST['cp_current'] ?? ''; // รหัสเดิม
    $new_pwd     = $_POST['cp_new'] ?? ''; // รหัสใหม่
    $new_pwd2    = $_POST['cp_new2'] ?? ''; // ยืนยันรหัสใหม่
    $phone       = clean_phone($phone_in);
    $_SESSION['last_phone'] = $phone_in;

    // ตรวจสอบความครบถ้วน
    if ($phone===''||$old_pwd===''||$new_pwd===''||$new_pwd2==='') {
      $_SESSION['flash_err']='กรอกข้อมูลให้ครบ'; header('Location: login.php'); exit;
    }
    if ($new_pwd !== $new_pwd2) { // รหัสใหม่ไม่ตรงกัน
      $_SESSION['flash_err']='รหัสผ่านใหม่ไม่ตรงกัน'; header('Location: login.php'); exit;
    }
    if (strlen($new_pwd) < 4) { // รหัสใหม่สั้นเกินไป
      $_SESSION['flash_err']='รหัสผ่านใหม่อย่างน้อย 4 ตัว'; header('Location: login.php'); exit;
    }

    // ตรวจสอบว่ามีผู้ใช้จริงหรือไม่
    $stmt=$conn->prepare("SELECT id,password FROM users WHERE phone=? LIMIT 1");
    $stmt->bind_param("s",$phone);
    $stmt->execute();
    $res=$stmt->get_result();
    $user=$res->fetch_assoc();
    $stmt->close();

    if(!$user||!password_verify($old_pwd,$user['password'])){ // ถ้ารหัสเดิมไม่ตรง
      $_SESSION['flash_err']='เบอร์หรือรหัสผ่านเดิมไม่ถูกต้อง'; header('Location: login.php'); exit;
    }

    // ถ้าผ่านทั้งหมดให้เปลี่ยนรหัส
    $hash=password_hash($new_pwd,PASSWORD_BCRYPT); // เข้ารหัสรหัสผ่านใหม่
    $stmt=$conn->prepare("UPDATE users SET password=? WHERE id=?");
    $stmt->bind_param("si",$hash,$user['id']);
    if($stmt->execute()){ // ถ้าเปลี่ยนสำเร็จ
      $_SESSION['flash_ok']='เปลี่ยนรหัสผ่านสำเร็จ ลองเข้าสู่ระบบใหม่';
    }else{ // ถ้าเปลี่ยนไม่สำเร็จ
      $_SESSION['flash_err']='เกิดข้อผิดพลาด: '.$stmt->error;
    }
    $stmt->close();
    header('Location: login.php'); exit;
  }

  /* ===== FORGOT PASSWORD ===== */
  if ($action === 'forgot_password') { // ถ้าผู้ใช้ลืมรหัสผ่าน
    $phone_in = $_POST['fp_phone'] ?? ''; // เบอร์โทร
    $new_pwd  = $_POST['fp_new'] ?? ''; // รหัสใหม่
    $new_pwd2 = $_POST['fp_new2'] ?? ''; // ยืนยันรหัสใหม่
    $phone    = clean_phone($phone_in);
    $_SESSION['last_phone'] = $phone_in;

    // ตรวจสอบข้อมูลเบื้องต้น
    if ($phone===''||$new_pwd===''||$new_pwd2==='') {
      $_SESSION['flash_err']='กรอกข้อมูลให้ครบ'; header('Location: login.php'); exit;
    }
    if ($new_pwd!==$new_pwd2) {
      $_SESSION['flash_err']='รหัสผ่านใหม่ไม่ตรงกัน'; header('Location: login.php'); exit;
    }

    // ตรวจสอบว่าเบอร์นี้มีในระบบไหม
    $stmt=$conn->prepare("SELECT id FROM users WHERE phone=? LIMIT 1");
    $stmt->bind_param("s",$phone);
    $stmt->execute();
    $res=$stmt->get_result();
    $user=$res->fetch_assoc();
    $stmt->close();

    if(!$user){ // ถ้าไม่พบเบอร์ในระบบ
      $_SESSION['flash_err']='ไม่พบบัญชีของเบอร์นี้'; header('Location: login.php'); exit;
    }

    // ถ้ามีบัญชี → ตั้งรหัสใหม่ให้เลย
    $hash=password_hash($new_pwd,PASSWORD_BCRYPT);
    $stmt=$conn->prepare("UPDATE users SET password=? WHERE id=?");
    $stmt->bind_param("si",$hash,$user['id']);
    if($stmt->execute()){
      $_SESSION['flash_ok']='ตั้งรหัสผ่านใหม่เรียบร้อยแล้ว';
    }else{
      $_SESSION['flash_err']='ไม่สามารถตั้งรหัสใหม่ได้: '.$stmt->error;
    }
    $stmt->close();
    header('Location: login.php'); exit;
  }

  // ถ้า action ไม่ตรงกับที่ระบบรู้จัก
  $_SESSION['flash_err']='คำสั่งไม่ถูกต้อง';
  header('Location: login.php'); exit;
}
?>

<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>เข้าสู่ระบบ | Deep D House</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body{background:#0f0f0f;color:#fff;}
.form-control{background:#1c1c1c;color:#fff;border-color:#333;}
.form-control:focus{border-color:#f5a524;box-shadow:0 0 0 .2rem rgba(245,165,36,.25);}
.btn-warning{background:#f5a524;color:#000;font-weight:600;}
.link-warning{text-decoration:none;}
.link-warning:hover{text-decoration:underline;}
</style>
</head>
<body class="d-flex align-items-center" style="min-height:100vh;">
<div class="container" style="max-width:460px;">
  <h3 class="mb-3 fw-bold text-warning">เข้าสู่ระบบ</h3>

  <?php if($ok): ?><div class="alert alert-success"><?=htmlspecialchars($ok)?></div><?php endif; ?>
  <?php if($err): ?><div class="alert alert-danger"><?=htmlspecialchars($err)?></div><?php endif; ?>

  <form method="post" action="login.php" autocomplete="off" novalidate>
    <input type="hidden" name="csrf_token" value="<?=htmlspecialchars($_SESSION['csrf_token'])?>">
    <input type="hidden" name="action" value="login">

    <div class="mb-3">
      <label class="form-label">เบอร์โทรศัพท์</label>
      <input type="tel" name="phone" class="form-control" inputmode="numeric" maxlength="10"
             value="<?=htmlspecialchars($_SESSION['last_phone'] ?? '')?>" required>
    </div>

    <div class="mb-3">
      <label class="form-label d-flex justify-content-between">
        <span>รหัสผ่าน</span>
        <span>
          <a href="#" class="link-warning me-3" data-bs-toggle="modal" data-bs-target="#forgotModal">ลืมรหัสผ่าน?</a>
          <a href="#" class="link-warning" data-bs-toggle="modal" data-bs-target="#changeModal">เปลี่ยนรหัสผ่าน</a>
        </span>
      </label>
      <div class="input-group">
        <input type="password" name="password" class="form-control" required>
        <button type="button" class="btn btn-outline-light" id="togglePwd">แสดง</button>
      </div>
    </div>

    <button class="btn btn-warning w-100" type="submit">เข้าสู่ระบบ</button>
    <p class="mt-3">ยังไม่มีบัญชี? <a href="register.php" class="link-warning">สมัครสมาชิก</a></p>
  </form>
</div>

<!-- Forgot Password -->
<div class="modal fade" id="forgotModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content bg-dark text-white" method="post" action="login.php">
      <div class="modal-header">
        <h5 class="modal-title">ลืมรหัสผ่าน</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="csrf_token" value="<?=htmlspecialchars($_SESSION['csrf_token'])?>">
        <input type="hidden" name="action" value="forgot_password">
        <div class="mb-3">
          <label>เบอร์โทรศัพท์</label>
          <input type="tel" name="fp_phone" class="form-control" required>
        </div>
        <div class="mb-3">
          <label>รหัสผ่านใหม่</label>
          <input type="password" name="fp_new" class="form-control" minlength="4" required>
        </div>
        <div class="mb-3">
          <label>ยืนยันรหัสผ่านใหม่</label>
          <input type="password" name="fp_new2" class="form-control" minlength="4" required>
        </div>
        <div class="form-text text-warning">* ใช้สำหรับการตั้งรหัสใหม่กรณีลืมรหัส</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">ยกเลิก</button>
        <button type="submit" class="btn btn-warning text-dark fw-bold">ตั้งรหัสใหม่</button>
      </div>
    </form>
  </div>
</div>

<!-- Change Password -->
<div class="modal fade" id="changeModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content bg-dark text-white" method="post" action="login.php">
      <div class="modal-header">
        <h5 class="modal-title">เปลี่ยนรหัสผ่าน</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="csrf_token" value="<?=htmlspecialchars($_SESSION['csrf_token'])?>">
        <input type="hidden" name="action" value="change_password">
        <div class="mb-3"><label>เบอร์โทรศัพท์</label><input type="tel" name="cp_phone" class="form-control" required></div>
        <div class="mb-3"><label>รหัสผ่านเดิม</label><input type="password" name="cp_current" class="form-control" required></div>
        <div class="mb-3"><label>รหัสผ่านใหม่</label><input type="password" name="cp_new" class="form-control" required></div>
        <div class="mb-3"><label>ยืนยันรหัสผ่านใหม่</label><input type="password" name="cp_new2" class="form-control" required></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">ยกเลิก</button>
        <button type="submit" class="btn btn-warning text-dark fw-bold">บันทึก</button>
      </div>
    </form>
  </div>
</div>

<script>
document.getElementById('togglePwd').addEventListener('click', ()=>{
  const pwd=document.querySelector('[name="password"]');
  const isPwd=pwd.type==='password';
  pwd.type=isPwd?'text':'password';
  togglePwd.textContent=isPwd?'ซ่อน':'แสดง';
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
