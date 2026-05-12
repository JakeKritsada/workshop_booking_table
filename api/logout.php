<?php
session_start(); // เริ่มต้น session เพื่อให้สามารถเข้าถึงข้อมูล session เดิมได้

session_unset(); // ลบค่าตัวแปรทั้งหมดที่อยู่ใน session (เช่น user_id, role, phone ฯลฯ)

session_destroy(); // ทำลาย session ทั้งหมด (รวมถึง ID ของ session ด้วย) เพื่อออกจากระบบอย่างสมบูรณ์

header('Location: login.php'); // หลังจากออกจากระบบแล้ว ให้เปลี่ยนหน้าไปยังหน้าเข้าสู่ระบบ (login.php)

exit; // หยุดการทำงานของสคริปต์ทันที (ป้องกันโค้ดส่วนอื่นรันต่อ)
?>
