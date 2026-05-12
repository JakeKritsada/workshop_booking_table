<?php
require_once 'condb.php'; // เรียกไฟล์เชื่อมต่อฐานข้อมูล ($conn)
session_start(); // เริ่มต้น session เพื่อให้เข้าถึงข้อมูลผู้ใช้ที่ล็อกอินไว้ เช่น เบอร์โทร ชื่อ

// ✅ ตรวจสอบว่า URL มีการส่ง id โต๊ะมาหรือไม่
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  die("❌ ไม่พบรหัสโต๊ะที่ต้องการจอง"); // ถ้าไม่มีหรือไม่ใช่ตัวเลข ให้หยุดการทำงานทันที
}

$table_id = intval($_GET['id']); // แปลงค่า id ให้เป็นตัวเลข (ป้องกัน SQL injection ระดับพื้นฐาน)

// ✅ ดึงข้อมูลโต๊ะจากฐานข้อมูลตาม id ที่ส่งมา
$query = "SELECT * FROM tbl_table WHERE id = $table_id";
$result = mysqli_query($conn, $query); // รันคำสั่ง SQL

// ตรวจสอบว่ามีข้อมูลหรือไม่
if (!$result || mysqli_num_rows($result) === 0) {
  die("❌ ไม่พบข้อมูลโต๊ะที่เลือก"); // ถ้าไม่มีข้อมูล ให้แสดงข้อความและหยุด
}
$table = mysqli_fetch_assoc($result); // เก็บข้อมูลโต๊ะเป็น array เพื่อใช้แสดงในฟอร์ม

// ✅ ดึงข้อมูลผู้ใช้จาก session (ที่ได้จากตอนล็อกอิน)
$user_phone = $_SESSION['phone'] ?? '';       // เบอร์โทรผู้ใช้
$user_name  = $_SESSION['fullname'] ?? 'ผู้ใช้ทั่วไป'; // ชื่อเต็ม (ถ้าไม่มีใน session ให้แสดง “ผู้ใช้ทั่วไป”)

// ✅ กำหนด timezone เป็นเวลาไทย เพื่อใช้ในค่าเริ่มต้นของวันและเวลา
date_default_timezone_set('Asia/Bangkok');
$current_date = date('Y-m-d'); // วันที่ปัจจุบัน เช่น 2025-10-20
$current_time = date('H:i');   // เวลา ณ ปัจจุบัน เช่น 11:35
?>

