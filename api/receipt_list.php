<?php
require_once 'condb.php'; // เรียกไฟล์เชื่อมต่อฐานข้อมูล
session_start(); // เริ่มต้น session สำหรับเก็บสถานะผู้ใช้

// ✅ รองรับทั้งตัวแปรเชื่อมต่อ $conn / $condb (บางไฟล์ตั้งชื่อไม่ตรงกัน)
if (!isset($conn) && isset($condb)) { 
  $conn = $condb; // ถ้ามี $condb แต่ไม่มี $conn → ใช้ $condb แทน
}

// 🔍 รับค่าค้นหาจากฟอร์ม (GET parameter ชื่อ search)
$search = trim($_GET['search'] ?? ''); 
// ถ้ามีพารามิเตอร์ ?search=... ให้เอามาเก็บไว้ใน $search
// ถ้าไม่มีให้เป็นค่าว่าง ('')
// ใช้ trim() เพื่อตัดช่องว่างหัวท้ายออก

// ✅ เริ่มสร้างคำสั่ง SQL หลัก
$sql = "SELECT r.*, b.booking_name, b.booking_date, b.booking_time, b.payment_status
        FROM tbl_receipt AS r
        LEFT JOIN tbl_booking AS b ON r.booking_no = b.no";
// ↑ ดึงข้อมูลจากตาราง tbl_receipt (r)
//    และเชื่อม (JOIN) กับ tbl_booking (b)
//    เพื่อให้ได้ข้อมูลเพิ่มเติม เช่น ชื่อผู้จอง, วันที่, เวลา, สถานะการจ่ายเงิน

// ✅ ถ้ามีการค้นหา (search ไม่ว่าง)
if ($search !== '') {
  // ทำความสะอาดค่า search เพื่อกัน SQL Injection
  $search_safe = mysqli_real_escape_string($conn, $search);

  // ต่อคำสั่ง SQL ให้กรองเฉพาะใบเสร็จที่มีคำค้นอยู่ใน receipt_code หรือชื่อผู้จอง
  $sql .= " WHERE r.receipt_code LIKE '%$search_safe%' 
            OR b.booking_name LIKE '%$search_safe%'";
}

// ✅ จัดเรียงข้อมูลใบเสร็จจากใหม่ → เก่า (ล่าสุดอยู่บนสุด)
$sql .= " ORDER BY r.receipt_date DESC";

// ✅ รันคำสั่ง SQL จริง
$result = mysqli_query($conn, $sql) or die("SQL Error: " . mysqli_error($conn));
// ถ้าคำสั่งผิดพลาดจะหยุดและแสดงข้อความ error

// ✅ นับจำนวนแถวที่ได้จากผลลัพธ์ (จำนวนใบเสร็จทั้งหมด)
$count_all = mysqli_num_rows($result); 
// เช่น ถ้ามีใบเสร็จ 15 ใบ → $count_all = 15
?>

