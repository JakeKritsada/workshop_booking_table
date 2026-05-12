<?php
require_once 'condb.php';
session_start();

// 🔒 ตรวจสอบสิทธิ์เฉพาะแอดมิน
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header("Location: 403.php");
  exit;
}

// 🔍 ค้นหาคำค้น
$kw = trim($_GET['q'] ?? '');

// 🔧 Query ดึงเฉพาะโต๊ะที่ “ไม่ว่าง”
$sql = "
  SELECT 
    b.no,
    b.table_id,
    b.booking_name,
    b.booking_date,
    b.booking_time,
    b.booking_phone,
    b.booking_staff,
    t.table_name,
    t.table_status,
    b.payment_status,
    COALESCE(r.total_amount, 0) AS total_amount
  FROM tbl_booking b
  JOIN tbl_table t ON t.id = b.table_id
  LEFT JOIN tbl_receipt r ON r.booking_no = b.no
  WHERE t.table_status != 0
";

$params = [];
if ($kw !== '') {
  $sql .= " AND (b.booking_name LIKE ? OR b.booking_phone LIKE ? OR t.table_name LIKE ?) ";
  $like = "%$kw%";
  $params = [$like, $like, $like];
}
$sql .= " ORDER BY b.booking_date DESC, b.booking_time DESC";

$stmt = mysqli_prepare($conn, $sql);
if ($params) mysqli_stmt_bind_param($stmt, "sss", $params[0], $params[1], $params[2]);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// ===== Helper ฟังก์ชัน =====
function h($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

function statusBadge($status) {
  switch ($status) {
    case 0: return '<span class="badge bg-success">ว่าง</span>';
    case 1: return '<span class="badge bg-danger">ไม่ว่าง</span>';
    case 2: return '<span class="badge bg-info text-dark">รอแอดมินยืนยัน</span>';
    default: return '<span class="badge bg-secondary">ไม่ทราบ</span>';
  }
}

function payBadge($status, $amount) {
  if ($status == 'paid') return '<span class="badge bg-success">ชำระแล้ว ' . number_format($amount, 2) . '฿</span>';
  if ($status == 'pending') return '<span class="badge bg-warning text-dark">รอตรวจสลิป</span>';
  return '<span class="badge bg-secondary">ยังไม่ชำระ</span>';
}
?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<title>📋 รายชื่อการจอง | Deep D House Admin</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
:root{
  --gold:#f5a524;
  --gold-2:#d9901d;
  --bg:#0b0b0b;
  --card:#151515;
}
body{
  background:var(--bg);
  color:#fff;
  font-family:"Kanit",sans-serif;
  min-height:100vh;
}
.navbar-dark{
  background:#000;
  box-shadow:0 4px 16px rgba(245,165,36,.25);
}
.navbar-brand{ color:var(--gold)!important; font-weight:800; }
.navbar-dark .nav-link{ color:#fff!important; font-weight:600; }
.navbar-dark .nav-link:hover, .navbar-dark .nav-link.active{ color:var(--gold)!important; }

.card{
  background:var(--card);
  border:1px solid rgba(255,255,255,.08);
  border-radius:16px;
  box-shadow:0 10px 30px rgba(0,0,0,.4);
}
.form-control{
  background:#1a1a1a;
  border:1px solid rgba(245,165,36,.3);
  color:#fff;
  border-radius:10px;
}
.form-control:focus{
  border-color:var(--gold);
  box-shadow:0 0 0 .25rem rgba(245,165,36,.25);
}
.table-dark{
  background:#1b1b1b;
}
.table thead th{
  color:var(--gold);
  border-bottom:2px solid rgba(245,165,36,.25);
  background:#111;
  position:sticky;
  top:0;
  z-index:5;
}
.table td{ border-color:rgba(255,255,255,.05); vertical-align:middle; }
footer{
  background:#000;
  color:#aaa;
  text-align:center;
  padding:16px;
  margin-top:60px;
  font-size:.9rem;
  border-top:1px solid rgba(255,255,255,.1);
}
.btn-warning{
  background:var(--gold);
  color:#000;
  border:none;
  font-weight:700;
}
.btn-warning:hover{ background:var(--gold-2); }
</style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="admin.php">Deep D House Admin</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="admin.php">แดชบอร์ด</a></li>
        <li class="nav-item"><a class="nav-link active" href="#">รายการจอง</a></li>
      </ul>
     <form class="d-flex" method="get" style="gap:10px;">
  <input 
    class="form-control me-2"
    type="search"
    name="q"
    placeholder="ค้นหา: ชื่อ / เบอร์ / โต๊ะ"
    value="<?=h($kw)?>"
    style="
      background:#ffffff;          /* ✅ พื้นขาว */
      color:#000;                  /* ✅ ตัวอักษรดำ */
      border:2px solid #f5a524;    /* ✅ ขอบทองชัด */
      border-radius:10px;
      font-weight:500;
      font-size:15px;
      padding:10px 14px;
      transition:0.2s;
    "
    onfocus="this.style.boxShadow='0 0 8px rgba(245,165,36,0.6)'"
    onblur="this.style.boxShadow='none'"
  >
  <button
    class="btn btn-warning"
    type="submit"
    style="
      background:#f5a524;
      color:#000;
      font-weight:700;
      border:none;
      border-radius:10px;
      padding:10px 20px;
      transition:0.25s;
      box-shadow:0 0 10px rgba(245,165,36,0.5);
    "
    onmouseover="this.style.background='#d9901d'"
    onmouseout="this.style.background='#f5a524'"
  >
    ค้นหา
  </button>
</form>

    </div>
  </div>
</nav>

<div class="container py-4">
  <h3 class="text-warning mb-4 text-center">📋 รายชื่อการจอง (โต๊ะที่ไม่ว่าง)</h3>

  <div class="card p-3">
    <div class="table-responsive" style="max-height:70vh;">
      <table class="table table-dark table-striped table-hover align-middle text-center">
        <thead>
          <tr>
            <th>#</th>
            <th>โต๊ะ</th>
            <th>ผู้จอง</th>
            <th>เบอร์โทร</th>
            <th>ยอดชำระ</th>
            <th>สถานะชำระเงิน</th>
            <th>สถานะโต๊ะ</th>
            <th>วันที่จอง</th>
            <th>เวลา</th>
            <th>การจัดการ</th>
          </tr>
        </thead>
        <tbody>
        <?php if (mysqli_num_rows($result) == 0): ?>
          <tr><td colspan="10" class="py-4 text-secondary">ยังไม่มีการจองโต๊ะที่ไม่ว่าง</td></tr>
        <?php else: ?>
          <?php while($r = mysqli_fetch_assoc($result)): ?>
            <tr>
              <td>#<?= (int)$r['no'] ?></td>
              <td><?= h($r['table_name']) ?></td>
              <td><?= h($r['booking_name']) ?></td>
              <td><?= h($r['booking_phone']) ?></td>
              <td><?= number_format($r['total_amount'],2) ?> ฿</td>
              <td><?= payBadge($r['payment_status'], $r['total_amount']) ?></td>
              <td><?= statusBadge((int)$r['table_status']) ?></td>
              <td><?= h($r['booking_date']) ?></td>
              <td><?= h(substr($r['booking_time'],0,5)) ?></td>
              <td>
                <a class="btn btn-sm btn-warning text-dark" href="admin_edit.php?no=<?=$r['no']?>">จัดการ</a>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<footer>
  © 2025 Deep D House · Admin System
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
