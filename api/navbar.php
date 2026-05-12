<?php
// navbar.php - บรรทัดที่ 1 ต้องเริ่มด้วย <?php ทันที ห้ามมีบรรทัดว่างข้างบน
if (!headers_sent() && session_status() === PHP_SESSION_NONE) {
    session_start();
}

$logged_in = !empty($_SESSION['user_id']);
$phone     = $logged_in ? $_SESSION['phone'] : null;
$role      = $logged_in ? ($_SESSION['role'] ?? 'user') : null;
?>

<nav class="navbar navbar-expand-lg navbar-dark navbar-glow">
  <div class="container-fluid">
    
    <a class="navbar-brand" href="index.php">Deep D House</a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="index.php">หน้าแรก</a></li>
        <li class="nav-item"><a class="nav-link" href="index2.php">จองโต๊ะ</a></li>
        <li class="nav-item"><a class="nav-link" href="detailtable.php">ข้อมูลโต๊ะ</a></li>
        <li class="nav-item"><a class="nav-link" href="location.php">สาขา</a></li>
        <li class="nav-item"><a class="nav-link" href="my_bookings.php">ประวัติของฉัน</a></li>
        <?php if ($role === 'admin'): ?>
          <li class="nav-item"><a class="nav-link" href="admin.php">Admin</a></li>
        <?php endif; ?>
      </ul>

      <?php if ($logged_in): ?>
        <div class="d-flex align-items-center gap-2">
          <a href="index2.php" class="btn btn-primary">จองโต๊ะ</a>
          
       <div class="dropdown custom-dropdown">
    <button class="btn btn-outline-light dropdown-toggle profile-btn" type="button" data-bs-toggle="dropdown">
        <i class="bi bi-person-circle me-1"></i> ฉัน: <?= htmlspecialchars($phone) ?>
    </button>
    
    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 animate slideIn dropdown-menu-dark">
        <li class="dropdown-header text-uppercase ls-1">บัญชีผู้ใช้</li>
        
        <li class="px-4 py-3 info-box">
            <div class="d-flex align-items-center">
                <div class="avatar-sm me-3">
                    <img src="https://ui-avatars.com/api/?name=User&background=f5a524&color=000" class="rounded-circle shadow-sm" width="40">
                </div>
                <div>
                    <p class="mb-0 small text-white">เบอร์โทรศัพท์</p>
                    <p class="mb-0 fw-bold text-white"><?= htmlspecialchars($phone) ?></p>
                    <p class="mb-0 small text-white">Role : 
                        <span class="fw-bold"><?= htmlspecialchars($role) ?></span>
                    </p>
                </div>
            </div>
        </li>
        
        <li><hr class="dropdown-divider"></li>
        
        <li>
            <a class="dropdown-item d-flex align-items-center" href="profile.php">
                <i class="bi bi-person-vcard me-2"></i> รายละเอียดบัญชี
            </a>
        </li>
        <li>
            <a class="dropdown-item d-flex align-items-center" href="#" data-bs-toggle="modal" data-bs-target="#changePwModal">
                <i class="bi bi-shield-lock me-2"></i> เปลี่ยนรหัสผ่าน
            </a>
        </li>
        
        <li><hr class="dropdown-divider"></li>
        
        <li class="px-3 pb-2">
            <form method="post" action="logout.php">
                <button class="btn btn-warning w-100 logout-btn d-flex align-items-center justify-content-center fw-bold" type="submit">
                    <i class="bi bi-box-arrow-right me-2"></i> ออกจากระบบ
                </button>
            </form>
        </li>
    </ul>
</div>
        </div>
      <?php else: ?>
        <div class="d-flex gap-2">
          <a class="btn btn-sm btn-outline-light" href="login.php">เข้าสู่ระบบ</a>
          <a class="btn btn-sm btn-warning fw-semibold" style="color:#000;" href="register.php">สมัครสมาชิก</a>
        </div>
      <?php endif; ?>

    </div>
  </div>
</nav>

<div class="modal fade" id="changePwModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content text-dark" method="post" action="change_password.php" autocomplete="off">
      <div class="modal-header">
        <h5 class="modal-title">เปลี่ยนรหัสผ่าน</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">รหัสผ่านเดิม</label>
          <input type="password" name="old_password" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">รหัสผ่านใหม่</label>
          <input type="password" name="new_password" class="form-control" required minlength="4">
        </div>
        <div class="mb-2">
          <label class="form-label">ยืนยันรหัสผ่านใหม่</label>
          <input type="password" name="confirm_password" class="form-control" required minlength="4">
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">ยกเลิก</button>
        <button class="btn btn-primary" type="submit">บันทึก</button>
      </div>
    </form>
  </div>
</div>