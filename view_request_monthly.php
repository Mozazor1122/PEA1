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

// รับค่าเดือนและปีจาก GET หรือใช้ค่าปัจจุบัน
$year = $_GET['year'] ?? date('Y');
$month = $_GET['month'] ?? date('m');

// หาวันเริ่มต้นและวันสิ้นสุดของเดือน
$month_start = "$year-$month-01";
$month_end = date("Y-m-t", strtotime($month_start));

// ดึงข้อมูลจาก request_form
$sql = "SELECT * FROM request_form WHERE Request_date BETWEEN ? AND ? ORDER BY Request_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ss', $month_start, $month_end);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <title>รายการคำขอรายเดือน</title>
  <link rel="stylesheet" href="../PEA1/assets/css/view_request.css">
  <link href="https://fonts.googleapis.com/css2?family=Sarabun&display=swap" rel="stylesheet">
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
  <div class="btn-back-wrapper">
    <a href="main.html" class="btn-back">← กลับไปหน้า Main</a>
    <a href="Report.pdf" class="btn export" target="_blank">📄 Export PDF</a>
  </div>

  <h2>ข้อมูลรายเดือน</h2>
  <!-- ตารางหรือฟอร์มเลือกเดือน -->


  <form method="get" class="filter-form">
    <label for="month">เดือน:</label>
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

    <label for="year">ปี:</label>
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

<?php ob_start();  // ฟังก์ชัน ob_start() ?>

<?php
// ฟังก์ชันแปลงวันที่เป็นเดือน-ปีไทย
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

<h3 style="text-align: center; font-size: 24pt; font-weight: bold; margin-bottom: 20px;">
  รายการที่ไม่ได้รับจัดสรรเดือน <?= thaiMonthYear($month_start) ?>
</h3>

<table>
  <thead>
    <tr>
      <th>ชื่อหน่วยงาน</th>
      <th>อุปกรณ์ที่ขอรับ</th>
      <th>เลขที่บันทึก</th>
      <th>วันที่ขอรับ</th> <!-- ย้ายขึ้น -->
      <th>หมายเหตุ</th> <!-- ย้ายลง -->
    </tr>
  </thead>
  <tbody>
    <?php if ($result->num_rows > 0): ?>
      <?php while($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($row['Agency_name']) ?></td>
          <td><?= htmlspecialchars($row['Device_name']) ?></td>
          <td><?= htmlspecialchars($row['Request_number']) ?></td>
          <td><?= formatThaiDateShort($row['request_date']) ?></td> <!-- ย้ายขึ้น -->
          <td><?= htmlspecialchars($row['Note']) ?></td> <!-- ย้ายลง -->
        </tr>
      <?php endwhile; ?>
    <?php else: ?>
      <tr><td colspan="6">ไม่พบข้อมูลในเดือนที่เลือก</td></tr>
    <?php endif; ?>
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
</div>
</body>
</html>
