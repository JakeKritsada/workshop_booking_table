<!-- <?php
require_once 'condb.php';
require_once __DIR__ . '/vendor/autoload.php'; // ถ้ามี composer (ไม่จำเป็นสำหรับ XAMPP)
use setasign\Fpdi\Fpdi;

// ใช้ reportlab ของระบบถ้าไม่มี composer
require_once 'C:/xampp/htdocs/workshop_booking_table/vendor/autoload.php'; // ปรับ path ถ้าใช้ lib ภายนอก

use Dompdf\Dompdf;
use Dompdf\Options;

$phone = $_POST['phone'] ?? '';
$name  = $_POST['name'] ?? '';

if ($phone == '') {
  die("❌ ข้อมูลไม่ถูกต้อง");
}

require_once 'condb.php';
if (!isset($conn) && isset($condb)) { $conn = $condb; }

$sql = "SELECT * FROM tbl_booking WHERE booking_phone = '$phone' ORDER BY booking_date DESC, booking_time DESC";
$result = mysqli_query($conn, $sql);

if (!$result || mysqli_num_rows($result) == 0) {
  die("❌ ไม่มีข้อมูลการจองของเบอร์นี้");
}

// 🧾 สร้างเนื้อหา HTML
$html = "
<h2 style='text-align:center;'>ประวัติการจองโต๊ะ - Deep D House</h2>
<p><strong>ชื่อผู้จอง:</strong> {$name}<br>
<strong>เบอร์โทร:</strong> {$phone}</p>
<table border='1' cellspacing='0' cellpadding='6' width='100%'>
<thead style='background-color:#f5a524;'>
<tr>
<th>#</th>
<th>วันที่</th>
<th>เวลา</th>
<th>ชื่อผู้จอง</th>
<th>สถานะการชำระเงิน</th>
</tr>
</thead>
<tbody>
";

while ($b = mysqli_fetch_assoc($result)) {
  $status = $b['payment_status'] == 'paid' ? '✅ ชำระแล้ว' :
             ($b['payment_status'] == 'pending' ? '⌛ รอตรวจสอบ' : '❌ ไม่ผ่าน');
  $html .= "
  <tr>
    <td>{$b['no']}</td>
    <td>{$b['booking_date']}</td>
    <td>{$b['booking_time']}</td>
    <td>{$b['booking_name']}</td>
    <td>{$status}</td>
  </tr>";
}
$html .= "</tbody></table><p style='text-align:center;margin-top:20px;'>ขอบคุณที่ใช้บริการ Deep D House ❤️</p>";

// 🖨️ แปลงเป็น PDF ด้วย Dompdf
require_once 'vendor/autoload.php';
$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("booking_history_{$phone}.pdf", ["Attachment" => true]);
exit;
?> -->
