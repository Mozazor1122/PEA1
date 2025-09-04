<?php
// เริ่มคำสั่ง Export ไฟล์ PDF
require_once __DIR__ . '/vendor/autoload.php';

$defaultConfig = (new Mpdf\Config\ConfigVariables())->getDefaults();
$fontDirs = $defaultConfig['fontDir'];

$defaultFontConfig = (new Mpdf\Config\FontVariables())->getDefaults();
$fontData = $defaultFontConfig['fontdata'];

$mpdf = new \Mpdf\Mpdf([
    'fontDir' => array_merge($fontDirs, [
        __DIR__ . '/tmp',
    ]),
    'fontdata' => $fontData + [
        'sarabun' => [
            'R' => 'THSarabunNew.ttf',
            'I' => 'THSarabunNew Italic.ttf',
            'B' => 'THSarabunNew Bold.ttf',
            'BI' => 'THSarabunNew BoldItalic.ttf'
        ]
    ], 
    'default_font' => 'sarabun'
]);
 // สิ้นสุดคำสั่ง Export ไฟล์ PDF ในส่วนบน เริ่มกำหนดตำแหน่งเริ่มต้นในการนำเนื้อหามาแสดงผลผ่าน
$mpdf->SetFont('sarabun','',14);
?>

<?php
require_once 'db.php';

// รับค่าเดือน/ปีจาก URL หรือใช้ของปัจจุบัน
$year = $_GET['year'] ?? date('Y');
$month = $_GET['month'] ?? date('m');

// หาวันเริ่มและวันสุดท้ายของเดือน
$month_start = "$year-$month-01";
$month_end = date("Y-m-t", strtotime($month_start));

// ดึงข้อมูล JOIN ตาราง devices และ status
$sql = "SELECT f.*, d.Device_name, s.Status_name 
        FROM form f
        JOIN devices d ON f.Device_id = d.Device_id
        JOIN status s ON f.status_id = s.Status_id
        WHERE f.Form_date BETWEEN ? AND ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param('ss', $month_start, $month_end);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8" />
  <title>ข้อมูลรายเดือน</title> 
  <link rel="stylesheet" href="assets/css/monthly.css" />
  <style>
    .btn.export {
      display: inline-block;
      background-color: #28a745;
      color: white;
      padding: 8px 16px;
      font-size: 14px;
      font-weight: bold;
      border: none;
      border-radius: 4px;
      text-decoration: none;
      transition: background-color 0.3s ease;
      float: right;
    }
    .btn.export:hover {
      background-color: #218838;
      text-decoration: none;
    }
    
  </style>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun&display=swap" rel="stylesheet">
</head>
<body>

<div class="container">
  <a href="main.html" class="btn-back">← กลับไปหน้า Main</a>
  <a href="Report.pdf" class="btn export" target="_blank">📄 Export PDF</a>
  <h2>ข้อมูลรายงานเดือน</h2>

  <form method="get" action="" class="filter-form">
  <label for="month">เลือกเดือน:</label>
  <select name="month" id="month">
    <?php
    $thaiMonths = [
        1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม',
        4 => 'เมษายน', 5 => 'พฤษภาคม', 6 => 'มิถุนายน',
        7 => 'กรกฎาคม', 8 => 'สิงหาคม', 9 => 'กันยายน',
        10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม'
    ];

    for ($m = 1; $m <= 12; $m++) {
        $value = str_pad($m, 2, '0', STR_PAD_LEFT);
        $selected = ($value == $month) ? 'selected' : '';
        echo "<option value='$value' $selected>{$thaiMonths[$m]}</option>";
    }
    ?>
  </select>

  <label for="year">เลือกปี:</label>
  <select name="year" id="year">
    <?php
    $currentYear = date('Y');
    for ($y = $currentYear - 2; $y <= $currentYear + 1; $y++) {
        $selected = ($y == $year) ? 'selected' : '';
        $thaiYear = $y + 543;
        echo "<option value='$y' $selected>$thaiYear</option>";
    }
    ?>
  </select>

    <button type="submit">แสดงผล</button>
  </form>

  <?php
function thaiMonthYear($date) {
    $months = [
        "January" => "มกราคม", "February" => "กุมภาพันธ์", "March" => "มีนาคม",
        "April" => "เมษายน", "May" => "พฤษภาคม", "June" => "มิถุนายน",
        "July" => "กรกฎาคม", "August" => "สิงหาคม", "September" => "กันยายน",
        "October" => "ตุลาคม", "November" => "พฤศจิกายน", "December" => "ธันวาคม"
    ];

    $timestamp = strtotime($date);
    $month_en = date('F', $timestamp);
    $month_th = $months[$month_en];
    $year_th = date('Y', $timestamp) + 543;

    return "$month_th $year_th";
}
?>

