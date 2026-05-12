<?php
if (isset($conn)) return;

$hostname = 'gateway01.ap-southeast-1.prod.alicloud.tidbcloud.com'; 
$username = '2sPFaH4bRb7ouXQ.root'; 
$password = 'kkUyvAYbFDhhe0IL'; 
$dbname   = 'workshop_booking'; 
$port     = 4000; 

$conn = mysqli_init();

// บรรทัดสำคัญที่ TiDB บังคับ
$conn->ssl_set(NULL, NULL, NULL, NULL, NULL);

if (!$conn->real_connect($hostname, $username, $password, $dbname, $port)) {
    die('เชื่อมต่อฐานข้อมูลไม่สำเร็จ: ' . mysqli_connect_error());
}

$conn->set_charset('utf8mb4');