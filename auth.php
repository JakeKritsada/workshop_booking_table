<?php
// auth.php

// เปิด session ถ้ายังไม่เปิด
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }

/** helper */
function isLoggedIn(): bool {
  // ปรับตามระบบล็อกอินของคุณ ถ้าตรวจด้วย user_id ก็เปลี่ยนเป็น isset($_SESSION['user_id'])
  return isset($_SESSION['role']) && $_SESSION['role'] !== '';
}
function currentUserRole(): string {
  return $_SESSION['role'] ?? 'guest';
}

/** แสดงหน้า 403 สวย ๆ แล้วหยุดการทำงาน */
function render403(string $title = 'คุณไม่มีสิทธิ์เข้าหน้านี้', string $desc = 'โปรดเข้าสู่ระบบด้วยสิทธิ์ที่ถูกต้องหรือกลับไปยังหน้าอื่น'): void {
  http_response_code(403);
  echo '<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<title>403 • ไม่มีสิทธิ์เข้าหน้านี้</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
  /* โทนเดียวกับ index */
  body{
    background:#0b0f17; color:#e6eaf2;
    min-height:100vh; display:flex; align-items:center; justify-content:center;
  }
  .card{
    background:#131a26; color:#e6eaf2;
    border:0; border-radius:1rem; max-width:560px
  }
  .text-muted{ color:#9aa4b2 !important; }
  .badge-x{ font-size:24px; margin-right:8px }

  /* ปุ่มหลักโทนเหลืองของเว็บ */
  .btn-primary{ background:#f5a524; border-color:#f5a524; color:#000; font-weight:600 }
  .btn-primary:hover{ background:#d9901d; border-color:#d9901d; color:#000 }

  /* ปุ่มเส้นขาวบนพื้นเข้มให้มองเห็นชัด */
  .btn-outline-light{
    border-color:#e6eaf2; color:#e6eaf2;
  }
  .btn-outline-light:hover{
    background:#e6eaf2; color:#0b0f17;
  }
</style>
</head>
<body>
  <div class="card p-4 shadow">
    <h4 class="mb-2"><span class="badge-x">⛔</span>'.$title.'</h4>
    <p class="text-muted mb-4">'.$desc.'</p>
    <div class="d-flex gap-2">
      <a class="btn btn-primary" href="index.php">กลับหน้าแรก</a>
      <a class="btn btn-outline-light" href="javascript:history.back()">ย้อนกลับ</a>
    </div>
  </div>
</body>
</html>';
  exit;
}


/**
 * ต้องล็อกอินเท่านั้น
 * ใช้ได้กับหน้าเดิมที่เรียก requireLogin();
 * พฤติกรรม: ถ้ายังไม่ล็อกอิน -> แสดง 403 (ไม่ redirect)
 */
function requireLogin(): void {
  if (!isLoggedIn()) {
    render403('คุณไม่มีสิทธิ์เข้าหน้านี้', 'กรุณาเข้าสู่ระบบก่อนเข้าหน้านี้');
  }
}

/**
 * บังคับสิทธิ์เข้าใช้งาน
 * @param string|array $roles เช่น "admin" หรือ ["admin","staff"]
 * ถ้าไม่ผ่าน -> แสดง 403 (ไม่ redirect)
 */
function requireRole($roles): void {
  // ต้องล็อกอินก่อน
  requireLogin();

  $userRole = currentUserRole();
  $allowed  = is_array($roles) ? in_array($userRole, $roles, true) : ($userRole === $roles);
  if ($allowed) return;

  render403('คุณไม่มีสิทธิ์เข้าหน้านี้', 'สิทธิ์ปัจจุบันของคุณไม่เพียงพอสำหรับหน้านี้');
}
// สรุปสั้นแบบตอบอาจารย์ได้เลย

// ในไฟล์ auth.php มีทั้งหมด 5 ฟังก์ชันหลัก
// ใช้สำหรับจัดการระบบสิทธิ์ผู้ใช้งาน โดย
// isLoggedIn() ตรวจว่าผู้ใช้ล็อกอินหรือยัง
// currentUserRole() ดึงสิทธิ์ของผู้ใช้จาก session
// render403() แสดงหน้า 403 Forbidden
// requireLogin() บังคับให้ล็อกอินก่อนเข้า
// requireRole() ตรวจสิทธิ์เฉพาะ role เช่น admin
// ทั้งหมดนี้ช่วยให้ระบบปลอดภัยและแยกสิทธิ์การเข้าถึงได้ชัดเจนระหว่างผู้ใช้ทั่วไปกับผู้ดูแลระบบ