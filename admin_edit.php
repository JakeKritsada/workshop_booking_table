<?php
require_once 'auth.php';
requireRole('admin'); 
// 🔹 ตรวจสอบสิทธิ์ผู้ใช้ ต้องเป็น admin เท่านั้นถึงจะเข้าหน้านี้ได้
// ถ้าไม่ใช่ admin → ฟังก์ชัน requireRole() จะ redirect ออกหรือตัดสิทธิ์

require_once 'condb.php'; // เรียกไฟล์เชื่อมต่อฐานข้อมูล
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); 
// สั่งให้ mysqli แสดง error แบบ Exception เพื่อใช้ try/catch ได้

if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); } 
// ถ้ายังไม่มี session ให้เริ่ม session

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
// ฟังก์ชันย่อสำหรับ escape ข้อความก่อนแสดงผล ป้องกัน XSS

function updateTableStatus($conn, $table_id, $status) {
  $stmt = mysqli_prepare($conn, "UPDATE tbl_table SET table_status = ? WHERE id = ?");
  mysqli_stmt_bind_param($stmt, "ii", $status, $table_id);
  mysqli_stmt_execute($stmt);
}
// $stmt (Statement Object) = ตัวแปรที่เก็บ “คำสั่ง SQL ที่ถูกเตรียมไว้”
// เพื่อให้เราส่งข้อมูลจริงเข้าไปทีหลังอย่างปลอดภัย โดยไม่ต้องต่อ string เอง
// ฟังก์ชันปรับสถานะโต๊ะ (0=ว่าง, 1=ไม่ว่าง, 2=รออนุมัติ)

/*---------------------------------------------
  ลบรายการล่าสุด
----------------------------------------------*/
if (isset($_GET['delete_latest']) && $_GET['delete_latest'] == '1') {
  try {
    mysqli_begin_transaction($conn); // เริ่ม transaction
    $res = mysqli_query($conn, "SELECT no, table_id FROM tbl_booking ORDER BY dateCreate DESC LIMIT 1");
    $latest = mysqli_fetch_assoc($res);
    if ($latest) {
      $no = (int)$latest['no'];
      $table_id = (int)$latest['table_id'];

          $stmtP = mysqli_prepare($conn, "DELETE FROM payments WHERE booking_id = ?");
      mysqli_stmt_bind_param($stmtP, "i", $no);
      mysqli_stmt_execute($stmtP);
      // ลบข้อมูลการชำระเงินของใบจองนั้น

      $stmtB = mysqli_prepare($conn, "DELETE FROM tbl_booking WHERE no = ?");
      mysqli_stmt_bind_param($stmtB, "i", $no);
      mysqli_stmt_execute($stmtB);
      // ลบข้อมูลการจองจริงออกจากฐานข้อมูล

      updateTableStatus($conn, $table_id, 0); // ตั้งสถานะโต๊ะกลับเป็น “ว่าง”
      mysqli_commit($conn);
      $_SESSION['msg'] = "✅ ลบรายการล่าสุดสำเร็จ และตั้งโต๊ะให้ว่างแล้ว";

    }
  } catch (Throwable $e) {
    mysqli_rollback($conn);
    $_SESSION['msg'] = "❌ ลบไม่สำเร็จ: ".$e->getMessage();
  }
  header("Location: admin_edit.php"); exit;
}

/*---------------------------------------------
  ดึงรายการโต๊ะ / การจอง
----------------------------------------------*/
$bookedTables = [];
$resBT = mysqli_query($conn, "SELECT id, table_name FROM tbl_table WHERE table_status IN (1,2) ORDER BY table_name ASC");
while($r = mysqli_fetch_assoc($resBT)){ $bookedTables[] = $r; }
// ดึงเฉพาะโต๊ะที่ “ไม่ว่างหรือรออนุมัติ” มาให้แอดมินเลือกใน dropdown

$selected_table_id = isset($_GET['table_id']) ? (int)$_GET['table_id'] : 0;

