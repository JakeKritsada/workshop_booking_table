<?php
require_once 'auth.php';
requireRole('admin');
require_once 'condb.php';
date_default_timezone_set('Asia/Bangkok');

// วันที่และเดือนปัจจุบัน
$today = date('Y-m-d');
$month = date('Y-m');

// -------------------------------
// ฟังก์ชันช่วย: ตรวจสอบผลลัพธ์
// -------------------------------
function safe_fetch_assoc($query) {
  if ($query && $query instanceof mysqli_result) {
    return mysqli_fetch_assoc($query);
  }
  return null;
}

// ✅ ยอดวันนี้
$q1 = mysqli_query($conn, "
  SELECT COUNT(*) AS count_today, SUM(total_amount) AS total_today
  FROM tbl_receipt
  WHERE DATE(receipt_date) = '$today'
");
$todayData = safe_fetch_assoc($q1) ?: ['count_today'=>0,'total_today'=>0];

// ✅ ยอดเดือนนี้
$q2 = mysqli_query($conn, "
  SELECT COUNT(*) AS count_month, SUM(total_amount) AS total_month
  FROM tbl_receipt
  WHERE DATE_FORMAT(receipt_date, '%Y-%m') = '$month'
");
$monthData = safe_fetch_assoc($q2) ?: ['count_month'=>0,'total_month'=>0];

// ✅ สรุปโต๊ะ
$q3 = mysqli_query($conn, "
  SELECT
    SUM(CASE WHEN table_status=0 THEN 1 ELSE 0 END) AS available,
    SUM(CASE WHEN table_status=1 THEN 1 ELSE 0 END) AS occupied,
    SUM(CASE WHEN table_status=2 THEN 1 ELSE 0 END) AS pending
  FROM tbl_table
");
$tableStats = safe_fetch_assoc($q3) ?: ['available'=>0,'occupied'=>0,'pending'=>0];

// ✅ กราฟ 1: ยอดขายรายวัน (เดือนปัจจุบัน)
$q4 = mysqli_query($conn, "
  SELECT DATE(receipt_date) AS date, SUM(total_amount) AS sum_total
  FROM tbl_receipt
  WHERE DATE_FORMAT(receipt_date,'%Y-%m')='$month'
  GROUP BY DATE(receipt_date)
  ORDER BY DATE(receipt_date)
");
$chartMonth = [];
if ($q4 && $q4 instanceof mysqli_result) {
  while ($r = mysqli_fetch_assoc($q4)) {
    $chartMonth[] = ['date' => $r['date'], 'total' => (float)$r['sum_total']];
  }
}

// ✅ กราฟ 2: ยอดขายรวมทุกเดือน (สะสม)
$q5 = mysqli_query($conn, "
  SELECT DATE_FORMAT(receipt_date, '%Y-%m') AS month, SUM(total_amount) AS sum_total
  FROM tbl_receipt
  GROUP BY DATE_FORMAT(receipt_date, '%Y-%m')
  ORDER BY DATE_FORMAT(receipt_date, '%Y-%m')
");
$chartAll = [];
if ($q5 && $q5 instanceof mysqli_result) {
  while ($r = mysqli_fetch_assoc($q5)) {
    $chartAll[] = ['month' => $r['month'], 'total' => (float)$r['sum_total']];
  }
}
?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<title>📊 สรุปยอดทั้งหมด | Deep D House</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
:root {
  --gold: #f5a524;
  --gold-2: #d9901d;
  --bg: #0b0f17;
}

body {
  background: var(--bg);
  color: #fff;
  font-family: "Kanit", sans-serif;
  min-height: 100vh;
}

.navbar-dark {
  background: #000;
  box-shadow: 0 4px 16px rgba(245,165,36,0.25);
}

.navbar-brand {
  color: var(--gold) !important;
  font-weight: 700;
  letter-spacing: 0.5px;
}

h2 {
  color: var(--gold);
  font-weight: 700;
}

.card {
  background: #131a26;
  border-radius: 1rem;
  border: 1px solid rgba(245,165,36,0.15);
  box-shadow: 0 10px 30px rgba(0,0,0,.4);
}

.card h4 {
  color: var(--gold);
}

.value {
  font-size: 2rem;
  font-weight: 800;
  color: var(--gold);
}

.sub {
  color: #aaa;
}

.btn-warning {
  background: var(--gold);
  border: none;
  color: #000;
  font-weight: 600;
}
.btn-warning:hover {
  background: var(--gold-2);
}

footer {
  background: #000;
  color: #aaa;
  text-align: center;
  padding: 12px 0;
  font-size: 0.9rem;
  border-top: 1px solid rgba(255,255,255,.1);
  margin-top: 60px;
}
</style>
</head>

<body>

<nav class="navbar navbar-expand-lg navbar-dark mb-4">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold" href="admin.php">← กลับหน้า Admin</a>
  </div>
</nav>

<div class="container py-3">
  <h2 class="mb-4">📊 สรุปยอดรวมระบบ</h2>

  <!-- ✅ การ์ดสรุป -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card p-3 text-center">
        <h4>วันนี้</h4>
        <div class="value"><?= (int)$todayData['count_today'] ?></div>
        <div class="sub">ยอดรวม <?= number_format($todayData['total_today'] ?? 0, 2) ?> ฿</div>
      </div>
    </div>

    <div class="col-md-3">
      <div class="card p-3 text-center">
        <h4>เดือนนี้</h4>
        <div class="value"><?= (int)$monthData['count_month'] ?></div>
        <div class="sub">ยอดรวม <?= number_format($monthData['total_month'] ?? 0, 2) ?> ฿</div>
      </div>
    </div>

    <div class="col-md-3">
      <div class="card p-3 text-center">
        <h4>โต๊ะทั้งหมด</h4>
        <div class="value"><?= (int)($tableStats['available']+$tableStats['occupied']+$tableStats['pending']) ?></div>
        <div class="sub">ทั้งหมดในระบบ</div>
      </div>
    </div>

    <div class="col-md-3">
      <div class="card p-3 text-center">
        <h4>สถานะโต๊ะ</h4>
        <div class="sub text-success">ว่าง: <?= $tableStats['available'] ?></div>
        <div class="sub text-warning">รออนุมัติ: <?= $tableStats['pending'] ?></div>
        <div class="sub text-danger">จองแล้ว: <?= $tableStats['occupied'] ?></div>
      </div>
    </div>
  </div>

  <!-- ✅ กราฟ 1: รายวันเดือนนี้ -->
  <div class="card p-4 mb-4">
    <h4 class="mb-3">📅 ยอดขายรายวัน (เดือน <?= date('F Y') ?>)</h4>
    <canvas id="chartMonth" height="100"></canvas>
  </div>

  <!-- ✅ กราฟ 2: ยอดขายรวมทุกเดือน -->
  <div class="card p-4 mb-4">
    <h4 class="mb-3">📈 ยอดขายรวมรายเดือนทั้งหมด</h4>
    <canvas id="chartAll" height="100"></canvas>
  </div>

  <div class="text-center">
    <a href="admin.php" class="btn btn-warning btn-lg">← กลับหน้า Admin</a>
  </div>
</div>

<footer>
  © 2025 Deep D House · Admin System
</footer>

<script>
const chartMonthData = <?= json_encode($chartMonth) ?>;
const chartAllData = <?= json_encode($chartAll) ?>;

// 🎨 ธีม Deep D House
const gold = '#f5a524';
const goldSoft = 'rgba(245,165,36,0.25)';
const goldFill = 'rgba(245,165,36,0.15)';
const textColor = '#ffffff';
const gridColor = 'rgba(255,255,255,0.08)';

// ✅ กราฟรายวัน
new Chart(document.getElementById('chartMonth'), {
  type: 'line',
  data: {
    labels: chartMonthData.map(d => d.date),
    datasets: [{
      label: 'ยอดรายวัน (บาท)',
      data: chartMonthData.map(d => d.total),
      borderColor: gold,
      backgroundColor: goldFill,
      pointBackgroundColor: gold,
      pointBorderColor: gold,
      fill: true,
      tension: 0.35,
      borderWidth: 3
    }]
  },
  options: {
    plugins: {
      legend: { labels: { color: textColor, font: { size: 14, weight: 'bold' } } },
      tooltip: {
        backgroundColor: '#1a1a1a',
        titleColor: gold,
        bodyColor: '#fff',
        borderColor: gold,
        borderWidth: 1
      }
    },
    scales: {
      x: { ticks: { color: textColor }, grid: { color: gridColor } },
      y: { beginAtZero: true, ticks: { color: textColor }, grid: { color: gridColor } }
    }
  }
});

// ✅ กราฟรายเดือนรวมทั้งหมด
new Chart(document.getElementById('chartAll'), {
  type: 'bar',
  data: {
    labels: chartAllData.map(d => d.month),
    datasets: [{
      label: 'ยอดขายรวมต่อเดือน (บาท)',
      data: chartAllData.map(d => d.total),
      backgroundColor: goldSoft,
      borderColor: gold,
      borderWidth: 2,
      hoverBackgroundColor: gold
    }]
  },
  options: {
    plugins: {
      legend: { labels: { color: textColor, font: { size: 14, weight: 'bold' } } },
      tooltip: {
        backgroundColor: '#1a1a1a',
        titleColor: gold,
        bodyColor: '#fff',
        borderColor: gold,
        borderWidth: 1
      }
    },
    scales: {
      x: { ticks: { color: textColor }, grid: { color: gridColor } },
      y: { beginAtZero: true, ticks: { color: textColor }, grid: { color: gridColor } }
    }
  }
});
</script>

</body>
</html>
