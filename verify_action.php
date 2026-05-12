<?php
require_once 'condb.php';
session_start();

// รองรับทั้ง $conn / $condb
if (!isset($conn) && isset($condb)) {
  $conn = $condb;
}

$no  = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$act = $_GET['act'] ?? '';

if ($no <= 0 || $act === '') {
  die("❌ ข้อมูลไม่ถูกต้อง (id หรือ act หาย)");
}

try {
  // ✅ เริ่มธุรกรรม
  mysqli_begin_transaction($conn);

  // ✅ ดึงข้อมูลการจอง
  $q = mysqli_query($conn, "SELECT * FROM tbl_booking WHERE no = $no LIMIT 1");
  $bk = mysqli_fetch_assoc($q);
  if (!$bk) {
    throw new Exception("ไม่พบข้อมูลการจองนี้");
  }

  $table_id = (int)$bk['table_id'];

  // ✅ กรณีอนุมัติการชำระเงิน
  if ($act === 'approve') {

    // อัปเดตสถานะการจอง
    mysqli_query($conn, "UPDATE tbl_booking SET payment_status = 'paid' WHERE no = $no");

    // ตั้งสถานะโต๊ะเป็น “ไม่ว่าง”
    mysqli_query($conn, "UPDATE tbl_table SET table_status = 1 WHERE id = $table_id");

    // ✅ สร้างใบเสร็จใหม่
    $receipt_code = 'RCP-' . date('YmdHis');

    // ✅ ดึงราคาจาก tbl_booking หรือใช้ราคาคงที่ 50 บาท
    $amount = 0.00;
    if (isset($bk['total']) && $bk['total'] > 0) {
      $amount = (float)$bk['total'];
    } elseif (isset($bk['price']) && $bk['price'] > 0) {
      $amount = (float)$bk['price'];
    } else {
      $amount = 150.00; // 💰 กำหนดราคาคงที่
    }

    // ✅ เตรียมข้อมูลสลิป
    $slip_img = mysqli_real_escape_string($conn, $bk['slip_img'] ?? '');

    // ✅ บันทึกข้อมูลใบเสร็จ
    $sql = "
      INSERT INTO tbl_receipt (booking_no, receipt_code, total_amount, slip_img, receipt_date)
      VALUES ($no, '$receipt_code', $amount, '$slip_img', NOW())
    ";
    if (!mysqli_query($conn, $sql)) {
      throw new Exception("บันทึกใบเสร็จไม่สำเร็จ: " . mysqli_error($conn));
    }

    // ✅ ยืนยันการทำรายการ
    mysqli_commit($conn);
    $_SESSION['flash_ok'] = "✅ อนุมัติการชำระเงินสำเร็จ โต๊ะถูกตั้งเป็นไม่ว่างแล้ว";
    header("Location: receipt.php?id=$no");
    exit;
  }

  // ❌ กรณีปฏิเสธการชำระเงิน
  if ($act === 'reject') {
    mysqli_query($conn, "UPDATE tbl_booking SET payment_status = 'rejected' WHERE no = $no");
    mysqli_query($conn, "UPDATE tbl_table SET table_status = 0 WHERE id = $table_id");

    mysqli_commit($conn);
    $_SESSION['flash_err'] = "❌ ปฏิเสธการชำระเงิน และคืนโต๊ะเรียบร้อยแล้ว";
    header("Location: admin_verify_payment.php");
    exit;
  }

  throw new Exception("คำสั่งไม่ถูกต้อง (act=$act)");

} catch (Throwable $e) {
  // ❗ กรณีเกิด error
  mysqli_rollback($conn);
  $_SESSION['flash_err'] = "เกิดข้อผิดพลาด: " . $e->getMessage();
  header("Location: admin_verify_payment.php");
  exit;
}
?>