<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <title>ประวัติใบเสร็จทั้งหมด | Deep D House</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    :root {
      --gold:#f5a524;
      --gold-2:#d9901d;
      --bg:#0e0e0e;
    }
    body {
      background:var(--bg);
      color:#fff;
      font-family:'Kanit',sans-serif;
      min-height:100vh;
    }

    /* Navbar */
    .navbar-dark {
      background:#000;
      box-shadow:0 4px 16px rgba(245,165,36,0.25);
    }
    .navbar-brand {
      color:var(--gold)!important;
      font-weight:800;
      letter-spacing:.5px;
    }
    .navbar-dark .nav-link {
      color:#fff!important;
      font-weight:600;
    }
    .navbar-dark .nav-link:hover,
    .navbar-dark .nav-link.active {
      color:var(--gold)!important;
    }

    /* กล่องสรุป */
    .status-box {
      background:rgba(245,165,36,0.1);
      border:1px solid rgba(245,165,36,0.3);
      border-radius:10px;
      display:inline-block;
      padding:10px 25px;
      color:var(--gold);
      font-weight:700;
      box-shadow:0 4px 14px rgba(245,165,36,0.15);
    }

    /* Search bar */
    .search-box {
      max-width:480px;
      margin:0 auto 24px auto;
    }
    .search-box input {
      border:1px solid rgba(245,165,36,0.4);
      background:#151515;
      color:#fff;
      border-radius:8px;
      padding:10px 14px;
    }
    .search-box input:focus {
      border-color:var(--gold);
      box-shadow:0 0 0 0.2rem rgba(245,165,36,0.25);
    }
    .btn-search {
      background:var(--gold);
      border:none;
      color:#000;
      font-weight:700;
    }
    .btn-search:hover {
      background:var(--gold-2);
      color:#000;
    }

    /* ตาราง */
    .card {
      background:#151515;
      border:1px solid rgba(255,255,255,0.08);
      border-radius:18px;
      box-shadow:0 10px 30px rgba(0,0,0,.4);
    }
    .table-dark {
      background:#1a1a1a;
      border-radius:12px;
      overflow:hidden;
    }
    .table th {
      color:var(--gold);
      font-weight:700;
      border-bottom:2px solid rgba(245,165,36,0.2);
    }
    .table td {
      border-color:rgba(255,255,255,0.05);
      vertical-align:middle;
    }

    .btn-main {
      background:var(--gold);
      color:#000;
      font-weight:700;
      border:none;
    }
    .btn-main:hover {
      background:var(--gold-2);
      color:#000;
    }

    .status-paid { color:#28a745; font-weight:bold; }
    .status-pending { color:var(--gold); font-weight:bold; }
    .status-rejected { color:#dc3545; font-weight:bold; }

    footer {
      position:fixed;
      bottom:0;
      left:0;
      width:100%;
      background:#000;
      color:#aaa;
      text-align:center;
      padding:12px 0;
      font-size:0.9rem;
      border-top:1px solid rgba(255,255,255,.1);
      box-shadow:0 -4px 10px rgba(0,0,0,0.3);
      z-index:1000;
    }
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark fixed-top">
  <div class="container-fluid px-3">
    <a class="navbar-brand" href="index.php">Deep D House</a>
    <button class="navbar-toggler bg-warning" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="index.php">หน้าแรก</a></li>
        <li class="nav-item"><a class="nav-link" href="index2.php">จองโต๊ะ</a></li>
        <li class="nav-item"><a class="nav-link" href="detailtable.php">ข้อมูลโต๊ะ</a></li>
        <li class="nav-item"><a class="nav-link active" href="#">ประวัติใบเสร็จ</a></li>
        <li class="nav-item"><a class="nav-link" href="admin.php">Admin</a></li>
      </ul>
    </div>
  </div>
</nav>

<!-- Content -->
<div class="container" style="margin-top:100px; margin-bottom:80px;">
  <div class="text-center mb-4">
    <h2 class="fw-bold text-warning">📜 ประวัติใบเสร็จทั้งหมด</h2>
    <p class="text-light mb-2">ค้นหาใบเสร็จจากชื่อผู้จองหรือเลขที่ใบเสร็จได้เลย</p>
    <div class="status-box mb-3">จำนวนใบเสร็จทั้งหมด: <?= $count_all ?> รายการ</div>
  </div>

  <!-- 🔍 ช่องค้นหา -->
  <form method="get" class="search-box d-flex justify-content-center">
    <input type="text" name="search" class="form-control me-2" placeholder="ค้นหาด้วยเลขที่ใบเสร็จหรือชื่อผู้จอง..." value="<?= htmlspecialchars($search) ?>">
    <button type="submit" class="btn btn-search">ค้นหา</button>
  </form>

  <div class="card p-4 mt-3">
    <?php if ($count_all == 0): ?>
      <div class="alert alert-secondary text-center">❌ ไม่พบข้อมูลที่ตรงกับ “<?= htmlspecialchars($search) ?>”</div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-dark table-hover align-middle text-center">
          <thead>
            <tr>
              <th>#</th>
              <th>เลขที่ใบเสร็จ</th>
              <th>ชื่อผู้จอง</th>
              <th>วันที่จอง</th>
              <th>เวลา</th>
              <th>ยอดชำระ</th>
              <th>สถานะ</th>
              <th>ดูใบเสร็จ</th>
            </tr>
          </thead>
          <tbody>
          <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <tr>
              <td><?= $row['id'] ?></td>
              <td><?= htmlspecialchars($row['receipt_code']) ?></td>
              <td><?= htmlspecialchars($row['booking_name'] ?? '-') ?></td>
              <td><?= htmlspecialchars($row['booking_date'] ?? '-') ?></td>
              <td><?= htmlspecialchars($row['booking_time'] ?? '-') ?></td>
              <td><?= number_format($row['total_amount'], 2) ?> บาท</td>
              <td>
                <?php if ($row['payment_status'] == 'paid'): ?>
                  <span class="status-paid">ชำระแล้ว</span>
                <?php elseif ($row['payment_status'] == 'pending'): ?>
                  <span class="status-pending">รอตรวจสอบ</span>
                <?php else: ?>
                  <span class="status-rejected">ไม่ผ่าน</span>
                <?php endif; ?>
              </td>
              <td><a href="receipt.php?id=<?= $row['booking_no'] ?>" class="btn btn-main btn-sm">ดูใบเสร็จ</a></td>
            </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<footer>
  © 2025 Deep D House · Admin System
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
