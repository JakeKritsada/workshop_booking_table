<?php
if (isset($conn)) return;

// นำข้อมูลจากหน้าจอ TiDB ของคุณมาใส่ตรงนี้
$hostname = 'gateway01.ap-southeast-1.prod.alicloud.tidbcloud.com'; // คัดลอกจากช่อง Host
$username = '2sPFaH4bRb7ouXQ.root'; // คัดลอกจากช่อง User
$password = 'kkUyvAYbFDhhe0IL'; // ใส่รหัสผ่านที่คุณตั้งเอง
$dbname   = 'workshop_booking'; // ชื่อฐานข้อมูลที่เราสร้างไว้
$port     = 4000; // คัดลอกจากช่อง Port

// 1. สร้างตัวแปรเชื่อมต่อ mysqli
$conn = mysqli_init();

// 2. ตั้งค่าให้ใช้ SSL (สำคัญมากสำหรับ TiDB Cloud)
$conn->ssl_set(NULL, NULL, NULL, NULL, NULL);

// 3. ทำการเชื่อมต่อจริง
if (!$conn->real_connect($hostname, $username, $password, $dbname, $port)) {
    die('เชื่อมต่อฐานข้อมูลไม่สำเร็จ: ' . mysqli_connect_error());
}

// ตั้งค่าภาษาไทย
$conn->set_charset('utf8mb4');