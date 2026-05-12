<?php
require_once 'condb.php'; // เรียกไฟล์เชื่อมต่อฐานข้อมูล (ตัวแปร $conn)
session_start(); // เริ่ม session เพื่อให้เข้าถึงข้อมูลผู้ใช้หรือตรวจสอบสิทธิ์


// ✅ รองรับทั้ง $conn / $condb
if (!isset($conn) && isset($condb)) { $conn = $condb; }

// // รับค่า id จาก URL เช่น receipt.php?id=5
// ตรวจสอบว่าเป็นตัวเลขจริง ไม่ใช่ string หรือ script
// ถ้าไม่มีหรือไม่ใช่ตัวเลข → หยุดโปรแกรมทันที
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  die("❌ ไม่พบรหัสการจอง");
}
$id = intval($_GET['id']);

// // ✅ ดึงข้อมูลการจองดึงข้อมูลการจองจากตาราง tbl_booking ตามรหัสใบจอง (no)
// ถ้าไม่พบข้อมูล → แสดงข้อความและหยุดทำงาน
// ถ้าพบ → เก็บรายละเอียดไว้ใน $booking_data เช่น booking_name, booking_date, payment_status
$q_booking = mysqli_query($conn, "SELECT * FROM tbl_booking WHERE no = $id LIMIT 1");
if (!$q_booking || mysqli_num_rows($q_booking) == 0) {
  die("❌ ไม่พบข้อมูลการจองนี้");
}
$booking_data = mysqli_fetch_assoc($q_booking);

