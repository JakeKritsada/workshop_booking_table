<?php
require_once 'condb.php'; // เรียกไฟล์เชื่อมฐานข้อมูล (ตัวแปร $conn)
session_start(); // เริ่มต้น session เพื่อใช้ flash message และเก็บข้อมูลการจอง
date_default_timezone_set('Asia/Bangkok'); // ตั้ง timezone ให้เป็นเวลาไทย
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); 
// สั่งให้ mysqli แจ้ง error แบบ Exception ชัดเจน (ช่วยให้ try/catch ดักได้ง่ายขึ้น)

$table_id      = isset($_POST['table_id'])      ? (int)$_POST['table_id'] : 0; // id ของโต๊ะที่เลือก
$booking_name  = trim($_POST['booking_name'] ?? ''); // ชื่อผู้จอง
$booking_date  = trim($_POST['booking_date'] ?? ''); // วันที่จอง
$booking_time  = trim($_POST['booking_time'] ?? ''); // เวลาจอง
$booking_phone = trim($_POST['booking_phone'] ?? ''); // เบอร์โทรผู้จอง
$booking_staff = trim($_POST['booking_staff'] ?? ''); // ชื่อพนักงานที่บันทึก (หรือชื่อผู้ใช้)


if ($table_id <= 0 || $booking_name === '' || $booking_date === '' || $booking_time === '') {
  $_SESSION['flash_err'] = '⚠️ กรุณากรอกข้อมูลให้ครบถ้วน'; // แจ้งเตือนผ่าน session
  header("Location: booking.php?id={$table_id}&act=booking"); // ส่งกลับไปหน้าฟอร์มเดิม
  exit;
}
// ถ้าข้อมูลไม่ครบ เช่น ไม่มีชื่อ วันที่ หรือเวลา → ไม่ให้บันทึก
// ส่งกลับไปหน้าเดิมพร้อมข้อความเตือน

mysqli_begin_transaction($conn); // เริ่มต้นธุรกรรม (transaction)
// ใช้ transaction เพื่อให้แน่ใจว่าขั้นตอนบันทึกทั้งหมดสำเร็จครบ
// ถ้ามี error จะ rollback กลับ ไม่ให้ข้อมูลบางส่วนค้างในฐานข้อมูล



// ขั้นตอนหลัก (บันทึกการจอง)
try {
  // ✅ (a) บันทึกการจอง
  $sql = "INSERT INTO tbl_booking 
            (table_id, booking_name, booking_date, booking_time, booking_phone, booking_staff, payment_status)
          VALUES (?, ?, ?, ?, ?, ?, 'unpaid')";


// ใช้ prepared statement (query ปลอดภัย) ป้องกัน SQL Injection
// ค่า payment_status ถูกตั้งค่าเริ่มต้นเป็น 'unpaid' (ยังไม่จ่าย)
// ผูกค่าและ execute

  $stmt = mysqli_prepare($conn, $sql);
  mysqli_stmt_bind_param($stmt, "isssss", $table_id, $booking_name, $booking_date, $booking_time, $booking_phone, $booking_staff);
  mysqli_stmt_execute($stmt);
// isssss หมายถึง: 1 ตัวเลข (i) และ 5 ตัวอักษร (s)
// บันทึกข้อมูลจากฟอร์มเข้าสู่ tbl_booking

  $last_no = mysqli_insert_id($conn); // ดึงหมายเลข (no) ล่าสุดที่เพิ่ง insert


  // ❌ ไม่เปลี่ยนสถานะโต๊ะตอนนี้
  mysqli_commit($conn);

  // ✅ เก็บใน session สำหรับ payment
  $_SESSION['booking_data'] = [
    'no'            => $last_no,
    'table_id'      => $table_id,
    'booking_name'  => $booking_name,
    'booking_date'  => $booking_date,
    'booking_time'  => $booking_time,
    'booking_phone' => $booking_phone,
    'booking_staff' => $booking_staff,
    'note'          => 'สร้างคำสั่งจองแล้ว (ยังไม่ชำระ)'
  ];
// เดิมระบบอาจเคย update table_status=1 ที่ tbl_table แต่ตอนนี้ยังไม่ทำ
// (รอให้ผู้ใช้ชำระเงินหรือแอดมินอนุมัติค่อยอัปเดตสถานะโต๊ะภายหลัง)
  header("Location: payment.php?id={$last_no}");
  exit;

} catch (Exception $e) {
  mysqli_rollback($conn); // ย้อนธุรกรรมกลับ (ไม่ให้ข้อมูลที่ insert ค้าง)
  $_SESSION['flash_err'] = "❌ เกิดข้อผิดพลาด: " . $e->getMessage(); // เก็บข้อความ error
  header("Location: booking.php?id={$table_id}&act=booking"); // กลับไปหน้าเดิม
  exit;
}
?>