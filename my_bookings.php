<?php
require_once 'condb.php'; 
session_start(); 

if (!isset($conn) && isset($condb)) { $conn = $condb; }

if (!isset($_SESSION['phone']) || empty($_SESSION['phone'])) { 
    echo "<div style='background:#0e0e0e; color:#fff; height:100vh; display:flex; flex-direction:column; align-items:center; justify-content:center; font-family:Kanit;'>
            <div style='background:#151515; padding:40px; border-radius:20px; border:1px solid #f5a524;'>
              <h2 style='color:#f5a524;'>🚫 กรุณาเข้าสู่ระบบ</h2>
              <p style='color:#aaa;'>โปรดเข้าสู่ระบบเพื่อดูประวัติการจองของคุณ</p>
              <a href='index.php' style='background:#f5a524; color:#000; text-decoration:none; padding:10px 25px; border-radius:10px; font-weight:bold;'>กลับหน้าแรก</a>
            </div>
          </div>";
    exit;
}

$phone = $_SESSION['phone'];
$name  = $_SESSION['fullname'] ?? 'ผู้ใช้ Deep D House';
$order = ($_GET['order'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
$toggleOrder = $order === 'asc' ? 'desc' : 'asc';
$orderLabel = $order === 'asc' ? 'เก่าสุด → ล่าสุด' : 'ล่าสุด → เก่าสุด';

$sql_history = "SELECT * FROM tbl_booking WHERE booking_phone = '$phone' ORDER BY booking_date $order, booking_time $order, no $order";
$result = mysqli_query($conn, $sql_history);
$bookings = $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : []; 
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ประวัติการจอง | Deep D House</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        /* 🚩 Sticky Footer & Layout */
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-color: #0e0e0e;
            color: #fff;
            margin: 0;
        }

        .main-content {
            flex: 1; /* ดัน Footer ลงล่าง */
            padding-top: 100px; /* เว้นระยะห่างจาก Navbar */
            padding-bottom: 50px;
        }

        /* ตกแต่งตาราง */
        .table-custom {
            background: #151515;
            border-radius: 15px;
            overflow: hidden;
            border: 1px solid rgba(245, 165, 36, 0.1);
        }

        .table-custom thead {
            background: #000;
        }

        .table-custom th {
            color: #f5a524;
            padding: 15px;
            border: none;
        }

        .table-custom td {
            color: #ccc;
            padding: 15px;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }

        /*Badge สถานะ */
        .badge-status {
            padding: 6px 12px;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        footer {
            background: #000;
            color: #555;
            text-align: center;
            padding: 25px 0;
            border-top: 1px solid rgba(245, 165, 36, 0.1);
        }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="main-content">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="text-gold fw-bold mb-1">📜 ประวัติการจอง</h3>
                <p class="text-light small mb-1">ข้อมูลสำหรับเบอร์: <?= htmlspecialchars($phone) ?></p>
            </div>
            <a href="?order=<?= $toggleOrder ?>" class="btn btn-sm btn-outline-warning rounded-pill px-3">
                 เรียง: <?= $orderLabel ?>
            </a>
        </div>

        <?php if (empty($bookings)): ?>
            <div class="card p-5 text-center bg-dark border-secondary">
                <p class="text-muted mb-0">❌ ยังไม่มีประวัติการจองในระบบ</p>
            </div>
        <?php else: ?>
            <div class="table-responsive table-custom shadow-lg">
                <table class="table table-dark table-hover mb-0 align-middle text-center">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>วันที่จอง</th>
                            <th>เวลา</th>
                            <th>ชื่อผู้จอง</th>
                            <th>สถานะ</th>
                            <th>การจัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $b): ?>
                            <tr>
                                <td class="fw-bold text-whit"><?= $b['no'] ?></td>
                                <td class="fw-bold text-white"><?= htmlspecialchars($b['booking_date']) ?></td>
                                <td><?= htmlspecialchars($b['booking_time']) ?> น.</td>
                                <td><?= htmlspecialchars($b['booking_name']) ?></td>
                                <td>
                                    <?php if ($b['payment_status'] === 'paid'): ?>
                                        <span class="badge-status" style="background: rgba(40,167,69,0.15); color: #28a745; border: 1px solid #28a745;">✅ ชำระแล้ว</span>
                                    <?php elseif ($b['payment_status'] === 'pending'): ?>
                                        <span class="badge-status" style="background: rgba(255,193,7,0.15); color: #ffc107; border: 1px solid #ffc107;">⌛ รอตรวจสอบ</span>
                                    <?php else: ?>
                                        <span class="badge-status" style="background: rgba(220,53,69,0.15); color: #dc3545; border: 1px solid #dc3545;">❌ ไม่ผ่าน</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group gap-2">
                                        <a href="booking_status.php?id=<?= $b['no'] ?>" class="btn btn-sm btn-outline-warning rounded-pill px-3">สถานะ</a>
                                        <?php if ($b['payment_status'] === 'paid'): ?>
                                            <a href="receipt.php?id=<?= $b['no'] ?>" class="btn btn-sm btn-outline-success rounded-pill px-3">ใบเสร็จ</a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <div class="text-center mt-5">
            <a href="index.php" class="btn btn-primary px-4 py-2 rounded-pill shadow-sm">⬅ กลับหน้าหลัก</a>
        </div>
    </div>
</div>

<footer>
    <div class="container">
© 2025 Deep D House · ระบบจองโต๊ะอาหารออนไลน์    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>