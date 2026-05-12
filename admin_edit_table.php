<?php
require_once 'condb.php'; // เรียกไฟล์เชื่อมฐานข้อมูล (เชื่อมต่อ MySQL ผ่านตัวแปร $conn)

// 🔹 ดึงข้อมูลโต๊ะที่ต้องการแก้ไขจาก id ที่รับมาทาง URL
$id  = intval($_GET['id']); // แปลงค่า id เป็นจำนวนเต็ม ป้องกัน SQL Injection เบื้องต้น
$res = mysqli_query($conn, "SELECT * FROM detailtable WHERE id=$id"); // ดึงข้อมูลโต๊ะจากตาราง detailtable ตาม id
if (!$res || mysqli_num_rows($res) == 0) { // ถ้า query ผิดพลาด หรือไม่พบข้อมูล
    die("ไม่พบโต๊ะที่ต้องการแก้ไข"); // หยุดโปรแกรมและแสดงข้อความ
}
$table = mysqli_fetch_assoc($res); // ดึงข้อมูลโต๊ะมาเก็บในตัวแปร $table (เป็น array)

// 🔸 ตรวจสอบว่ามีการกด submit ฟอร์มหรือไม่
if (isset($_POST['submit'])) {

    // รับค่าจากฟอร์มและทำการ escape เพื่อป้องกัน SQL Injection
    $name     = mysqli_real_escape_string($conn, $_POST['name']);  // ชื่อโต๊ะ
    $seats    = mysqli_real_escape_string($conn, $_POST['seats']); // จำนวนที่นั่ง
    $img_name = $table['img']; // ตั้งค่ารูปภาพเริ่มต้นเป็นรูปเดิม (ในกรณีที่ผู้ใช้ไม่อัปโหลดใหม่)

    // 🔹 ตรวจสอบว่ามีการอัปโหลดรูปภาพใหม่หรือไม่
    if (isset($_FILES['img']) && $_FILES['img']['error'] == 0) {
        $img_tmp       = $_FILES['img']['tmp_name'];  // ไฟล์ชั่วคราว
        $original_name = $_FILES['img']['name'];      // ชื่อไฟล์ต้นฉบับ
        $file_type     = mime_content_type($img_tmp); // ตรวจสอบ MIME type
        $file_size     = $_FILES['img']['size'];      // ขนาดไฟล์

        // อนุญาตเฉพาะไฟล์รูปตามประเภทที่กำหนด
        $allowed_types = ['image/jpeg','image/png','image/gif','image/webp'];
        if (in_array($file_type, $allowed_types) && $file_size <= 5*1024*1024) { // ตรวจสอบชนิดและขนาดไฟล์
            // 🔸 ลบรูปเก่าออกก่อน (หากมี)
            if (!empty($table['img']) && file_exists("images/" . $table['img'])) {
                unlink("images/" . $table['img']); // ลบไฟล์ออกจากโฟลเดอร์ images
            }

            // 🔹 ตั้งชื่อไฟล์ใหม่ให้ปลอดภัย (ใช้เวลา + ตัดอักขระพิเศษออก)
            $img_name = time() . '_' . preg_replace('/[^A-Za-z0-9.\-_]/','_', $original_name);

            // ย้ายไฟล์จาก temp ไปไว้ในโฟลเดอร์ images/
            move_uploaded_file($img_tmp, "images/" . $img_name);
        }
    }

    // 🔹 อัปเดตข้อมูลลงฐานข้อมูล
    $sql = "UPDATE detailtable SET name='$name', seats='$seats', img='$img_name' WHERE id=$id";
    if (mysqli_query($conn, $sql)) { // ถ้าอัปเดตสำเร็จ
        header("Location: dashboardtable.php?message=success"); // เด้งกลับหน้า Dashboard พร้อมข้อความสำเร็จ
        exit;
    } else {
        // ถ้าอัปเดตไม่สำเร็จ แสดง error
        $message = "เกิดข้อผิดพลาด: " . mysqli_error($conn);
    }
}
?>
<!-- “ไฟล์นี้เป็นหน้าสำหรับ แก้ไขรายละเอียดโต๊ะในระบบ Admin
เริ่มจากรับค่า id ของโต๊ะจาก URL → ดึงข้อมูลโต๊ะจากตาราง detailtable
จากนั้นเมื่อผู้ใช้กดบันทึก (POST) ระบบจะตรวจสอบข้อมูลที่กรอก รวมถึงตรวจสอบรูปภาพใหม่ที่อัปโหลด
ถ้ามีการอัปโหลดรูปใหม่ จะลบรูปเก่าและบันทึกชื่อไฟล์ใหม่ก่อนอัปเดตข้อมูลในฐานข้อมูล
สุดท้ายถ้าอัปเดตสำเร็จ จะ redirect กลับไปหน้า dashboardtable.php?message=success เพื่อแสดงผลลัพธ์” -->

<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>แก้ไขโต๊ะ | DeepD House</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body{background:#f5f5f5;color:#111;font-family:'Segoe UI',sans-serif;}
.form-container{background:#fff;padding:30px;border-radius:12px;max-width:600px;margin:auto;margin-top:50px;}
label{font-weight:700;color:#111;}
.btn-primary {background:#f5a524;border:none;color:#000;font-weight:600;}
.btn-primary:hover {background:#d9901d;}
.btn-warning {background:#f5a524;color:#000;border:none;font-weight:600;}
img{border-radius:8px;object-fit:cover;max-height:120px;}
</style>
</head>
<body>

<div class="form-container">
<h3 class="mb-4">แก้ไขโต๊ะ</h3>

<?php if(isset($message) && $message!=""): ?>
<div class="alert alert-danger"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data">
  <div class="mb-3">
    <label>ชื่อโต๊ะ</label>
    <input type="text" name="name" class="form-control" 
           value="<?= htmlspecialchars($table['name']) ?>" required>
  </div>

  <div class="mb-3">
    <label>จำนวนที่นั่ง</label>
    <input type="text" name="seats" class="form-control" 
           value="<?= htmlspecialchars($table['seats']) ?>" required>
  </div>

  <div class="mb-3">
    <label>รูปภาพ (อัปโหลดถ้าต้องการเปลี่ยน)</label>
    <input type="file" name="img" class="form-control">
    <img src="images/<?= htmlspecialchars($table['img']) ?>" class="mt-2" 
         onerror="this.src='https://placehold.co/120x120?text=No+Image';">
  </div>

  <button type="submit" name="submit" class="btn btn-primary w-100">บันทึกการแก้ไข</button>
  <a href="dashboardtable.php" class="btn btn-warning w-100 mt-2">กลับ Dashboard</a>
</form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
