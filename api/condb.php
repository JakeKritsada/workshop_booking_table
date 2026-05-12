<?php
if (isset($pdo)) return;

$hostname = 'gateway01.ap-southeast-1.prod.alicloud.tidbcloud.com'; 
$username = '2sPFaH4bRb7ouXQ.root'; 
$password = 'kkUyvAYbFDhhe0IL'; 
$dbname   = 'workshop_booking'; 
$port     = 4000; 

try {
    // กำหนดการเชื่อมต่อแบบ PDO พร้อมเปิดใช้งาน SSL
    $dsn = "mysql:host=$hostname;port=$port;dbname=$dbname;charset=utf8mb4";
    $options = [
        PDO::MYSQL_ATTR_SSL_CA => true, // บังคับใช้ SSL
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];

    $pdo = new PDO($dsn, $username, $password, $options);
    
    // สร้างตัวแปร $conn ไว้เพื่อให้โค้ดเก่าของคุณ (mysqli) ยังพอทำงานร่วมกันได้ (ถ้าจำเป็น)
    // แต่แนะนำให้เปลี่ยนมาใช้ $pdo ในหน้าอื่นๆ ด้วยจะดีที่สุดครับ
    $conn = new mysqli($hostname, $username, $password, $dbname, $port);
    $conn->ssl_set(NULL, NULL, NULL, NULL, NULL);
    $conn->real_connect($hostname, $username, $password, $dbname, $port, NULL, MYSQLI_CLIENT_SSL);

} catch (PDOException $e) {
    die("เชื่อมต่อฐานข้อมูลไม่สำเร็จ: " . $e->getMessage());
}