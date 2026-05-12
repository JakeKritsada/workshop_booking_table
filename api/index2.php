<?php
session_start(); 
require_once 'condb.php'; 

$phone = $_SESSION['phone'] ?? '';
$tables = [];
$query_error = '';

$sql = "SELECT id, table_name, table_status FROM tbl_table ORDER BY id ASC";
$result = mysqli_query($conn, $sql);

if ($result) {
  while ($row = mysqli_fetch_assoc($result)) {
    $tables[] = $row;
  }
  mysqli_free_result($result);
} else {
  $query_error = "เกิดข้อผิดพลาด: " . mysqli_error($conn);
}
?>

<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>จองโต๊ะ | Deep D House</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  
  <style>
    /* 🚩 คุมสไตล์เนื้อหาโดยไม่ยุ่งกับ Navbar */
    body {
      display: flex;
      flex-direction: column;
      min-height: 100vh;
      margin: 0;
      background-color: #0e0e0e;
    }

    .main-content {
      flex: 1;
      padding-top: 100px; /* เว้นระยะจาก Navbar เดิม */
    }

    .card-booking {
      background: #151515;
      border: 1px solid rgba(245, 165, 36, 0.15);
      border-radius: 25px;
      padding: 35px;
      box-shadow: 0 15px 35px rgba(0, 0, 0, 0.5);
    }

    /* ตกแต่งปุ่มโต๊ะให้น่ากดขึ้น */
    .btn-table {
      height: 70px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 700;
      font-size: 1.1rem;
      border-radius: 15px;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      border: none;
      box-shadow: 0 4px 10px rgba(0,0,0,0.3);
    }

    .btn-table:hover:not([disabled]) {
      transform: translateY(-5px);
      box-shadow: 0 8px 20px rgba(40, 167, 69, 0.3);
      filter: brightness(1.1);
    }

    .badge-guide {
      padding: 10px 20px;
      border-radius: 50px;
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid rgba(255, 255, 255, 0.1);
    }

    footer {
      background: #000;
      color: #555;
      padding: 25px 0;
      text-align: center;
      border-top: 1px solid rgba(245, 165, 36, 0.1);
    }
  </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="main-content">
  <div class="container pb-5">
    <div class="card-booking">
      <div class="text-center mb-5">
        <h3 class="text-warning fw-bold mb-2" style="font-size: 2rem;">🪑 ผังการจองโต๊ะ</h3>
        <p class="text-secondary">กรุณาเลือกโต๊ะที่คุณต้องการนั่งพักผ่อนในค่ำคืนนี้</p>
      </div>

      <?php if (!empty($query_error)): ?>
        <div class="alert alert-danger rounded-3"><?= htmlspecialchars($query_error) ?></div>
      <?php endif; ?>

      <div class="row g-3 justify-content-center">
        <?php if (!empty($tables)): ?>
          <?php foreach ($tables as $row): 
            $status = (int)$row['table_status'];
            $btnClass = 'btn-secondary opacity-50'; 
            $disabled = 'disabled';
            $href = '#';

            if ($status === 0) { // ว่าง
              $btnClass = 'btn-success bg-success';
              $disabled = '';
              $href = 'booking.php?id='.(int)$row['id'].'&act=booking';
            } elseif ($status === 2) { // รออนุมัติ
              $btnClass = 'btn-info text-white';
              $disabled = 'disabled';
            }
          ?>
          
          <div class="col-6 col-sm-4 col-md-3 col-lg-2">
            <?php if ($disabled === ''): ?>
              <a href="<?= $href ?>" class="btn <?= $btnClass ?> btn-table w-100">
                <?= htmlspecialchars($row['table_name']) ?>
              </a>
            <?php else: ?>
              <button class="btn <?= $btnClass ?> btn-table w-100" disabled>
                <?= htmlspecialchars($row['table_name']) ?>
              </button>
            <?php endif; ?>
          </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="alert alert-secondary w-100 text-center py-4">ยังไม่มีข้อมูลโต๊ะในระบบ</div>
        <?php endif; ?>
      </div>

      <div class="mt-5 text-center">
        <div class="badge-guide d-inline-block">
          <small class="text-light d-flex gap-3 flex-wrap justify-content-center fw-bold">
            <span class="text-success"><i class="bi bi-circle-fill me-1"></i> สีเขียว: ว่าง</span>
            <span class="text-info"><i class="bi bi-circle-fill me-1"></i> สีฟ้า: รอตรวจสอบ</span>
            <span class="text-secondary"><i class="bi bi-circle-fill me-1"></i> สีเทา: ไม่ว่าง</span>
          </small>
        </div>
      </div>
    </div>
  </div>
</div>

<footer>
  <div class="container">
    &copy; 2025 Deep D House · ระบบจองโต๊ะอาหารออนไลน์
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>