<?php
function formatThaiDateShort($dateStr) {
    $monthsShort = [
        "", "ม.ค.", "ก.พ.", "มี.ค.", "เม.ย.", "พ.ค.", "มิ.ย.",
        "ก.ค.", "ส.ค.", "ก.ย.", "ต.ค.", "พ.ย.", "ธ.ค."
    ];
    $date = new DateTime($dateStr);
    $day = $date->format('j');
    $month = $monthsShort[(int)$date->format('n')];
    $year = $date->format('Y') + 543;
    return "$day $month $year";
}
?>

<?php ob_start();  //ฟังก์ชัน ob_start() ?>
  <h3 style="text-align: center; font-size: 24pt; font-weight: bold; margin-bottom: 20px;">
  รายการที่ได้รับจัดสรรเดือน <?= thaiMonthYear($month_start) ?>
  </h3>

  <table>
    <thead>
      <tr>
        <th>ชื่อผู้จัดสรร</th>
        <th>รายการอุปกรณ์</th>
        <th>เลขที่สัญญา</th>
        <th>วันที่จัดสรร</th>
        <th>เลขที่บันทึกขอรับจัดสรร</th>
        <th>ชื่อหน่วยงาน</th>
        <th>สถานะ</th>
        <!-- <th>การจัดการ</th> -->
      </tr>
    </thead>
    <tbody>
      <?php while($row = $result->fetch_assoc()): ?>
        <tr>
          <td data-label="ชื่อผู้จัดสรร"><?= htmlspecialchars($row['Form_alloname']) ?></td>
          <td data-label="รายการอุปกรณ์"><?= htmlspecialchars($row['Device_name']) ?></td>
          <td data-label="เลขที่สัญญา"><?= htmlspecialchars($row['Form_contractnum']) ?></td>
          <td data-label="วันที่จัดสรร"><?= formatThaiDateShort($row['Form_date']) ?></td>
          <td data-label="เลขที่บันทึกขอรับจัดสรร"><?= htmlspecialchars($row['Form_requestnum']) ?></td>
          <td data-label="ชื่อหน่วยงาน"><?= htmlspecialchars($row['Form_agencyname']) ?></td>
          <td data-label="สถานะ"><?= htmlspecialchars($row['Status_name']) ?></td>
          <!-- <td data-label="การจัดการ">
            <?php if ($row['status_id'] == 1): ?>
              <a href="update_status_monthly.php?id=<?= $row['Form_id'] ?>&status=2&month=<?= $month ?>&year=<?= $year ?>" class="btn approve" title="จัดสรรแล้ว">✅</a>
              <a href="update_status_monthly.php?id=<?= $row['Form_id'] ?>&status=3&month=<?= $month ?>&year=<?= $year ?>" class="btn cancel" title="ยกเลิก" onclick="return confirm('ยืนยันการยกเลิก?');">❌</a>
            <?php else: ?>
              -
            <?php endif; ?>
            <a href="edit_form_monthly.php?id=<?= $row['Form_id'] ?>&month=<?= $month ?>&year=<?= $year ?>" class="btn edit" title="แก้ไข">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" style="vertical-align: middle;">
                <path d="M12.146.854a.5.5 0 0 1 .708 0l2.292 2.292a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168l10-10zM11.207 2L3 10.207V13h2.793L14 4.793 11.207 2z"/>
              </svg>
            </a>
            <a href="delete_form_monthly.php?id=<?= $row['Form_id'] ?>&month=<?= $month ?>&year=<?= $year ?>" class="btn delete" onclick="return confirm('ลบรายการนี้?');">ลบ</a>
          </td> -->
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
<?php
 // คำสั่งการ Export ไฟล์เป็น PDF
$html = ob_get_contents();      // เรียกใช้ฟังก์ชัน รับข้อมูลที่จะมาแสดงผล

$style = '
<style>
  body { font-family: sarabun, sans-serif; font-size: 11pt; }
  table { border-collapse: collapse; width: 100%; margin-top: 10px; }
  th, td { border: 1px solid #444; padding: 8px; text-align: center; }
  thead tr { background-color: #4CAF50; color: white; }
  tbody tr:nth-child(even) { background-color: #f2f2f2; }
</style>
';

$mpdf->WriteHTML($style);
$mpdf->WriteHTML($html);        // รับข้อมูลเนื้อหาที่จะแสดงผลผ่านตัวแปร $html
$mpdf->Output('Report.pdf');  //สร้างไฟล์ PDF ชื่อว่า myReport.pdf
ob_end_flush();                 // ปิดการแสดงผลข้อมูลของไฟล์ HTML ณ จุดนี้
?>
</div>

</body>
</html>
