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

// รับค่า filter
$currentMonth = isset($_GET['month']) ? intval($_GET['month']) : date('n');
$currentYear = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$currentWeekInMonth = isset($_GET['week_in_month']) ? intval($_GET['week_in_month']) : 1;

// คำนวณช่วงวันที่สำหรับสัปดาห์ในเดือน
$startOfMonth = new DateTime("{$currentYear}-{$currentMonth}-01");
$weekOffset = ($currentWeekInMonth - 1) * 7;
$week_start_dt = clone $startOfMonth;
$week_start_dt->modify("+{$weekOffset} days");
$week_end_dt = clone $week_start_dt;
$week_end_dt->modify('+6 days');
$lastDayOfMonth = new DateTime($startOfMonth->format('Y-m-t'));
if ($week_end_dt > $lastDayOfMonth) {
    $week_end_dt = $lastDayOfMonth;
}

$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$week = isset($_GET['week']) ? intval($_GET['week']) : date('W');

// หาวันเริ่ม-จบของสัปดาห์
$startDate = new DateTime();
$startDate->setISODate($year, $week);
$week_start = $startDate->format('Y-m-d');
$startDate->modify('+6 days');
$week_end = $startDate->format('Y-m-d');

$sql = "SELECT * FROM request_form WHERE Request_date BETWEEN ? AND ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ss', $week_start, $week_end);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <title>รายการคำขอประจำสัปดาห์</title>
  <link rel="stylesheet" href="assets/css/view_request.css">
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

  <h2 style="text-align: center; font-size: 24pt; font-weight: bold; margin-bottom: 20px;">
    ข้อมูลไม่ได้รับจัดสรรรายสัปดาห์
  </h2>

  <!-- ฟอร์มเลือกเดือน ปี สัปดาห์ -->
<form method="get" class="filter-form">
  <label>เดือน:
    <select name="month">
      <?php 
      $thaiMonths = ['มกราคม','กุมภาพันธ์','มีนาคม','เมษายน','พฤษภาคม','มิถุนายน',
                    'กรกฎาคม','สิงหาคม','กันยายน','ตุลาคม','พฤศจิกายน','ธันวาคม'];
      for ($m = 1; $m <= 12; $m++): ?>
        <option value="<?= $m ?>" <?= $m == $currentMonth ? 'selected' : '' ?>>
          <?= $thaiMonths[$m - 1] ?>
        </option>
      <?php endfor; ?>
    </select>
  </label>

  <label>ปี:
    <select name="year">
      <?php for ($y = date('Y') - 2; $y <= date('Y') + 1; $y++): ?>
        <option value="<?= $y ?>" <?= $y == $currentYear ? 'selected' : '' ?>>
          <?= $y + 543 /* แปลง ค.ศ. เป็น พ.ศ. */ ?>
        </option>
      <?php endfor; ?>
    </select>
  </label>

  <label>สัปดาห์ที่:
    <select name="week_in_month">
      <?php for ($w = 1; $w <= 5; $w++): ?>
        <option value="<?= $w ?>" <?= $w == $currentWeekInMonth ? 'selected' : '' ?>>
          <?= "สัปดาห์ที่ $w" ?>
        </option>
      <?php endfor; ?>
    </select>
  </label>

  <button type="submit">แสดงข้อมูล</button>
</form>

  <?php ob_start();  //ฟังก์ชัน ob_start() ?>

  <?php
// แปลงเดือนเป็นชื่อเดือนภาษาไทย
$thaiMonths = ['มกราคม','กุมภาพันธ์','มีนาคม','เมษายน','พฤษภาคม','มิถุนายน',
               'กรกฎาคม','สิงหาคม','กันยายน','ตุลาคม','พฤศจิกายน','ธันวาคม'];
$thaiMonthName = $thaiMonths[$currentMonth - 1];
$thaiYear = $currentYear + 543;

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

  <h2 style="text-align: center; font-size: 24pt; font-weight: bold; margin-bottom: 20px;">
  ข้อมูลรายงานที่ไม่ได้รับจัดสรรสัปดาห์ <br>เดือน <?= $thaiMonthName ?> ปี <?= $thaiYear ?> (สัปดาห์ที่ <?= $currentWeekInMonth ?>)</h2>
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
  <?php while ($row = $result->fetch_assoc()): ?>
    <tr>
      <td><?= htmlspecialchars($row['Agency_name']) ?></td>
      <td><?= htmlspecialchars($row['Device_name']) ?></td>
      <td><?= htmlspecialchars($row['Request_number']) ?></td>
      <td><?= formatThaiDateShort($row['request_date']) ?></td> <!-- ย้ายขึ้น -->
      <td><?= htmlspecialchars($row['Note']) ?></td> <!-- ย้ายลง -->
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