// ✅ ตรวจสอบว่ามีใบเสร็จอยู่แล้วหรือยัง
$q_receipt = mysqli_query($conn, "SELECT * FROM tbl_receipt WHERE booking_no = $id LIMIT 1");
if ($q_receipt && mysqli_num_rows($q_receipt) > 0) {
  $receipt_data = mysqli_fetch_assoc($q_receipt);
} 
// ❌ ถ้ายังไม่มีใบเสร็จ → สร้างใหม่ทันที
else {
  $receipt_code = "RCP-" . date("YmdHis"); // สร้างรหัสใบเสร็จ เช่น RCP-20251020142035
  $amount = isset($booking_data['total']) ? $booking_data['total'] : 0; // ยอดรวม
  $slip_img = $booking_data['slip_img'] ?? null; // ภาพสลิป (ถ้ามี)
// ใช้เวลา (timestamp) ต่อท้าย RCP- เพื่อให้รหัสไม่ซ้ำ
// ถ้าในข้อมูลการจองมีค่ายอด (total) จะนำมาใส่ในใบเสร็จ
// ถ้ามีภาพสลิป (slip_img) จากตอนจ่ายเงินจะนำมาเก็บด้วย

// 💾 บันทึกใบเสร็จใหม่ลงฐานข้อมูล
  $insert = "INSERT INTO tbl_receipt (booking_no, receipt_code, total_amount, slip_img)
             VALUES ($id, '$receipt_code', $amount, '$slip_img')";
  mysqli_query($conn, $insert);
// เพิ่มข้อมูลใบเสร็จใหม่เข้าไปในตาราง tbl_receipt
// เชื่อมกับ booking_no เพื่อให้รู้ว่าใบเสร็จนี้มาจากการจองไหน

// 📦 สร้างตัวแปร $receipt_data สำหรับแสดงผล
  $receipt_data = [
    'receipt_code' => $receipt_code,
    'receipt_date' => date('Y-m-d H:i:s'),
    'total_amount' => $amount,
    'slip_img'     => $slip_img
  ];
}
?>
<!-- สรุปสั้นก่อนสอบ (5 บรรทัด)
ตรวจสอบ id การจองจาก URL
ดึงข้อมูลการจองจาก tbl_booking
ตรวจใน tbl_receipt ว่ามีใบเสร็จหรือยัง
ถ้าไม่มี → สร้างใบเสร็จใหม่และบันทึกลงฐานข้อมูล
แสดงรายละเอียดใบเสร็จ + ปุ่มดาวน์โหลด PDF -->
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>🧾 ใบเสร็จการจอง | Deep D House</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    :root {
      --gold:#f5a524;
      --gold-dark:#d9901d;
      --bg:#0b0b0b;
    }
    body {
      background: radial-gradient(circle at top, #111 0%, #000 100%);
      color:#fff;
      font-family:'Kanit',sans-serif;
      min-height:100vh;
      display:flex;
      flex-direction:column;
    }

    /* Navbar */
    .navbar-dark {
      background:#000;
      box-shadow:0 4px 12px rgba(245,165,36,.25);
      border-bottom:1px solid rgba(245,165,36,.2);
    }
    .navbar-brand {
      color:var(--gold)!important;
      font-weight:800;
      letter-spacing:.5px;
      text-transform:uppercase;
    }
    .navbar-brand:hover { color:var(--gold-dark)!important; }
    .navbar-dark .nav-link {
      color:#fff!important;
      font-weight:700;
      transition:.25s;
    }
    .navbar-dark .nav-link:hover,
    .navbar-dark .nav-link.active {
      color:var(--gold)!important;
    }

    /* Receipt Card */
    .receipt-card {
      background: linear-gradient(180deg, #1b1b1b 0%, #101010 100%);
      border:1px solid rgba(245,165,36,.25);
      border-radius:20px;
      box-shadow:0 10px 40px rgba(245,165,36,.15);
      padding:2rem 2.5rem;
      color:#fff;
      max-width:650px;
      margin:auto;
      margin-top:120px;
      margin-bottom:80px;
      animation: fadeIn .8s ease;
    }
    @keyframes fadeIn { from{opacity:0;transform:translateY(30px);} to{opacity:1;transform:translateY(0);} }

    h2 {
      color:var(--gold);
      font-weight:800;
      text-align:center;
      margin-bottom:1rem;
      text-shadow:0 0 12px rgba(245,165,36,.4);
    }
    hr { border-color:rgba(245,165,36,.2); }

    .receipt-details p { margin:.4rem 0; font-size:1.05rem; }
    .receipt-details strong { color:var(--gold-dark); }

    /* Slip */
    .slip-preview img {
      border:2px solid rgba(245,165,36,.3);
      border-radius:12px;
      box-shadow:0 0 20px rgba(245,165,36,.15);
      transition:.3s;
      max-height:400px;
    }
    .slip-preview img:hover {
      transform:scale(1.03);
      border-color:var(--gold);
      box-shadow:0 0 30px rgba(245,165,36,.3);
    }

    /* Buttons */
    .btn-main {
      background:var(--gold);
      border:none;
      color:#000;
      font-weight:700;
      border-radius:12px;
      padding:.6rem 1.4rem;
      box-shadow:0 4px 12px rgba(245,165,36,.3);
      transition:.25s;
    }
    .btn-main:hover { background:var(--gold-dark); transform:translateY(-1px); }
    .btn-danger { border:none; border-radius:12px; }

    /* Footer */
    footer {
      background:#000;
      color:#aaa;
      text-align:center;
      padding:12px;
      font-size:.9rem;
      border-top:1px solid rgba(245,165,36,.25);
      box-shadow:0 -6px 24px rgba(245,165,36,.25);
      margin-top:auto;
    }
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark fixed-top">
  <div class="container-fluid">
    <a class="navbar-brand" href="index.php">Deep D House</a>
    <button class="navbar-toggler bg-warning" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="index.php">หน้าแรก</a></li>
        <li class="nav-item"><a class="nav-link" href="index2.php">จองโต๊ะ</a></li>
        <li class="nav-item"><a class="nav-link" href="detailtable.php">ข้อมูลโต๊ะ</a></li>
        <li class="nav-item"><a class="nav-link" href="location.php">สาขา</a></li>
        <li class="nav-item"><a class="nav-link active" href="#">ใบเสร็จ</a></li>
        <li class="nav-item"><a class="nav-link" href="admin.php">Admin</a></li>
      </ul>
    </div>
  </div>
</nav>

<!-- Receipt -->
<div class="container">
  <div id="receipt" class="receipt-card">
    <h2>🧾 ใบเสร็จการจองโต๊ะ</h2>
    <hr>

    <?php if ($booking_data): ?>
      <div class="receipt-details">
        <p><strong>เลขที่ใบเสร็จ:</strong> <?= htmlspecialchars($receipt_data['receipt_code']) ?></p>
        <p><strong>วันที่ออกใบเสร็จ:</strong> <?= htmlspecialchars($receipt_data['receipt_date']) ?></p>
        <p><strong>เลขโต๊ะ:</strong> <?= htmlspecialchars($booking_data['table_id']) ?></p>
        <p><strong>ชื่อผู้จอง:</strong> <?= htmlspecialchars($booking_data['booking_name']) ?></p>
        <p><strong>วันที่จอง:</strong> <?= htmlspecialchars($booking_data['booking_date']) ?></p>
        <p><strong>เวลา:</strong> <?= htmlspecialchars($booking_data['booking_time']) ?></p>
        <p><strong>เบอร์โทร:</strong> <?= htmlspecialchars($booking_data['booking_phone']) ?></p>
        <p><strong>ยอดชำระ:</strong> <?= number_format($receipt_data['total_amount'],2) ?> บาท</p>
        <p><strong>สถานะการชำระเงิน:</strong>
          <?php if ($booking_data['payment_status']=='paid'): ?>
            <span class="text-success fw-bold">✅ ชำระเงินแล้ว</span>
          <?php elseif ($booking_data['payment_status']=='pending'): ?>
            <span class="text-warning fw-bold">⌛ รอตรวจสอบ</span>
          <?php else: ?>
            <span class="text-danger fw-bold">❌ ไม่ผ่านการตรวจสอบ</span>
          <?php endif; ?>
        </p>
        <?php if (!empty($booking_data['booking_staff'])): ?>
          <p><strong>พนักงานที่รับจอง:</strong> <?= htmlspecialchars($booking_data['booking_staff']) ?></p>
        <?php endif; ?>
      </div>

      <?php if (!empty($receipt_data['slip_img'])): ?>
      <div class="text-center mt-4 slip-preview">
        <h5 class="text-warning fw-bold mb-2">📸 สลิปการโอนเงิน</h5>
        <img src="<?= htmlspecialchars($receipt_data['slip_img']) ?>" alt="สลิปการโอน" class="img-fluid">
      </div>
      <?php endif; ?>

      <div class="text-center mt-4">
        <a href="index.php" class="btn btn-main me-2">⬅ กลับหน้าหลัก</a>
        <a href="my_bookings.php" class="btn btn-outline-light me-2">📋 ดูประวัติของฉัน</a>
        <button id="downloadPDF" class="btn btn-danger fw-bold">🧾 บันทึกเป็น PDF</button>
      </div>
    <?php else: ?>
      <div class="alert alert-danger text-center mt-3">❌ ไม่พบข้อมูลการจอง</div>
    <?php endif; ?>
  </div>
</div>

<footer>
  &copy; 2025 Deep D House. All rights reserved.
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script>
document.getElementById("downloadPDF")?.addEventListener("click", () => {
  const { jsPDF } = window.jspdf;
  const receipt = document.getElementById("receipt");
  html2canvas(receipt, { scale: 2 }).then(canvas => {
    const imgData = canvas.toDataURL("image/png");
    const pdf = new jsPDF("p", "mm", "a4");
    const pdfWidth = pdf.internal.pageSize.getWidth();
    const pdfHeight = (canvas.height * pdfWidth) / canvas.width;
    pdf.addImage(imgData, "PNG", 0, 0, pdfWidth, pdfHeight);
    pdf.save("receipt.pdf");
  });
});
</script>
</body>
</html>