if ($selected_table_id > 0) {
  $stmt = mysqli_prepare($conn, "SELECT b.*, t.table_name, t.table_status
    FROM tbl_booking b
    JOIN tbl_table t ON b.table_id = t.id
    WHERE b.table_id = ?
    ORDER BY b.dateCreate DESC LIMIT 1");
  mysqli_stmt_bind_param($stmt, "i", $selected_table_id);
  mysqli_stmt_execute($stmt);
  $booking = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}
 else {
  $sql = "SELECT b.*, t.table_name, t.table_status
          FROM tbl_booking b
          JOIN tbl_table t ON b.table_id = t.id
          ORDER BY b.dateCreate DESC LIMIT 1";
  $booking = mysqli_fetch_assoc(mysqli_query($conn, $sql));
}

/*---------------------------------------------
  ฟังก์ชันอัปเดต / ยกเลิก / อนุมัติ / ปฏิเสธ
----------------------------------------------*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $booking) {

  if (isset($_POST['approve'])) {
    try {
      mysqli_begin_transaction($conn);
      $table_id = (int)$booking['table_id'];
      $stmt = mysqli_prepare($conn, "UPDATE tbl_table SET table_status = 1 WHERE id = ? AND table_status = 2");
      mysqli_stmt_bind_param($stmt, "i", $table_id);
      mysqli_stmt_execute($stmt);
      if (mysqli_stmt_affected_rows($stmt) < 1) throw new Exception('สถานะโต๊ะไม่พร้อมอนุมัติ');
      mysqli_commit($conn);
      $_SESSION['msg'] = "อนุมัติการจองสำเร็จ";
    } catch (Throwable $e) {
      mysqli_rollback($conn);
      $_SESSION['msg'] = "อนุมัติไม่สำเร็จ: ".$e->getMessage();
    }
    header("Location: admin_edit.php?table_id=$table_id"); exit;
  }

  if (isset($_POST['reject'])) {
    try {
      mysqli_begin_transaction($conn);
      $no = (int)$booking['no'];
      $table_id = (int)$booking['table_id'];
      $stmtP = mysqli_prepare($conn, "DELETE FROM payments WHERE booking_id = ?");
      mysqli_stmt_bind_param($stmtP, "i", $no);
      mysqli_stmt_execute($stmtP);
      $stmtB = mysqli_prepare($conn, "DELETE FROM tbl_booking WHERE no = ?");
      mysqli_stmt_bind_param($stmtB, "i", $no);
      mysqli_stmt_execute($stmtB);
      updateTableStatus($conn, $table_id, 0);
      mysqli_commit($conn);
      $_SESSION['msg'] = "ปฏิเสธคำขอและคืนโต๊ะเรียบร้อย";
    } catch (Throwable $e) {
      mysqli_rollback($conn);
      $_SESSION['msg'] = "ปฏิเสธไม่สำเร็จ: ".$e->getMessage();
    }
    header("Location: admin_edit.php"); exit;
  }
// 🔹 แก้ไขข้อมูลการจอง (update)
  if (isset($_POST['update'])) {
    try {
      mysqli_begin_transaction($conn);
      $no = (int)$_POST['no'];
      $old_table_id = (int)$booking['table_id'];
      $table_id      = (int)$_POST['table_id'];
      $booking_name  = trim($_POST['booking_name']);
      $booking_date  = trim($_POST['booking_date']);
      $booking_time  = trim($_POST['booking_time']);
      $booking_phone = trim($_POST['booking_phone']);
      $booking_staff = trim($_POST['booking_staff']);
      // รับข้อมูลใหม่จากฟอร์มมาอัปเดต
            $stmt = mysqli_prepare($conn, "UPDATE tbl_booking
        SET table_id=?, booking_name=?, booking_date=?, booking_time=?, booking_phone=?, booking_staff=? WHERE no=?");
      mysqli_stmt_bind_param($stmt, "isssssi", $table_id, $booking_name, $booking_date, $booking_time, $booking_phone, $booking_staff, $no);
      mysqli_stmt_execute($stmt);

      // ถ้ามีการเปลี่ยนโต๊ะ ต้องคืนโต๊ะเก่าและจองโต๊ะใหม่
      if ($old_table_id != $table_id) {
        updateTableStatus($conn, $old_table_id, 0); // โต๊ะเก่ากลับเป็นว่าง
        updateTableStatus($conn, $table_id, 1);     // โต๊ะใหม่เป็นไม่ว่าง
      }

      mysqli_commit($conn);
      $_SESSION['msg'] = "บันทึกการแก้ไขสำเร็จ";

    } catch (Throwable $e) {
      mysqli_rollback($conn);
      $_SESSION['msg'] = "บันทึกไม่สำเร็จ: ".$e->getMessage();
    }
    header("Location: admin_edit.php?table_id=$table_id"); exit;
  }

  if (isset($_POST['cancel'])) {
    try {
      mysqli_begin_transaction($conn);
      $no = (int)$_POST['no'];
      $table_id = (int)$booking['table_id'];
      $stmtP = mysqli_prepare($conn, "DELETE FROM payments WHERE booking_id = ?");
      mysqli_stmt_bind_param($stmtP, "i", $no);
      mysqli_stmt_execute($stmtP);
      $stmtB = mysqli_prepare($conn, "DELETE FROM tbl_booking WHERE no=?");
      mysqli_stmt_bind_param($stmtB, "i", $no);
      mysqli_stmt_execute($stmtB);
      updateTableStatus($conn, $table_id, 0);
      mysqli_commit($conn);
      $_SESSION['msg'] = "ยกเลิกการจองแล้ว";
    } catch (Throwable $e) {
      mysqli_rollback($conn);
      $_SESSION['msg'] = "ยกเลิกไม่สำเร็จ: ".$e->getMessage();
    }
    header("Location: admin_edit.php"); exit;
  }
}

/*---------------------------------------------
  ดึงรายการโต๊ะทั้งหมด
----------------------------------------------*/
$tables = mysqli_query($conn, "SELECT id, table_name, table_status FROM tbl_table ORDER BY table_name ASC");
$createdAt = $booking['dateCreate'] ?? ($booking['created_at'] ?? '-');
?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<title>จัดการ/อนุมัติการจอง (Admin)</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
:root{--gold:#f5a524;--gold2:#d9901d;--bg:#0b0b0b;--card:#151515;}
body{background:var(--bg);color:#fff;font-family:'Kanit',sans-serif;}
.navbar-dark{background:#000;box-shadow:0 4px 16px rgba(245,165,36,.25);}
.navbar-brand{color:var(--gold)!important;font-weight:800;}
.card{background:var(--card);border:1px solid rgba(255,255,255,.08);border-radius:16px;box-shadow:0 10px 30px rgba(0,0,0,.4);}
label{color:var(--gold2);font-weight:600;}
.form-control,.form-select{background:#1a1a1a;color:#fff;border:1px solid rgba(245,165,36,.3);}
.form-control:focus,.form-select:focus{border-color:var(--gold);box-shadow:0 0 0 .25rem rgba(245,165,36,.25);}
.btn-warning{background:var(--gold);color:#000;border:none;font-weight:700;}
.btn-warning:hover{background:var(--gold2);}
.btn-danger{background:#ef4444;border:none;font-weight:700;}
.btn-success{background:#22c55e;border:none;font-weight:700;}
.btn-info{background:#0ea5e9;border:none;color:#052c3a;font-weight:700;}
.btn-outline-light{border:1px solid rgba(245,165,36,.4);color:#f5f5f5;}
.btn-outline-light:hover{background:var(--gold2);color:#000;}
footer{text-align:center;color:#aaa;padding:16px;margin-top:40px;font-size:.9rem;}
</style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark">
 <div class="container-fluid">
  <a class="navbar-brand" href="admin.php">Deep D House Admin</a>
  <a class="btn btn-warning btn-sm" href="admin_list.php">← กลับรายชื่อ / ประวัติ</a>
 </div>
</nav>

<div class="container py-5" style="max-width:900px;">
  <?php if (!empty($_SESSION['msg'])): ?>
    <div class="alert alert-info text-center"><?= $_SESSION['msg']; unset($_SESSION['msg']); ?></div>
  <?php endif; ?>

  <?php if (!$booking): ?>
    <div class="card p-4 text-center">
      <h4 class="text-warning mb-3">ยังไม่พบข้อมูลการจอง</h4>
      <form method="get" class="row g-3 align-items-end justify-content-center">
        <div class="col-md-8">
          <label class="form-label text-warning">เลือกโต๊ะที่กำลังถือสิทธิ์</label>
          <select name="table_id" class="form-select">
            <?php if (empty($bookedTables)): ?>
              <option>— ไม่มีโต๊ะที่ถูกจอง / รออนุมัติ —</option>
            <?php else: foreach($bookedTables as $t): ?>
              <option value="<?= (int)$t['id']; ?>"><?= h($t['table_name']); ?></option>
            <?php endforeach; endif; ?>
          </select>
        </div>
        <div class="col-md-4 d-flex gap-2">
          <button class="btn btn-warning flex-fill" type="submit">โหลดข้อมูล</button>
          <a href="admin_edit.php?delete_latest=1" class="btn btn-danger flex-fill" onclick="return confirm('ยืนยันลบรายการล่าสุด?');">ลบล่าสุด</a>
        </div>
      </form>
    </div>
  <?php else: ?>
  <div class="card p-4">
    <h4 class="text-warning text-center mb-4">จัดการ / อนุมัติการจอง</h4>
    <form method="get" class="row g-2 align-items-end mb-3">
      <div class="col-md-8">
        <label class="form-label">เลือกโต๊ะที่จองอยู่ / รออนุมัติ</label>
        <select name="table_id" class="form-select">
          <?php foreach($bookedTables as $t): ?>
            <option value="<?= (int)$t['id']; ?>" <?= ($t['id']==$booking['table_id'])?'selected':''; ?>><?= h($t['table_name']); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4 d-flex gap-2">
        <button class="btn btn-warning flex-fill" type="submit">โหลดข้อมูล</button>
        <a href="admin_edit.php?delete_latest=1" class="btn btn-danger flex-fill" onclick="return confirm('ยืนยันลบรายการล่าสุด?');">ลบล่าสุด</a>
      </div>
    </form>

    <form method="post">
      <input type="hidden" name="no" value="<?= (int)$booking['no']; ?>">
      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label">โต๊ะ (เปลี่ยนโต๊ะได้)</label>
          <select name="table_id" class="form-select" required>
            <?php mysqli_data_seek($tables, 0); while ($t = mysqli_fetch_assoc($tables)): ?>
              <?php $labelStatus = ($t['table_status']==0?'ว่าง':($t['table_status']==2?'รออนุมัติ':'ไม่ว่าง')); ?>
              <option value="<?= (int)$t['id']; ?>" <?= ($t['id']==$booking['table_id'])?'selected':''; ?>>
                <?= h($t['table_name']); ?> (<?= $labelStatus; ?>)
              </option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="col-md-3 mb-3">
          <label class="form-label">วันที่จอง</label>
          <input type="date" name="booking_date" value="<?= h($booking['booking_date']); ?>" class="form-control" required>
        </div>
        <div class="col-md-3 mb-3">
          <label class="form-label">เวลาจอง</label>
          <input type="time" name="booking_time" value="<?= h(substr($booking['booking_time'],0,5)); ?>" class="form-control" required>
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">ชื่อผู้จอง</label>
          <input type="text" name="booking_name" value="<?= h($booking['booking_name']); ?>" class="form-control" required>
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">เบอร์โทร</label>
          <input type="text" name="booking_phone" value="<?= h($booking['booking_phone']); ?>" class="form-control" required>
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">พนักงาน</label>
          <input type="text" name="booking_staff" value="<?= h($booking['booking_staff']); ?>" class="form-control">
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">วันที่สร้าง</label>
          <input type="text" class="form-control" value="<?= h($createdAt); ?>" readonly>
        </div>
      </div>

      <div class="d-flex flex-wrap gap-2 mt-3">
        <?php if ((int)$booking['table_status'] === 2): ?>
          <button type="submit" name="approve" class="btn btn-success" onclick="return confirm('ยืนยันอนุมัติคำขอนี้?');">✅ อนุมัติ</button>
          <button type="submit" name="reject" class="btn btn-outline-light" onclick="return confirm('ยืนยันปฏิเสธคำขอนี้?');">❌ ปฏิเสธ</button>
        <?php endif; ?>
        <button type="submit" name="update" class="btn btn-info">💾 บันทึกการแก้ไข</button>
        <button type="submit" name="cancel" class="btn btn-danger" onclick="return confirm('ยืนยันการยกเลิกการจอง?');">🗑️ ยกเลิกการจอง</button>
      </div>
    </form>
  </div>
  <?php endif; ?>
</div>
<footer>© 2025 Deep D House · Admin System</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
