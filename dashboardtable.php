<?php
require_once 'auth.php';
requireRole('admin');

require_once 'condb.php';

date_default_timezone_set('Asia/Bangkok');

/* =========================
   RESET TABLES
========================= */
if (isset($_POST['reset_tables'])) {

    mysqli_query($conn, "DELETE FROM tbl_booking WHERE booking_date < CURDATE()");
    mysqli_query($conn, "UPDATE tbl_table SET table_status = 0");

    $message = "รีเซ็ตโต๊ะทั้งหมดเรียบร้อยแล้ว!";
}

/* =========================
   DELETE TABLE
========================= */
if (isset($_GET['delete'])) {

    $id = intval($_GET['delete']);

    $stmt = $conn->prepare("SELECT name,img FROM detailtable WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows > 0) {

        $row = $result->fetch_assoc();

        $table_name = $row['name'];
        $img = $row['img'];

        // ลบรูปภาพ
        if (!empty($img) && file_exists("images/" . $img)) {
            unlink("images/" . $img);
        }

        // ลบ booking ที่เกี่ยวข้อง
        $stmtBooking = $conn->prepare("DELETE FROM tbl_booking WHERE table_name=?");
        $stmtBooking->bind_param("s", $table_name);
        $stmtBooking->execute();

        // ลบ tbl_table
        $stmtTable = $conn->prepare("DELETE FROM tbl_table WHERE table_name=?");
        $stmtTable->bind_param("s", $table_name);
        $stmtTable->execute();

        // ลบ detailtable
        $stmtDetail = $conn->prepare("DELETE FROM detailtable WHERE id=?");
        $stmtDetail->bind_param("i", $id);
        $stmtDetail->execute();
    }

    header("Location: dashboardtable.php");
    exit;
}

/* =========================
   ADD TABLE
========================= */
if (isset($_POST['add_submit'])) {

    $name = trim($_POST['name']);
    $seats = trim($_POST['seats']);

    if (isset($_FILES['img']) && $_FILES['img']['error'] == 0) {

        $img_tmp = $_FILES['img']['tmp_name'];
        $original_name = $_FILES['img']['name'];

        $file_type = mime_content_type($img_tmp);
        $file_size = $_FILES['img']['size'];

        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

        // ตรวจสอบประเภทไฟล์
        if (!in_array($file_type, $allowed_types)) {

            $message = "รองรับเฉพาะ JPG PNG GIF WEBP";

        }
        // ตรวจสอบขนาดไฟล์
        elseif ($file_size > 5 * 1024 * 1024) {

            $message = "ไฟล์ใหญ่เกิน 5MB";

        }
        else {

            // ตั้งชื่อไฟล์ใหม่
            $img_name = time() . '_' . preg_replace('/[^A-Za-z0-9.\-_]/', '_', $original_name);

            // สร้างโฟลเดอร์ images ถ้ายังไม่มี
            if (!is_dir("images")) {
                mkdir("images", 0755, true);
            }

            // ย้ายไฟล์
            move_uploaded_file($img_tmp, "images/" . $img_name);

            // เพิ่ม detailtable
            $stmt = $conn->prepare("
                INSERT INTO detailtable(name,seats,img)
                VALUES(?,?,?)
            ");

            $stmt->bind_param("sss", $name, $seats, $img_name);
            $stmt->execute();

            // เพิ่ม tbl_table
            $stmt2 = $conn->prepare("
                INSERT INTO tbl_table(table_name,table_status)
                VALUES(?,0)
            ");

            $stmt2->bind_param("s", $name);
            $stmt2->execute();

            $message = "เพิ่มโต๊ะเรียบร้อยแล้ว!";
        }

    } else {

        $message = "กรุณาเลือกรูปภาพ";
    }
}

/* =========================
   LOAD TABLES
========================= */
$tables = [];

$result = mysqli_query($conn, "
    SELECT *
    FROM detailtable
    ORDER BY id ASC
");

if ($result) {

    while ($row = mysqli_fetch_assoc($result)) {
        $tables[] = $row;
    }

    mysqli_free_result($result);
}
?>

<!doctype html>
<html lang="th">
<head>

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Dashboard Table | DeepD House</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="style.css">

</head>

<body>

<?php include 'navbar.php'; ?>

<div class="container py-4">

    <!-- HEADER -->

    <div class="d-flex justify-content-between align-items-center mb-4">

        <h3 class="text-warning m-0">
            Dashboard จัดการโต๊ะ
        </h3>

        <div class="d-flex gap-2">

            <a href="admin_list.php" class="btn btn-outline-dark">
                กลับหน้าจอง
            </a>

            <form method="post"
                  onsubmit="return confirm('รีเซ็ตโต๊ะทั้งหมด ?');">

                <button type="submit"
                        name="reset_tables"
                        class="btn btn-danger">

                    รีเซ็ตโต๊ะ

                </button>

            </form>

        </div>

    </div>

    <!-- MESSAGE -->

    <?php if (!empty($message)): ?>

        <div class="alert alert-info">
            <?= htmlspecialchars($message) ?>
        </div>

    <?php endif; ?>

    <!-- FORM ADD TABLE -->

    <div class="form-container p-4">

        <h5 class="mb-3 text-warning">
            เพิ่มโต๊ะใหม่
        </h5>

        <form method="post" enctype="multipart/form-data">

            <div class="row g-2 mb-3">

                <div class="col-md-6">
                    <input type="text"
                           name="name"
                           class="form-control"
                           placeholder="ชื่อโต๊ะ"
                           required>
                </div>

                <div class="col-md-6">
                    <input type="text"
                           name="seats"
                           class="form-control"
                           placeholder="จำนวนที่นั่ง"
                           required>
                </div>

            </div>

            <div class="mb-3">

                <input type="file"
                       name="img"
                       class="form-control"
                       required>

            </div>

            <button type="submit"
                    name="add_submit"
                    class="btn btn-primary w-100">

                บันทึกโต๊ะ

            </button>

        </form>

    </div>

    <!-- TABLE LIST -->

    <div class="card p-3">
        <table class="table table-hover text-center align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>ชื่อโต๊ะ</th>
                    <th>ที่นั่ง</th>
                    <th>รูปภาพ</th>
                    <th>Action</th>
                </tr>

            </thead>

            <tbody>

            <?php if (!empty($tables)): ?>

                <?php foreach($tables as $t): ?>

                    <tr>

                        <td><?= $t['id'] ?></td>

                        <td>
                            <?= htmlspecialchars($t['name']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($t['seats']) ?>
                        </td>

                        <td>

                            <img
                                src="images/<?= htmlspecialchars($t['img']) ?>"
                                class="img-thumb"
                                onerror="this.src='https://placehold.co/120x120?text=No+Image';"
                            >

                        </td>

                        <td>

                            <a href="admin_edit_table.php?id=<?= $t['id'] ?>"
                               class="btn btn-primary btn-sm">

                                Edit

                            </a>

                            <a href="dashboardtable.php?delete=<?= $t['id'] ?>"
                               class="btn btn-danger btn-sm"
                               onclick="return confirm('ลบโต๊ะนี้ ?');">

                                Delete

                            </a>

                        </td>

                    </tr>

                <?php endforeach; ?>

            <?php else: ?>

                <tr>

                    <td colspan="5">
                        ยังไม่มีข้อมูลโต๊ะ
                    </td>

                </tr>

            <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>