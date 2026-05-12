<?php
require_once 'condb.php';
session_start();

// ✅ ตรวจสอบ id
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  die("ไม่พบรหัสการจอง");
}
$booking_no = (int)$_GET['id'];

// ✅ ดึงข้อมูลการจองจาก tbl_booking
$sql = "SELECT booking_name, booking_phone, booking_date, booking_time 
        FROM tbl_booking WHERE no = $booking_no LIMIT 1";
$result = mysqli_query($conn, $sql);

if ($result === false) {
  die("SQL Error: " . mysqli_error($conn));
}
if (mysqli_num_rows($result) === 0) {
  die("ไม่พบข้อมูลการจอง (no = {$booking_no})");
}
$booking = mysqli_fetch_assoc($result);

// ✅ ข้อมูล PromptPay
$promptpay_id = "0642064899";
// old: $amount = 150.00;
// old: $qr_amount = intval($amount);

$amount = 150.00;    // ยอดคงที่ที่ต้องการให้สแกน (บาท)
$qr_amount = number_format($amount, 2, '.', ''); // ให้เป็น "150.00" (สำคัญ: 2 ตำแหน่งทศนิยม)
$qr_url = "https://promptpay.io/{$promptpay_id}?amount={$qr_amount}"; // ลิงก์ที่มี amount

?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
  <title>💰 ชำระเงิน | Deep D House</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    :root {
      --gold: #f5a524;
      --gold-dark: #d9901d;
      --bg: #0b0b0b;
    }
    body {
      background: radial-gradient(circle at top, #111 0%, #000 100%);
      color: #fff;
      font-family: "Kanit", sans-serif;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2rem;
    }
    .card {
      background: linear-gradient(180deg, #1b1b1b 0%, #101010 100%);
      border: 1px solid rgba(245,165,36,0.2);
      border-radius: 20px;
      box-shadow: 0 10px 40px rgba(245,165,36,0.15);
      color: #fff;
      padding: 2rem 2.5rem;
      max-width: 550px;
      width: 100%;
      animation: fadeIn 0.8s ease;
    }
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(30px); }
      to { opacity: 1; transform: translateY(0); }
    }
    h3 {
      color: var(--gold);
      font-weight: 800;
      text-align: center;
      margin-bottom: 1.5rem;
      text-shadow: 0 0 10px rgba(245,165,36,0.4);
    }
    p strong { color: var(--gold-dark); }
    .qr-box {
      background: rgba(245,165,36,0.05);
      border: 1px solid rgba(245,165,36,0.25);
      border-radius: 12px;
      padding: 1rem;
      box-shadow: inset 0 0 15px rgba(245,165,36,0.1);
      text-align: center;
    }
    .qr-box img {
      width: 230px;
      border-radius: 10px;
      border: 2px solid rgba(245,165,36,0.3);
      transition: 0.25s;
    }
    .qr-box img:hover {
      transform: scale(1.04);
      border-color: var(--gold);
      box-shadow: 0 0 20px rgba(245,165,36,0.3);
    }
    label { color: var(--gold-dark); font-weight: 600; }
    .form-control {
      background: #fff;
      color: #000;
      border-radius: 10px;
      border: 1px solid #ccc;
    }
    .form-control:focus {
      border-color: var(--gold);
      box-shadow: 0 0 0 .25rem rgba(245,165,36,.25);
    }
    .btn-main {
      background: var(--gold);
      border: none;
      color: #000;
      font-weight: 700;
      border-radius: 12px;
      padding: 0.8rem;
      box-shadow: 0 4px 15px rgba(245,165,36,0.3);
      transition: all 0.2s;
    }
    .btn-main:hover {
      background: var(--gold-dark);
      transform: translateY(-1px);
    }
    footer {
      text-align: center;
      color: #aaa;
      font-size: 0.9rem;
      margin-top: 1.5rem;
    }
  </style>
</head>
<body>

  <div class="card">
    <h3>💰 ชำระเงินการจองโต๊ะ</h3>

    <div class="mb-3">
      <p><strong>ชื่อผู้จอง:</strong> <?= htmlspecialchars($booking['booking_name']) ?></p>
      <p><strong>เบอร์โทร:</strong> <?= htmlspecialchars($booking['booking_phone']) ?></p>
      <p><strong>วันที่จอง:</strong> <?= htmlspecialchars($booking['booking_date']) ?></p>
      <p><strong>เวลา:</strong> <?= htmlspecialchars($booking['booking_time']) ?></p>
      <p><strong>ยอดชำระ:</strong> <?= number_format($amount, 2) ?> บาท</p>
    </div>

    <div class="qr-box mb-4">
      <h5 class="mb-3 text-warning">สแกนจ่ายผ่าน PromptPay</h5>
      <img src="<?= $qr_url ?>" alt="PromptPay QR" onerror="this.src='fallback_qr.png'">
      <div class="mt-3">
        <a href="<?= $qr_url ?>" download class="btn btn-sm btn-outline-warning">⬇️ ดาวน์โหลด QR</a>
      </div>
    </div>

    <form action="upload_slip.php" method="post" enctype="multipart/form-data">
      <input type="hidden" name="booking_id" value="<?= $booking_no ?>">
      <div class="mb-3">
        <label for="slip">📷 อัปโหลดสลิปการโอน</label>
        <input type="file" name="slip" id="slip" class="form-control" accept="image/*" required>
      </div>
      <button type="submit" class="btn btn-main w-100">ส่งสลิปยืนยันการชำระเงิน</button>
    </form>

    <footer>© 2025 Deep D House · Secure Payment System</footer>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
