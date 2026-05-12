<?php
require_once 'condb.php';
session_start();

// 🔐 1. ตรวจสอบสิทธิ์เฉพาะแอดมิน
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    ?>
    <!DOCTYPE html>
    <html lang="th">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>403 - ไม่มีสิทธิ์เข้าหน้านี้</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="style.css">
        <style>
            body { background-color: #0e0e0e; color: #fff; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; font-family: 'Kanit', sans-serif; }
            .error-box { text-align: center; background: #151515; padding: 50px; border-radius: 20px; border: 1px solid #f5a524; box-shadow: 0 0 30px rgba(245, 165, 36, 0.2); }
            .error-icon { font-size: 4rem; margin-bottom: 20px; }
        </style>
    </head>
    <body>
        <div class="error-box">
            <div class="error-icon">🚫</div>
            <h2 class="text-warning">คุณไม่มีสิทธิ์เข้าหน้านี้</h2>
            <p class="text-secondary">สิทธิ์ใช้งานปัจจุบันของคุณไม่เพียงพอสำหรับเข้าถึงหน้าแอดมิน</p>
            <div class="d-flex justify-content-center gap-2 mt-4">
                <a href="index.php" class="btn btn-warning px-4 fw-bold">กลับหน้าหลัก</a>
                <button onclick="history.back()" class="btn btn-outline-light px-4">ย้อนกลับ</button>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// ✅ 2. จัดการตัวแปรเชื่อมต่อฐานข้อมูล
if (!isset($conn) && isset($condb)) { $conn = $condb; }

// --- 3. ดึงตัวเลขสรุปสำหรับการ์ด ---
$counts = ['pending_payments' => 0, 'total_receipts' => 0];

// นับรายการที่ยัง "รอตรวจสลิป"
$q1 = mysqli_query($conn, "SELECT COUNT(*) AS c FROM tbl_booking WHERE payment_status='pending'");
if ($q1) { $counts['pending_payments'] = (int)mysqli_fetch_assoc($q1)['c']; }

// นับจำนวนใบเสร็จทั้งหมด
$q2 = mysqli_query($conn, "SELECT COUNT(*) AS c FROM tbl_receipt");
if ($q2) { $counts['total_receipts'] = (int)mysqli_fetch_assoc($q2)['c']; }
?>

<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <title>Admin Dashboard | Deep D House</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="style.css">

    <style>
        /* 🚩 บทเรียนเรื่อง Sticky Footer & Admin Layout */
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            margin: 0;
            background-color: #0e0e0e;
            color: #fff;
            font-family: 'Kanit', sans-serif;
        }

        .main-content {
            flex: 1; /* ดัน Footer ลงล่างสุด */
            padding-top: 100px; /* เว้นระยะจาก Navbar เรืองแสง */
            padding-bottom: 50px;
        }

        .stat {
            font-size: 3rem;
            font-weight: 800;
            color: #f5a524; /* --gold */
            text-shadow: 0 0 15px rgba(245, 165, 36, 0.3);
        }

        .card {
            background: #151515 !important;
            border: 1px solid rgba(245, 165, 36, 0.1) !important;
            border-radius: 20px;
            transition: transform 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(245, 165, 36, 0.15) !important;
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
            <div class="text-center mb-5">
                <h2 class="fw-bold text-warning" style="font-size: 2.5rem;">🛠️ Admin Dashboard</h2>
                <p class="text-secondary mb-0">ระบบจัดการร้านอาหาร Deep D House สำหรับผู้ดูแลระบบ</p>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-12 col-md-6">
                    <div class="card p-4 text-center h-100">
                        <h5 class="text-warning fw-bold">💳 รายการรอตรวจสลิป</h5>
                        <div class="stat my-3"><?= number_format($counts['pending_payments']); ?></div>
                        <a href="admin_verify_payment.php" class="btn btn-warning w-100 fw-bold rounded-pill" style="color:#000;">ตรวจสอบสลิป</a>
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="card p-4 text-center h-100">
                        <h5 class="text-warning fw-bold">📜 ประวัติใบเสร็จทั้งหมด</h5>
                        <div class="stat my-3"><?= number_format($counts['total_receipts']); ?></div>
                        <a href="receipt_list.php" class="btn btn-outline-warning w-100 fw-bold rounded-pill">ดูประวัติใบเสร็จ</a>
                    </div>
                </div>
            </div>

            <div class="card p-4 mb-4 text-center">
                <h5 class="mb-4 text-warning fw-bold">⚡ เมนูด่วนสำหรับผู้ดูแลระบบ</h5>
                <div class="d-flex flex-wrap justify-content-center gap-3">
                    <a href="dashboard_summary.php" class="btn btn-warning fw-bold px-5 rounded-pill" style="color:#000;">
                        📊 ดูสรุปยอดขาย
                    </a>
                    <form method="post" action="index.php" onsubmit="return confirm('⚠️ ยืนยันการรีเซ็ตโต๊ะทั้งหมด?');">
                        <button type="submit" name="reset_tables" class="btn btn-danger fw-bold px-5 rounded-pill">
                            🔄 รีเซ็ตโต๊ะทั้งหมด
                        </button>
                    </form>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-12 col-lg-6">
                    <div class="card p-4 h-100">
                        <h5 class="text-warning fw-bold mb-3">📅 รายการจอง & การเงิน</h5>
                        <div class="d-grid gap-2">
                            <a class="btn btn-outline-light text-start p-3 rounded-3" href="admin_list.php">📋 รายการจองทั้งหมด</a>
                            <a class="btn btn-outline-light text-start p-3 rounded-3" href="admin_verify_payment.php">💳 จัดการตรวจสอบการชำระเงิน</a>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-6">
                    <div class="card p-4 h-100">
                        <h5 class="text-warning fw-bold mb-3">🪑 จัดการผังร้าน & โต๊ะ</h5>
                        <div class="d-grid gap-2">
                            <a class="btn btn-outline-light text-start p-3 rounded-3" href="dashboardtable.php">⚙️ แก้ไขโต๊ะ / เพิ่ม-ลบ</a>
                            <a class="btn btn-outline-light text-start p-3 rounded-3" href="detailtable.php">👁️ ดูโต๊ะในหน้าสำหรับลูกค้า</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <div class="container">
            © 2025 Deep D House · ระบบบริหารจัดการหลังบ้านออนไลน์
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>