<?php
if (isset($conn)) return;

$hostname = 'gateway01.ap-southeast-1.prod.alicloud.tidbcloud.com'; 
$username = '2sPFaH4bRb7ouXQ.root'; 
$password = 'kkUyvAYbFDhhe0IL'; 
$dbname   = 'workshop_booking'; 
$port     = 4000; 

// 1. เริ่มต้น mysqli
$conn = mysqli_init();

// 2. ตั้งค่า SSL ก่อนที่จะทำการ connect (ห้ามสลับลำดับ)
mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);

// 3. ทำการเชื่อมต่อพร้อม Flag SSL
$options = MYSQLI_CLIENT_SSL;
if (!mysqli_real_connect($conn, $hostname, $username, $password, $dbname, $port, NULL, $options)) {
    // ปิดการแสดง Error ตรงๆ เพื่อไม่ให้กระทบ Header
    error_log('Connection Error: ' . mysqli_connect_error());
    return;
}
$conn->set_charset('utf8mb4');
?>