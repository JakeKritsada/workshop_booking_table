<?php
require_once 'condb.php';
session_start();

// รองรับทั้ง $conn / $condb
if (!isset($conn) && isset($condb)) { $conn = $condb; }

// ดึงข้อมูลการจองที่รอตรวจสอบ (pending)
$sql = "SELECT * FROM tbl_booking WHERE payment_status = 'pending' ORDER BY no DESC";
$result = mysqli_query($conn, $sql) or die("SQL Error: " . mysqli_error($conn));
$count_pending = mysqli_num_rows($result);
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <title>ตรวจสอบการชำระเงิน | Deep D House</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    :root {
      --gold:#f5a524;
      --gold-2:#d9901d;
      --bg:#0e0e0e;
    }

    body {
      background: var(--bg);
      color: #fff;
      font-family: "Kanit", sans-serif;
      min-height: 100vh;
    }

    /* Navbar */
    .navbar-dark {
      background:#000;
      box-shadow:0 4px 16px rgba(245,165,36,0.25);
    }
    .navbar-brand {
      color:var(--gold)!important;
      font-weight:800;
      letter-spacing:0.5px;
    }
    .navbar-dark .nav-link {
      color:#fff!important;
      font-weight:600;
    }
    .navbar-dark .nav-link:hover,
    .navbar-dark .nav-link.active {
      color:var(--gold)!important;
    }

    /* Card + Table */
    .card {
      background:#151515;
      border:1px solid rgba(255,255,255,0.1);
      border-radius:18px;
      box-shadow:0 8px 25px rgba(0,0,0,.4);
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
      vertical-align:middle;
      border-color:rgba(255,255,255,0.05);
    }

    /* ✅ ปุ่มใหม่แบบดูเป็นทางการ */
    .btn-approve {
      background:linear-gradient(180deg,#3ccf4e,#28a745);
      border:none;
      color:#fff;
      font-weight:700;
      border-radius:10px;
      padding:6px 18px;
      box-shadow:0 4px 12px rgba(40,167,69,0.25);
      transition:all .25s ease;
    }
    .btn-approve:hover {
      background:linear-gradient(180deg,#48d55e,#32b94f);
      box-shadow:0 6px 16px rgba(50,205,50,0.35);
      transform:translateY(-1px);
    }

    .btn-reject {
      background:linear-gradient(180deg,#e74c3c,#c82333);
      border:none;
      color:#fff;
      font-weight:700;
      border-radius:10px;
      padding:6px 18px;
      box-shadow:0 4px 12px rgba(231,76,60,0.25);
      transition:all .25s ease;
    }
    .btn-reject:hover {
      background:linear-gradient(180deg,#ff5e4d,#e03e35);
      box-shadow:0 6px 16px rgba(255,99,71,0.35);
      transform:translateY(-1px);
    }

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

    footer {
      background:#000;
      color:#aaa;
      text-align:center;
      padding:16px;
      margin-top:50px;
      border-top:1px solid rgba(255,255,255,.1);
    }

    .footer-fixed {
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
    <a class="navbar-brand" href="admin.php">Deep D House</a>
    <button class="navbar-toggler bg-warning" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="admin.php">Admin</a></li>
        <li class="nav-item"><a class="nav-link active" href="#">ตรวจสอบการชำระเงิน</a></li>
      </ul>
    </div>
  </div>
</nav>

<!-- Content -->
<div class="container" style="margin-top: 90px;">

  <div class="text-center mb-4">
    <h2 class="fw-bold text-warning">🧾 ตรวจสอบการชำระเงิน</h2>
    <div class="status-box mt-3">จำนวนที่รอตรวจสอบ: <?= $count_pending ?> รายการ</div>
  </div>

  <div class="card p-4 mt-4">
    <?php if ($count_pending == 0): ?>
      <div class="alert alert-secondary text-center">❌ ยังไม่มีรายการรอตรวจสอบ</div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-dark table-hover text-center align-middle">
          <thead>
            <tr>
              <th>#</th>
              <th>ชื่อผู้จอง</th>
              <th>วันที่</th>
              <th>เวลา</th>
              <th>สถานะ</th>
              <th>สลิป</th>
              <th>จัดการ</th>
            </tr>
          </thead>
          <tbody>
          <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <tr>
              <td><?= $row['no'] ?></td>
              <td><?= htmlspecialchars($row['booking_name']) ?></td>
              <td><?= htmlspecialchars($row['booking_date']) ?></td>
              <td><?= htmlspecialchars($row['booking_time']) ?></td>
              <td><span class="text-warning fw-bold">⌛ รอตรวจสอบ</span></td>
              <td>
                <?php if (!empty($row['slip_img'])): ?>
                  <img src="<?= htmlspecialchars($row['slip_img']) ?>" width="140" class="rounded border border-warning shadow-sm">
                <?php else: ?>
                  <span class="text-secondary">ไม่มีสลิป</span>
                <?php endif; ?>
              </td>
              <td>
                <div class="d-flex justify-content-center gap-2">
                  <a href="verify_action.php?id=<?= $row['no'] ?>&act=approve" 
                     class="btn btn-approve" 
                     onclick="return confirm('ยืนยันอนุมัติการชำระเงินนี้หรือไม่?');">
                    ✅ อนุมัติ
                  </a>
                  <a href="verify_action.php?id=<?= $row['no'] ?>&act=reject" 
                     class="btn btn-reject" 
                     onclick="return confirm('ไม่อนุมัติรายการนี้ใช่หรือไม่?');">
                    ❌ ไม่อนุมัติ
                  </a>
                </div>
              </td>
            </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<footer class="footer-fixed">
  © 2025 Deep D House · Admin System
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
