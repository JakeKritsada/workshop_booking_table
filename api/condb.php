<?php
if (isset($conn)) return;

// ใช้ข้อมูลจาก InfinityFree ที่คุณแคปรูปมาให้ผมก่อนหน้านี้
$hostname = 'sql307.infinityfree.com';
$username = 'if0_41897370';
$password = 'FEKiMjQhXzA2';
$dbname   = 'if0_41897370_workshop_booking_table';

$conn = new mysqli($hostname, $username, $password, $dbname);

if ($conn->connect_error) {
    die('เชื่อมต่อฐานข้อมูลไม่สำเร็จ: ' . $conn->connect_error);
}

$conn->set_charset('utf8mb4');