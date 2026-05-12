<?php
if (isset($conn)) return;

// นำข้อมูลจากหน้าจอ TiDB ของคุณมาใส่ตรงนี้
$hostname = 'gateway01.ap-southeast-1.prod.aws.tidbcloud.com'; // คัดลอกจากช่อง Host
$username = '2sPFaH4bRb7ouXQ.root'; // คัดลอกจากช่อง User
$password = 'รหัสผ่านที่คุณตั้งไว้ตอนกด Reset Password'; // ใส่รหัสผ่านที่คุณตั้งเอง
$dbname   = 'workshop_booking'; // ชื่อฐานข้อมูลที่เราสร้างไว้
$port     = 4000; // คัดลอกจากช่อง Port

// เชื่อมต่อฐานข้อมูล (สำหรับ TiDB ต้องระบุ Port 4000 ด้วย)
$conn = new mysqli($hostname, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    die('เชื่อมต่อฐานข้อมูลไม่สำเร็จ: ' . $conn->connect_error);
}

// ตั้งค่าภาษาไทย
$conn->set_charset('utf8mb4');