<!-- “ไฟล์นี้คือหน้า ฟอร์มสำหรับกรอกข้อมูลการจองโต๊ะ
เริ่มจากตรวจสอบว่าใน URL มี id ของโต๊ะที่ต้องการจองหรือไม่ ถ้าไม่มีหรือไม่ใช่ตัวเลข ระบบจะหยุดการทำงานทันทีเพื่อป้องกันความผิดพลาด
จากนั้นจะดึงข้อมูลของโต๊ะนั้นจากตาราง tbl_table แล้วแสดงในแบบฟอร์ม
ส่วนของผู้ใช้ ระบบจะดึงชื่อและเบอร์โทรจาก session ของคนที่ล็อกอินอยู่
นอกจากนี้ยังตั้งค่าเวลาเริ่มต้นเป็นวันและเวลาปัจจุบันของประเทศไทย
เมื่อผู้ใช้กรอกข้อมูลครบแล้วกด “ยืนยันการจอง” ฟอร์มจะส่งข้อมูลทั้งหมดไปที่ save_booking.php เพื่อบันทึกลงฐานข้อมูลจริง” -->
<!doctype html>
<html lang="th">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>จองโต๊ะ | Deep D House</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
      :root {
        --gold:#f5a524;
        --gold-2:#d9901d;
        --bg:#0b0b0b;
        --card:#151515;
      }

      body {
        background: radial-gradient(circle at top, #111 0%, #000 100%);
        color:#fff;
        font-family:"Kanit",sans-serif;
        min-height:100vh;
      }

      /* ===== NAVBAR ===== */
      .navbar-dark {
        background:#000;
        border-bottom:1px solid rgba(245,165,36,0.3);
        box-shadow:0 4px 16px rgba(245,165,36,0.2);
      }
      .navbar-brand { color:var(--gold)!important; font-weight:800; }
      .navbar-brand:hover { color:var(--gold-2)!important; }
      .navbar-dark .nav-link {
        color:#fff!important; font-weight:600; transition:color .2s;
      }
      .navbar-dark .nav-link:hover, .navbar-dark .nav-link.active {
        color:var(--gold)!important;
      }

      /* ===== กล่องจอง ===== */
      .booking-box {
        background:linear-gradient(180deg, #161616 0%, #0e0e0e 100%);
        border:1px solid rgba(245,165,36,0.2);
        border-radius:16px;
        box-shadow:0 10px 40px rgba(0,0,0,0.6);
        padding:2rem 2.5rem;
        animation: fadeInUp 0.8s ease;
      }

      @keyframes fadeInUp {
        from {opacity:0; transform:translateY(30px);}
        to {opacity:1; transform:translateY(0);}
      }

      h4 {
        color:var(--gold);
        font-weight:700;
        letter-spacing:.5px;
      }
      label {
        color:var(--gold-2);
        font-weight:600;
      }
      .form-control {
        background:#fff;
        color:#000;
        border-radius:10px;
      }
      .form-control:focus {
        border-color:var(--gold);
        box-shadow:0 0 0 .25rem rgba(245,165,36,.25);
      }

      /* ปุ่ม */
      .btn-success {
        background:var(--gold);
        border:none;
        color:#000;
        font-weight:700;
        border-radius:12px;
        box-shadow:0 4px 10px rgba(245,165,36,.4);
      }
      .btn-success:hover {
        background:var(--gold-2);
        transform:translateY(-1px);
      }
      .btn-secondary {
        background:#2a2a2a;
        border:none;
        color:#fff;
        border-radius:12px;
      }
      .btn-secondary:hover {
        background:#3a3a3a;
      }

      /* Alert */
      .alert-warning {
        background:rgba(245,165,36,0.05);
        border:1px solid rgba(245,165,36,0.3);
        color:var(--gold);
        border-radius:10px;
        font-weight:500;
      }

      /* Footer */
      footer {
        background:#000;
        color:#aaa;
        text-align:center;
        padding:14px;
        margin-top:60px;
        border-top:1px solid rgba(245,165,36,0.25);
        box-shadow:0 -6px 24px rgba(245,165,36,0.25);
      }
    </style>
  </head>

  <body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
      <div class="container-fluid">
        <a class="navbar-brand" href="index.php">Deep D House</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
          <ul class="navbar-nav me-auto mb-2 mb-lg-0">
            <li class="nav-item"><a class="nav-link" href="index.php">หน้าแรก</a></li>
            <li class="nav-item"><a class="nav-link active" href="index2.php">จองโต๊ะ</a></li>
            <li class="nav-item"><a class="nav-link" href="detailtable.php">ข้อมูลโต๊ะ</a></li>
            <li class="nav-item"><a class="nav-link" href="location.php">สาขา</a></li>
            <li class="nav-item"><a class="nav-link" href="admin.php">Admin</a></li>
          </ul>
        </div>
      </div>
    </nav>

    <!-- Content -->
    <div class="container py-5">
      <div class="col-md-8 mx-auto booking-box">
        <h4 class="text-center mb-4">รายละเอียดการจองโต๊ะ</h4>

        <div class="alert alert-warning text-center mb-4">
          📅 <b>หมายเหตุ:</b> เลือกและจองได้วันต่อวัน<br>หากเกิน <b>22:00 น.</b> ของวันนั้นจะถือเป็น Walk-in
        </div>

        <form action="save_booking.php" method="post">
          <input type="hidden" name="table_id" value="<?= (int)$table['id']; ?>">

          <div class="mb-3 row">
            <label class="col-sm-3 col-form-label">เลขโต๊ะ</label>
            <div class="col-sm-4">
              <input type="text" class="form-control" value="<?= htmlspecialchars($table['table_name']); ?>" readonly>
            </div>
          </div>

          <div class="mb-3 row">
            <label class="col-sm-3 col-form-label">ผู้จอง</label>
            <div class="col-sm-7">
              <input type="text" name="booking_name" class="form-control" required placeholder="ชื่อผู้จอง" minlength="2">
            </div>
          </div>

          <div class="mb-3 row">
            <label class="col-sm-3 col-form-label">วันที่</label>
            <div class="col-sm-5">
              <input type="date" id="booking_date" name="booking_date" class="form-control" value="<?= $current_date ?>" required>
            </div>
            <label class="col-sm-1 col-form-label">เวลา</label>
            <div class="col-sm-3">
              <input type="time" id="booking_time" name="booking_time" class="form-control" value="<?= $current_time ?>" required>
            </div>
          </div>

          <div class="mb-3 row">
            <label class="col-sm-3 col-form-label">เบอร์โทร</label>
            <div class="col-sm-7">
              <input type="text" name="booking_phone" class="form-control" value="<?= htmlspecialchars($user_phone); ?>" readonly>
            </div>
          </div>

          <div class="mb-4 row">
            <label class="col-sm-3 col-form-label">ผู้บันทึก</label>
            <div class="col-sm-3">
              <input type="text" name="booking_staff" class="form-control" value="<?= htmlspecialchars($user_name); ?>" readonly>
            </div>
          </div>

          <div class="text-center">
            <button type="submit" class="btn btn-success px-4">✅ ยืนยันการจอง</button>
            <a href="index2.php" class="btn btn-secondary px-4 ms-2">ยกเลิก</a>
          </div>
        </form>
      </div>
    </div>

    <!-- Footer -->
    <footer>© 2025 Deep D House · ระบบจองโต๊ะอาหารออนไลน์</footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- อัปเดตเวลาเรียลไทม์ -->
    <script>
      setInterval(() => {
        const now = new Date();
        const date = now.toISOString().split('T')[0];
        const time = now.toTimeString().slice(0,5);
        document.getElementById('booking_date').value = date;
        document.getElementById('booking_time').value = time;
      }, 1000);
    </script>
  </body>
</html>
