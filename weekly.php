<?php
// เริ่ม Export PDF
require_once __DIR__ . '/vendor/autoload.php';
$defaultConfig = (new Mpdf\Config\ConfigVariables())->getDefaults();
$fontDirs = $defaultConfig['fontDir'];
$defaultFontConfig = (new Mpdf\Config\FontVariables())->getDefaults();
$fontData = $defaultFontConfig['fontdata'];
$mpdf = new \Mpdf\Mpdf([
    'fontDir' => array_merge($fontDirs, [__DIR__ . '/tmp']),
    'fontdata' => $fontData + [
        'sarabun' => [
            'R' => 'THSarabunNew.ttf',
            'I' => 'THSarabunNew Italic.ttf',
            'B' => 'THSarabunNew Bold.ttf',
            'BI'=> 'THSarabunNew BoldItalic.ttf'
        ]
    ],
    'default_font' => 'sarabun'
]);
$mpdf->SetFont('sarabun','',14);

// เชื่อมฐานข้อมูล
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
$week_start = $week_start_dt->format('Y-m-d');
$week_end = $week_end_dt->format('Y-m-d');

// ดึงข้อมูลจากฐานข้อมูล
$sql = "SELECT f.*, d.Device_name, s.Status_name 
        FROM form f
        JOIN devices d ON f.Device_id = d.Device_id
        JOIN status s ON f.status_id = s.Status_id
        WHERE f.Form_date BETWEEN ? AND ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ss', $week_start, $week_end);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <title>ข้อมูลรายงานสัปดาห์</title>
  <link rel="stylesheet" href="assets/css/weekly.css" />
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
</head>
<body>

<div class="container">
  <a href="main.html" class="btn-back">← กลับไปหน้า Main</a>
  <a href="Report.pdf?month=<?= $currentMonth ?>&year=<?= $currentYear ?>&week_in_month=<?= $currentWeekInMonth ?>" class="btn export" target="_blank">
    📄 Export PDF
  </a>

  <h2 style="text-align: center; font-size: 24pt; font-weight: bold; margin-bottom: 20px;">
    ข้อมูลรายงานสัปดาห์
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
            <?= $y + 543 ?>
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

<?php
  // ใช้ output buffering สำหรับ PDF
  ob_start();

  // แปลงเดือนเป็นชื่อเดือนภาษาไทย
  $thaiMonthName = $thaiMonths[$currentMonth - 1];
  $thaiYear = $currentYear + 543;

  // ฟังก์ชันแปลงวันที่แบบไทย
  function formatThaiDate($dateStr) {
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
  รายการที่ได้รับจัดสรร<br>
  เดือน <?= $thaiMonthName ?> ปี <?= $thaiYear ?> (สัปดาห์ที่ <?= $currentWeekInMonth ?>)
</h3>

<table>
  <thead>
    <tr>
      <th>ชื่อผู้จัดสรร</th><th>รายการอุปกรณ์</th>
      <th>เลขที่สัญญา</th><th>วันที่จัดสรร</th><th>เลขที่บันทึกขอรับจัดสรร</th>
      <th>ชื่อหน่วยงาน</th><th>สถานะ</th>
    </tr>
  </thead>
  <tbody>
    <?php while($row = $result->fetch_assoc()): ?>
    <tr>
      <td><?= htmlspecialchars($row['Form_alloname']) ?></td>
      <td><?= htmlspecialchars($row['Device_name']) ?></td>
      <td><?= htmlspecialchars($row['Form_contractnum']) ?></td>
      <td><?= formatThaiDate($row['Form_date']) ?></td>
      <td><?= htmlspecialchars($row['Form_requestnum']) ?></td>
      <td><?= htmlspecialchars($row['Form_agencyname']) ?></td>
      <td><?= htmlspecialchars($row['Status_name']) ?></td>
    </tr>
    <?php endwhile; ?>
  </tbody>
</table>

<?php
  $html = ob_get_contents();
  $style = '
 <style>
  body { font-family: sarabun, sans-serif; font-size: 11pt; }
  table { border-collapse: collapse; width: 100%; margin-top: 10px; }
  th, td { border: 1px solid #444; padding: 8px; text-align: center; }
  thead tr { background-color: #4CAF50; color: white; }
  tbody tr:nth-child(even) { background-color: #f2f2f2; }
</style>';
  $mpdf->WriteHTML($style);
  $mpdf->WriteHTML($html);
  $mpdf->Output('Report.pdf');
  ob_end_flush();
?>
</div>
</body>
</html>
