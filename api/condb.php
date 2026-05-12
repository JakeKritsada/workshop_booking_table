<?php
// api/condb.php
if (isset($conn)) return;

$hostname = 'gateway01.ap-southeast-1.prod.alicloud.tidbcloud.com'; 
$username = '2sPFaH4bRb7ouXQ.root'; 
$password = 'kkUyvAYbFDhhe0IL'; 
$dbname   = 'workshop_booking'; 
$port     = 4000; 

try {
    // เชื่อมต่อผ่าน PDO พร้อมบังคับ SSL
    $dsn = "mysql:host=$hostname;port=$port;dbname=$dbname;charset=utf8mb4";
    $options = [
        PDO::MYSQL_ATTR_SSL_CA => true, // บังคับใช้ SSL
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ];
    
    $pdo = new PDO($dsn, $username, $password, $options);
    
    // สร้างตัวแปร $conn แบบเดิมไว้หลอกระบบเก่าให้ยังทำงานได้
    $conn = mysqli_init();
    mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);
    mysqli_real_connect($conn, $hostname, $username, $password, $dbname, $port, NULL, MYSQLI_CLIENT_SSL);
    
} catch (PDOException $e) {
    // ถ้าพลาดให้บันทึก log แทนการพ่นออกมาหน้าจอ
    error_log("Connection failed: " . $e->getMessage());
}
?>