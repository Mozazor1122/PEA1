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

// ดึงชื่อหน่วยงานทั้งหมดไม่ซ้ำ
$agencies = [];
$res = $conn->query("SELECT DISTINCT Agency_name FROM request_form ORDER BY Agency_name");
while ($row = $res->fetch_assoc()) {
    $agencies[] = $row['Agency_name'];
}

// รับค่าหน่วยงานจาก dropdown (ถ้ามี)
$selectedAgency = $_GET['agency'] ?? '';

// เตรียม query
if ($selectedAgency && in_array($selectedAgency, $agencies)) {
    $stmt = $conn->prepare("SELECT * FROM request_form WHERE Agency_name = ? ORDER BY Request_date DESC");
    $stmt->bind_param("s", $selectedAgency);
} else {
    $stmt = $conn->prepare("SELECT * FROM request_form ORDER BY Request_date DESC");
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ข้อมูลตามหน่วยงาน</title>
    <link rel="stylesheet" href="assets/css/department.css">
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
    <a href="main.html" class="btn-back">← กลับไปหน้า Main</a>
    <a href="Report.pdf" class="btn export" target="_blank">📄 Export PDF</a>
    <h2>ข้อมูลไม่ได้รับจัดสรรตามหน่วยงาน</h2>

    <form method="GET">
        <label for="agency">เลือกหน่วยงาน:</label>
        <select name="agency" id="agency" onchange="this.form.submit()">
            <option value="">-- แสดงทั้งหมด --</option>
            <?php foreach ($agencies as $agency): ?>
                <option value="<?= htmlspecialchars($agency) ?>" <?= $agency === $selectedAgency ? 'selected' : '' ?>>
                    <?= htmlspecialchars($agency) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>
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
    รายงานที่ไม่ได้จัดสรรตามหน่วยงาน </h3>

    <table>
        <thead>
            <tr>
                <!-- <th>ลำดับ</th> -->
                <!-- <th>รหัสคำขอ</th> -->
                <th>ชื่อหน่วยงาน</th>
                <th>อุปกรณ์ที่ขอรับ</th>
                <th>เลขที่บันทึก</th>
                <th>วันที่ขอรับ</th> <!-- ย้ายขึ้น -->
                <th>หมายเหตุ</th> <!-- ย้ายลง -->
            </tr>
        </thead>
        <tbody>
            <?php $index = 1; ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    
                    <!-- -->
                    <td><?= htmlspecialchars($row['Agency_name']) ?></td>
                    <td><?= htmlspecialchars($row['Device_name']) ?></td>
                    <td><?= htmlspecialchars($row['Request_number']) ?></td>
                    <td><?= formatThaiDateShort($row['request_date']) ?></td> 
                    <td><?= htmlspecialchars($row['Note']) ?></td>
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
