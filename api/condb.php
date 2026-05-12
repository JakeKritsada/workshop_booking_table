<?php
// api/condb.php
if (isset($pdo)) return;

$hostname = 'gateway01.ap-southeast-1.prod.alicloud.tidbcloud.com'; 
$username = '2sPFaH4bRb7ouXQ.root'; 
$password = 'kkUyvAYbFDhhe0IL'; 
$dbname   = 'workshop_booking'; 
$port     = 4000; 

try {
    $dsn = "mysql:host=$hostname;port=$port;dbname=$dbname;charset=utf8mb4";
    $options = [
        // บังคับใช้ SSL และข้ามการตรวจสอบใบรับรองเซิร์ฟเวอร์เพื่อแก้ปัญหาบน Vercel
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];

    $pdo = new PDO($dsn, $username, $password, $options);
    
    // สำหรับโค้ดเก่าที่ยังใช้ mysqli
    $conn = mysqli_init();
    mysqli_options($conn, MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);
    mysqli_real_connect($conn, $hostname, $username, $password, $dbname, $port, NULL, MYSQLI_CLIENT_SSL);

} catch (PDOException $e) {
    die("เชื่อมต่อฐานข้อมูลไม่สำเร็จ: " . $e->getMessage());
}