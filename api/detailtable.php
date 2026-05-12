<?php
// detailtable.php — ไม่มี reset ตี3
require_once 'condb.php'; // ให้แน่ใจว่าไฟล์นี้ set ตัวแปร $conn

$sql = "SELECT id, name, img, seats FROM detailtable ORDER BY id ASC";
$result = mysqli_query($conn, $sql);
if (!$result) {
  die("Error: " . mysqli_error($conn));
}

$tables = [];
while ($row = mysqli_fetch_assoc($result)) {
  $tables[] = $row;
}
?>
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>ข้อมูลโต๊ะ | Deep D House</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <?php include 'navbar.php'; ?>
</head>
<body>

  

  <div class="container py-5 flex-grow-1">
    <h2 class="fw-bold text-warning mb-4 text-center">ข้อมูลโต๊ะทั้งหมด</h2>

    <div class="row g-4">
      <?php foreach($tables as $t): ?>
        <div class="col-12 col-sm-6 col-lg-3">
          <div class="card h-100">
            <img
              src="images/<?= htmlspecialchars($t['img'] ?: '', ENT_QUOTES, 'UTF-8') ?>"
              class="card-img-top"
              alt="<?= htmlspecialchars($t['name'] ?: '', ENT_QUOTES, 'UTF-8') ?>"
              onerror="this.src='https://placehold.co/600x400/1a1a1a/f5a524?text=No+Image';">
            <div class="card-body d-flex flex-column">
              <div class="d-flex justify-content-between align-items-start mb-2">
                <span class="badge badge-new rounded-pill">ใหม่</span>
              </div>
              <div class="title mb-1"><?= htmlspecialchars($t['name'], ENT_QUOTES, 'UTF-8') ?></div>
              <div class="seat mb-3">ที่นั่ง <?= htmlspecialchars($t['seats'], ENT_QUOTES, 'UTF-8') ?></div>
              <div class="mt-auto">
                <a class="btn btn-primary w-100"
                   href="index2.php?table_id=<?= (int)$t['id'] ?>">
                  จองโต๊ะ
                </a>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>

      <?php if (empty($tables)): ?>
        <div class="col-12">
          <div class="alert alert-secondary border-0 text-center">ยังไม่มีข้อมูลโต๊ะ</div>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <footer>
© 2025 Deep D House · ระบบจองโต๊ะอาหารออนไลน์  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
