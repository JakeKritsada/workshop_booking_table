<?php
// detailtable.php
require_once 'condb.php'; 

// 1. ตรวจสอบการเชื่อมต่อ
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// 2. ดึงข้อมูลจากตารางตาม SQL Dump ของคุณ
$sql = "SELECT id, name, img, seats FROM detailtable ORDER BY id ASC";
$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Query Error: " . mysqli_error($conn));
}

// แปลงข้อมูลจาก result set เป็น array เพื่อให้ง่ายต่อการใช้ foreach
$tables = [];
while ($row = mysqli_fetch_assoc($result)) {
    $tables[] = $row;
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ข้อมูลโต๊ะ - Deep D House</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #1a1a1a; color: white; }
        .card { border: 1px solid #f5a524; transition: 0.3s; }
        .card:hover { transform: translateY(-5px); }
        .badge-new { background-color: #f5a524; color: black; }
        footer { text-align: center; padding: 20px; color: #888; }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">

<?php include 'navbar.php'; ?>

<div class="container py-5 flex-grow-1">
    <h2 class="fw-bold text-warning mb-4 text-center">ข้อมูลโต๊ะภายในร้าน</h2>

    <div class="row g-4">
      <?php foreach($tables as $t): ?>
        <div class="col-12 col-sm-6 col-lg-3">
          <div class="card h-100 bg-dark text-white">
            <img
              src="images/<?= htmlspecialchars($t['img'] ?: '', ENT_QUOTES, 'UTF-8') ?>"
              class="card-img-top"
              alt="<?= htmlspecialchars($t['name'] ?: '', ENT_QUOTES, 'UTF-8') ?>"
              onerror="this.src='https://placehold.co/600x400/1a1a1a/f5a524?text=No+Image';">
            
            <div class="card-body d-flex flex-column">
              <div class="d-flex justify-content-between align-items-start mb-2">
                <span class="badge badge-new rounded-pill">ใหม่</span>
              </div>
              <h5 class="fw-bold mb-1"><?= htmlspecialchars($t['name'], ENT_QUOTES, 'UTF-8') ?></h5>
              <div class="text-secondary mb-3">ที่นั่ง: <?= htmlspecialchars($t['seats'], ENT_QUOTES, 'UTF-8') ?></div>
              
              <div class="mt-auto">
                <a class="btn btn-warning w-100 fw-bold"
                   href="index2.php?table_id=<?= (int)$t['id'] ?>">
                   จองโต๊ะนี้
                </a>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>

      <?php if (empty($tables)): ?>
        <div class="col-12">
          <div class="alert alert-secondary border-0 text-center">ยังไม่มีข้อมูลโต๊ะในระบบ</div>
        </div>
      <?php endif; ?>
    </div>
</div>

<footer>
    © 2026 Deep D House · ระบบจองโต๊ะอาหารออนไลน์
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>