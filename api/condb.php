<?php
// api/condb.php
if (isset($conn)) return;

// อ้างอิงค่าจาก mysql cli ของคุณ
$hostname = 'gateway01.ap-southeast-1.prod.alicloud.tidbcloud.com'; 
$username = '2sPFaH4bRb7ouXQ.root'; 
$password = 'V3HSTeWqh5fGWwmK'; 
$dbname   = 'workshop_booking'; 
$port     = 4000; 

// 1. เริ่มต้นการเชื่อมต่อ
$conn = mysqli_init();

// 2. ตั้งค่าข้ามการตรวจสอบใบรับรอง (เพื่อแก้ปัญหา SSL บน Vercel)
mysqli_options($conn, MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);

// 3. ทำการเชื่อมต่อจริง (mysqli_real_connect)
$success = mysqli_real_connect(
    $conn, 
    $hostname, 
    $username, 
    $password, 
    $dbname, 
    $port, 
    NULL, 
    MYSQLI_CLIENT_SSL
);

if (!$success) {
    // บันทึก Error ไว้ในระบบ ไม่พ่นออกหน้าจอ
    error_log('Connect Error: ' . mysqli_connect_error());
    die("ขออภัย ระบบขัดข้องชั่วคราว (Database Connection Error)");
}

// 4. ตั้งค่าภาษาไทย
$conn->set_charset('utf8mb4');