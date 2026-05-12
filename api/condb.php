<?php
// api/condb.php
if (isset($pdo)) return;

$hostname = 'gateway01.ap-southeast-1.prod.alicloud.tidbcloud.com'; 
$username = '2sPFaH4bRb7ouXQ.root'; 
$password = 'kkUyvAYbFDhhe0IL'; 
$dbname   = 'workshop_booking'; 
$port     = 4000; 

try {
    // --- ส่วนของ PDO ---
    $dsn = "mysql:host=$hostname;port=$port;dbname=$dbname;charset=utf8mb4";
    $options = [
        // บังคับเปิด SSL แต่สั่งไม่ต้อง Verify ใบรับรอง (เพื่อแก้ปัญหาบน Vercel)
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];
    $pdo = new PDO($dsn, $username, $password, $options);
    
    // --- ส่วนของ mysqli (สำหรับโค้ดเก่าของคุณ) ---
    $conn = mysqli_init();
    // บังคับข้ามการตรวจสอบใบรับรองเซิร์ฟเวอร์
    mysqli_options($conn, MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);
    
    // ทำการเชื่อมต่อพร้อม Flag SSL
    if (!mysqli_real_connect($conn, $hostname, $username, $password, $dbname, $port, NULL, MYSQLI_CLIENT_SSL)) {
        error_log('Connection Error: ' . mysqli_connect_error());
    }
    $conn->set_charset('utf8mb4');

} catch (PDOException $e) {
    // ปิดการแสดง Error หน้าจอเพื่อป้องกัน Headers already sent
    error_log("DB Connection Failed: " . $e->getMessage());
    // แสดงข้อความสั้นๆ แทน
    die("ขออภัย ระบบขัดข้องชั่วคราว");
}