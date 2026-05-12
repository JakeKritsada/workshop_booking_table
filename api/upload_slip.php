<?php
require_once 'condb.php';
session_start();

date_default_timezone_set('Asia/Bangkok');

// ✅ 1. ตรวจสอบข้อมูลจากฟอร์ม
if (empty($_POST['booking_id']) || empty($_FILES['slip']['name'])) {
  $_SESSION['flash_err'] = "❌ กรุณาแนบสลิปก่อนดำเนินการ";
  header("Location: payment.php?id=" . ($_POST['booking_id'] ?? 0));
  exit;
}

$booking_id = (int)$_POST['booking_id'];
$file = $_FILES['slip'];

// ✅ 2. สร้างโฟลเดอร์อัปโหลด (ถ้ายังไม่มี)
$target_dir = "uploads/slips/";
if (!is_dir($target_dir)) {
  mkdir($target_dir, 0777, true);
}

// ✅ 3. ตรวจสอบชนิดไฟล์
$ext = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
$allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
if (!in_array($ext, $allowed_types)) {
  $_SESSION['flash_err'] = "⚠️ อนุญาตเฉพาะไฟล์ JPG, PNG, GIF, WEBP เท่านั้น";
  header("Location: payment.php?id=$booking_id");
  exit;
}

// ✅ 4. ตั้งชื่อไฟล์ใหม่แบบไม่ซ้ำ
$new_filename = "slip_" . $booking_id . "_" . time() . "." . $ext;
$target_file = $target_dir . $new_filename;

// ✅ 5. ย้ายไฟล์ไปโฟลเดอร์จริง
if (!move_uploaded_file($file["tmp_name"], $target_file)) {
  $_SESSION['flash_err'] = "❌ ไม่สามารถอัปโหลดไฟล์ได้ กรุณาลองใหม่อีกครั้ง";
  header("Location: payment.php?id=$booking_id");
  exit;
}

// ✅ 6. บันทึก path ลงฐานข้อมูล + เปลี่ยนสถานะเป็น pending
$sql = "UPDATE tbl_booking 
        SET slip_img = ?, payment_status = 'pending'
        WHERE no = ?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "si", $target_file, $booking_id);
$ok = mysqli_stmt_execute($stmt);

// ✅ 7. ถ้าอัปโหลดสำเร็จ → เปลี่ยนสถานะโต๊ะเป็น “รออนุมัติ (2)”
if ($ok) {
  $update_table = "
    UPDATE tbl_table 
    SET table_status = 2 
    WHERE id = (SELECT table_id FROM tbl_booking WHERE no = ?)
  ";
  $stmt2 = mysqli_prepare($conn, $update_table);
  mysqli_stmt_bind_param($stmt2, "i", $booking_id);
  mysqli_stmt_execute($stmt2);

  $_SESSION['flash_ok'] = "📸 อัปโหลดสลิปเรียบร้อยแล้ว รอการตรวจสอบจากแอดมิน";
  header("Location: booking_status.php?id=$booking_id");
} else {
  $_SESSION['flash_err'] = "❌ เกิดข้อผิดพลาดในการบันทึกข้อมูล";
  header("Location: payment.php?id=$booking_id");
}

exit;
?>
