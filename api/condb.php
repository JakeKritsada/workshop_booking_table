<?php
// condb.php — เชื่อมต่อฐานข้อมูลครั้งเดียว แบบ OOP

// ป้องกัน include ซ้ำ
if (isset($conn)) return;

$conn = new mysqli('localhost', 'root', '', 'workshop_booking_table');

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die('เชื่อมต่อฐานข้อมูลไม่สำเร็จ: ' . $conn->connect_error);
}

// ตั้งค่า charset รองรับภาษาไทย
$conn->set_charset('utf8mb4');