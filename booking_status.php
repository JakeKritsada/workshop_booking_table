<?php
require_once 'condb.php'; 
session_start();

if (!isset($conn) && isset($condb)) { $conn = $condb; }

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ไม่พบรหัสการจอง");
}

$id = intval($_GET['id']);
$sql = "SELECT * FROM tbl_booking WHERE no = $id";
$result = mysqli_query($conn, $sql);
$booking = mysqli_fetch_assoc($result);

if (!$booking) {
    die("ไม่พบข้อมูลการจองนี้");
}

$amount = 150.00;
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📋 สถานะการจองโต๊ะ | Deep D House</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    
    <style>
        /* จัดโครงสร้างให้ Footer อยู่ล่างสุดเสมอ */
        body {
            background-color: #0e0e0e;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            margin: 0;
            font-family: 'Kanit', sans-serif;
        }

        .main-wrapper {
            flex: 1; /* ขยายพื้นที่เพื่อดัน footer ลงไป */
            padding-bottom: 50px;
        }

        .card-custom {
            max-width: 600px;
            margin: 40px auto;
            padding: 30px;
            border-radius: 25px;
            background: #151515;
            border: 1px solid rgba(245, 165, 36, 0.15);
            box-shadow: 0 15px 35px rgba(0,0,0,0.5);
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            padding-bottom: 8px;
        }

        .status-pill {
            padding: 10px 25px;
            border-radius: 50px;
            font-weight: 700;
            display: inline-block;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        footer {
            background-color: #000;
            color: #666;
            text-align: center;
            padding: 25px 0;
            font-size: 0.9rem;
            border-top: 1px solid rgba(245, 165, 36, 0.1);
        }

        .text-gold { color: #f5a524 !important; }
    </style>
</head>
<body>

    <?php include 'navbar.php'; ?>

    <!-- ส่วนเนื้อหาหลัก -->
    <div class="main-wrapper">
        <div class="container">
            <div class="card-custom">
                
                <h3 class="text-center text-gold mb-4 fw-bold">📋 สถานะการจองโต๊ะ</h3>

                <!-- ส่วนข้อมูลการจอง -->
                <div class="p-4 mb-4" style="background: rgba(255,255,255,0.03); border-radius: 20px;">
                    <div class="info-item">
                        <span class="text-secondary">ชื่อผู้จอง:</span>
                        <span class="text-white fw-bold"><?= htmlspecialchars($booking['booking_name']) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="text-secondary">เบอร์โทร:</span>
                        <span class="text-white"><?= htmlspecialchars($booking['booking_phone']) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="text-secondary">วันที่จอง:</span>
                        <span class="text-white"><?= htmlspecialchars($booking['booking_date']) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="text-secondary">เวลา:</span>
                        <span class="text-white"><?= htmlspecialchars($booking['booking_time']) ?></span>
                    </div>
                    <div class="info-item border-0 mt-3 pt-2">
                        <span class="text-gold fw-bold">ยอดชำระ:</span>
                        <span class="text-gold fw-bold h4 mb-0"><?= number_format($amount, 2) ?> บาท</span>
                    </div>
                </div>

                <!-- ส่วนสถานะการชำระเงิน -->
                <div class="text-center mb-5">
                    <h5 class="text-gold mb-3 fw-semibold">สถานะการชำระเงิน</h5>
                    <?php if ($booking['payment_status'] == 'pending'): ?>
                        <div class="status-pill" style="background: rgba(255, 193, 7, 0.15); color: #ffc107; border: 1px solid #ffc107;">
                            ⌛ รอตรวจสอบ
                        </div>
                    <?php elseif ($booking['payment_status'] == 'paid'): ?>
                        <div class="status-pill" style="background: rgba(40, 167, 69, 0.15); color: #28a745; border: 1px solid #28a745;">
                            ✅ ชำระเงินแล้ว
                        </div>
                    <?php else: ?>
                        <div class="status-pill" style="background: rgba(220, 53, 69, 0.15); color: #dc3545; border: 1px solid #dc3545;">
                            ❌ ไม่ผ่านการตรวจสอบ
                        </div>
                    <?php endif; ?>
                </div>

                <!-- ส่วนรูปสลิป -->
                <?php if (!empty($booking['slip_img'])): ?>
                <div class="text-center mb-5">
                    <h5 class="text-gold mb-3 fw-semibold">สลิปการชำระเงิน</h5>
                    <div class="d-inline-block p-2" style="background: #000; border-radius: 20px; border: 2px solid #f5a524;">
                        <img src="<?= htmlspecialchars($booking['slip_img']) ?>" 
                             alt="สลิปการชำระเงิน" 
                             style="max-width: 280px; width: 100%; height: auto; border-radius: 15px;">
                    </div>
                    <p class="mt-3 text-secondary small">📸 สลิปที่คุณอัปโหลดในระบบ</p>
                </div>
                <?php endif; ?>

                <!-- ปุ่มดำเนินการ -->
                <div class="text-center d-grid gap-2 d-sm-flex justify-content-sm-center">
                    <?php if ($booking['payment_status'] == 'paid'): ?>
                        <a href="receipt.php?id=<?= $booking['no'] ?>" class="btn btn-success btn-lg px-5 fw-bold rounded-pill">ดูใบเสร็จ</a>
                    <?php elseif ($booking['payment_status'] == 'rejected'): ?>
                        <a href="payment.php?id=<?= $booking['no'] ?>" class="btn btn-warning btn-lg px-5 fw-bold rounded-pill">อัปโหลดสลิปใหม่</a>
                    <?php else: ?>
                        <button class="btn btn-outline-secondary btn-lg px-4 rounded-pill" disabled>⌛ รอแอดมินตรวจสอบ</button>
                    <?php endif; ?>
                </div>

                <!-- แถบเมนูย้อนกลับ -->
                <div class="mt-5 pt-3 border-top border-secondary border-opacity-25 d-flex justify-content-center gap-3">
                    <a href="index.php" class="btn btn-sm btn-outline-light px-3 rounded-pill">🏠 กลับหน้าหลัก</a>
                    <a href="my_bookings.php" class="btn btn-sm btn-outline-warning px-3 rounded-pill">📜 ประวัติการจอง</a>
                </div>

            </div> <!-- /card -->
        </div> <!-- /container -->
    </div> <!-- /main-wrapper -->

    <!-- Footer ล่างสุดเสมอ -->
    <footer>
        <div class="container">
© 2025 Deep D House · ระบบจองโต๊ะอาหารออนไลน์        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>