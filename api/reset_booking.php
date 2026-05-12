<?php
// เรียกใช้งานฐานข้อมูล
require_once __DIR__.'/condb.php';date_default_timezone_set('Asia/Bangkok');

$hourNow = date('H');           // ชั่วโมงปัจจุบัน
$today = date('Y-m-d');         // วันปัจจุบัน

// --- Reset โต๊ะทุกวันหลังตี 3 ---
if($hourNow >= 3){
    // ลบการจองเก่าของวันก่อน
    $delete = mysqli_query($conn, "DELETE FROM tbl_booking WHERE booking_date < '$today'");
    if(!$delete){
        error_log("Reset Booking Error: " . mysqli_error($conn));
    }

    // รีเซ็ตสถานะโต๊ะเป็นว่าง
    $reset = mysqli_query($conn, "UPDATE tbl_table SET table_status=0");
    if(!$reset){
        error_log("Reset Table Status Error: " . mysqli_error($conn));
    }
}

// --- Output สำหรับ debug (สามารถ comment ออกได้) ---
echo "Reset ระบบเรียบร้อยแล้ว: ".date('Y-m-d H:i:s');
?>
