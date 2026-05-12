<?php
require_once 'condb.php'; 
session_start(); 

if (empty($_SESSION['user_id'])) { 
    header('Location: login.php'); 
    exit; 
}

$user_id = (int)$_SESSION['user_id']; 
$stmt = $conn->prepare("SELECT phone, role, created_at FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>

<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>รายละเอียดบัญชี | Deep D House</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
    
    <style>
        /* 🎨 คุมโทนสีหน้าโปรไฟล์ */
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-color: #0e0e0e; /* ดำ Deep D */
            color: #fff;
            margin: 0;
            font-family: 'Kanit', sans-serif;
        }

        .main-content {
            flex: 1;
            padding-top: 100px; /* เว้นระยะให้ Navbar Glow */
            display: flex;
            align-items: center; /* จัด Card ให้อยู่กลางแนวตั้ง */
        }

        .card-profile {
            background: #151515 !important;
            border: 1px solid rgba(245, 165, 36, 0.2) !important;
            border-radius: 25px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.6);
            overflow: hidden;
        }

        .profile-header {
            background: linear-gradient(45deg, #151515, #000);
            padding: 20px;
            border-bottom: 1px solid rgba(245, 165, 36, 0.1);
        }

        .text-gold {
            color: #f5a524 !important;
        }

        .label-text {
            color: #888;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .info-value {
            font-size: 1.1rem;
            font-weight: 600;
            color: #eee;
        }

        footer {
            background: #000;
            color: #444;
            padding: 25px 0;
            text-align: center;
            border-top: 1px solid rgba(245, 165, 36, 0.1);
        }

        .btn-gold {
            background: #f5a524;
            color: #000;
            border: none;
            font-weight: bold;
            border-radius: 10px;
            transition: all 0.3s;
        }

        .btn-gold:hover {
            background: #d9901d;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(245, 165, 36, 0.3);
        }
    </style>
</head>
<body>

    <?php include 'navbar.php'; ?>

    <div class="main-content">
        <div class="container" style="max-width:600px;">
            <div class="card card-profile shadow-lg">
                <div class="profile-header text-center">
                    <h4 class="mb-0 text-gold fw-bold">👤 รายละเอียดบัญชี</h4>
                </div>
                <div class="card-body p-4">
                    
                    <div class="mb-4 p-3 rounded-3" style="background: rgba(255,255,255,0.02);">
                        <div class="row align-items-center mb-3">
                            <div class="col-4 label-text">เบอร์โทรศัพท์</div>
                            <div class="col-8 info-value text-gold"><?=htmlspecialchars($user['phone'])?></div>
                        </div>
                        <div class="row align-items-center mb-3">
                            <div class="col-4 label-text">ระดับผู้ใช้งาน</div>
                            <div class="col-8 info-value">
                                <span class="badge" style="background: rgba(245,165,36,0.1); color: #f5a524; border: 1px solid #f5a524;">
                                    <?=htmlspecialchars(strtoupper($user['role']))?>
                                </span>
                            </div>
                        </div>
                        <div class="row align-items-center">
                            <div class="col-4 label-text">สมาชิกเมื่อ</div>
                            <div class="col-8 info-value text-secondary" style="font-size: 0.95rem;">
                                <?=htmlspecialchars($user['created_at'])?>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 align-items-center mt-4">
                        <button class="btn btn-outline-light px-4 rounded-pill" data-bs-toggle="modal" data-bs-target="#changeModal">
                            🔑 เปลี่ยนรหัสผ่าน
                        </button>
                        
                        <form method="post" action="logout.php" class="ms-auto">
                            <button class="btn btn-gold px-4 rounded-pill shadow-sm" type="submit">
                                🚪 ออกจากระบบ
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <div class="container text-muted">
            © 2025 Deep D House · บัญชีผู้ใช้งานระบบจองโต๊ะ
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>