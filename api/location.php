<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>ดูที่อยู่ร้าน | Deep D House</title>

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  
  <style>
    /* 🚩 บทเรียนเรื่อง Sticky Footer */
    body {
      display: flex;
      flex-direction: column;
      min-height: 100vh;
      margin: 0;
      background-color: #0e0e0e;
    }

    .main-content {
      flex: 1; /* กล่องนี้จะยืดพื้นที่เพื่อดัน footer ลงข้างล่าง */
      display: flex;
      align-items: center; /* จัดเนื้อหาให้อยู่กลางแนวตั้ง */
      justify-content: center; /* จัดเนื้อหาให้อยู่กลางแนวนอน */
    }

    /* ตกแต่งกรอบแผนที่ */
    .map-container {
      width: 100%;
      max-width: 800px;
      background: #151515;
      padding: 15px;
      border-radius: 25px;
      border: 1px solid rgba(245, 165, 36, 0.2);
      box-shadow: 0 10px 30px rgba(0,0,0,0.5);
    }

    .map-iframe {
      width: 100%;
      height: 450px;
      border-radius: 15px;
      border: none;
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

  <!-- 🚩 ใช้ main-content คลุมเพื่อให้ Footer ตกไปอยู่ด้านล่าง -->
  <div class="main-content py-5">
    <div class="container">
      
      <!-- ส่วนหัวข้อจัดกึ่งกลาง -->
      <div class="text-center mb-5">
        <h1 class="fw-bold text-warning mb-3">📍 ที่อยู่ของร้านเรา</h1>
        <p class="text-light fs-5">
          ร้านของเราตั้งอยู่ที่<br>
          <span class="text-warning fw-bold">90 ถนน ทุ่งรี-โคกวัด ตำบลคอหงส์ อำเภอหาดใหญ่ สงขลา 90110</span>
        </p>
      </div>

      <!-- ส่วนแผนที่จัดกึ่งกลาง -->
      <div class="d-flex justify-content-center">
        <div class="map-container">
          <iframe
            class="map-iframe"
            src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d25584.375689779477!2d100.4961118!3d6.9980834!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x304d290018c0c353%3A0x6c77c7c7d4425ad4!2sDeep%20D%20House!5e0!3m2!1sth!2sth!4v1628084434241"
            loading="lazy" allowfullscreen="" referrerpolicy="no-referrer-when-downgrade">
          </iframe>
          
          <div class="text-center mt-4">
            <a class="btn btn-warning btn-lg px-5 fw-bold rounded-pill shadow"
               style="color: #000;"
               href="https://www.google.com/maps?q=6.9980834,100.4961118"
               target="_blank" rel="noopener">
              เปิดใน Google Maps
            </a>
          </div>
        </div>
      </div>

    </div>
  </div>

  <footer>
    <div class="container">
© 2025 Deep D House · ระบบจองโต๊ะอาหารออนไลน์    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>