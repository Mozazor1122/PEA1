<?php
// เริ่ม Export PDF
require_once __DIR__ . '/vendor/autoload.php';
$defaultConfig = (new Mpdf\Config\ConfigVariables())->getDefaults();
$fontDirs = $defaultConfig['fontDir'];
$defaultFontConfig = (new Mpdf\Config\FontVariables())->getDefaults();
$fontData = $defaultFontConfig['fontdata'];
$mpdf = new \Mpdf\Mpdf([
    'fontDir' => array_merge((new \Mpdf\Config\ConfigVariables())->getDefaults()['fontDir'], [__DIR__ . '/fonts']),
    'fontdata' => (new \Mpdf\Config\FontVariables())->getDefaults()['fontdata'] + [
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

require_once 'db.php';
require_once __DIR__ . '/vendor/autoload.php';

// แปลงวันที่เป็นรูปแบบไทย
function formatThaiDateShort($dateStr) {
    $months = ["", "ม.ค.", "ก.พ.", "มี.ค.", "เม.ย.", "พ.ค.", "มิ.ย.",
               "ก.ค.", "ส.ค.", "ก.ย.", "ต.ค.", "พ.ย.", "ธ.ค."];
    $date = new DateTime($dateStr);
    return $date->format('j') . ' ' . $months[(int)$date->format('n')] . ' ' . ($date->format('Y') + 543);
}

// รับค่าจากฟอร์ม
$month = $_GET['month'] ?? 'all';
$year = $_GET['year'] ?? 'all';
$agency = $_GET['agency'] ?? 'all';
$contract = $_GET['contract'] ?? '';

// ===== สร้าง WHERE แยก =====
$formCon = [];
$requestCon = [];

if ($month !== 'all' && $year !== 'all') {
    // กรองทั้งเดือน + ปี
    $start = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01";
    $end = date("Y-m-t", strtotime($start));
    $formCon[] = "Form_date BETWEEN '$start' AND '$end'";
    $requestCon[] = "Request_date BETWEEN '$start' AND '$end'";
} elseif ($month !== 'all') {
    // กรองเฉพาะเดือน (ทุกปี)
    $formCon[] = "MONTH(Form_date) = '$month'";
    $requestCon[] = "MONTH(Request_date) = '$month'";
} elseif ($year !== 'all') {
    // กรองเฉพาะปี
    $formCon[] = "YEAR(Form_date) = '$year'";
    $requestCon[] = "YEAR(Request_date) = '$year'";
}


if ($agency !== 'all') {
    $formCon[] = "Form_agencyname = '" . $conn->real_escape_string($agency) . "'";
    $requestCon[] = "Agency_name = '" . $conn->real_escape_string($agency) . "'";
}

if (!empty($contract)) {
    $formCon[] = "Form_contractnum LIKE '%" . $conn->real_escape_string($contract) . "%'";
}

$whereForm = !empty($formCon) ? "WHERE " . implode(" AND ", $formCon) : "";
$whereRequest = !empty($requestCon) ? "WHERE " . implode(" AND ", $requestCon) : "";

// ===== Query รวมฟอร์ม =====
$sql = "
(
  SELECT 
    f.Form_id AS id,
    'form' AS type,
    f.Form_alloname AS name,
    f.Form_agencyname AS agency,
    d.Device_name AS device,
    f.Form_contractnum AS contractnum,
    f.Form_requestnum AS requestnum,
    f.Form_date AS form_date,
    s.Status_name AS status
  FROM form f
  JOIN devices d ON f.Device_id = d.Device_id
  JOIN status s ON f.status_id = s.status_id
  $whereForm
)
UNION ALL
(
  SELECT 
    r.Request_id AS id,
    'request' AS type,
    NULL AS name,
    r.Agency_name AS agency,
    r.Device_name AS device,
    NULL AS contractnum,
    r.Request_number AS requestnum,
    r.Request_date AS form_date,
    NULL AS status
  FROM request_form r
  $whereRequest
)
ORDER BY form_date DESC
";

$result = $conn->query($sql);

if (isset($_GET['export']) && $_GET['export'] === 'pdf') {
    ob_start();
?>
<!-- CSS + HTML ทั้งหมดตรงนี้ -->
<style>
  body { font-family: sarabun, sans-serif; font-size: 11pt; }
  table { border-collapse: collapse; width: 100%; margin-top: 10px; }
  th, td { border: 1px solid #444; padding: 8px; text-align: center; }
  thead tr { background-color: #4CAF50; color: white; }
  tbody tr:nth-child(even) { background-color: #f2f2f2; }
</style>

<h3 style="text-align: center; font-size: 20pt; font-weight: bold; margin-bottom: 20px;">รายงานรายการรวม</h3>
<table>
  <thead>
    <tr>
      <th>ประเภท</th>
      <th>ผู้จัดสรร</th>
      <th>หน่วยงาน</th>
      <th>อุปกรณ์</th>
      <th>สัญญา</th>
      <th>วันที่</th>
      <th>คำขอ</th>
      <th>สถานะ</th>
    </tr>
  </thead>
  <tbody>
    <?php while($row = $result->fetch_assoc()): ?>
    <tr>
      <td><?= $row['type'] === 'form' ? 'ฟอร์มจัดสรร' : 'คำขอ' ?></td>
      <td><?= $row['name'] ?? '-' ?></td>
      <td><?= $row['agency'] ?></td>
      <td><?= $row['device'] ?></td>
      <td><?= $row['contractnum'] ?? '-' ?></td>
      <td><?= formatThaiDateShort($row['form_date']) ?></td>
      <td><?= $row['requestnum'] ?></td>
      <td><?= $row['status'] ?? '-' ?></td>
    </tr>
    <?php endwhile; ?>
  </tbody>
</table>
<?php
$html = ob_get_clean();
$mpdf->WriteHTML($html);
$mpdf->Output('Report.pdf', 'I');
exit;
}

?>

<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <title>รายการรวมฟอร์ม</title>
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="assets/css/totaldata.css" />
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
  <div class="btn-back-wrapper">
    <a href="main.html" class="btn-back">← กลับไปหน้า Main</a>
    <a href="?export=pdf&month=<?= $month ?>&year=<?= $year ?>&agency=<?= urlencode($agency) ?>&contract=<?= urlencode($contract) ?>" class="btn export" target="_blank">📄 Export PDF</a>
  </div>
  <h2>รายการรวมจากฟอร์มการจัดสรรและฟอร์มไม่ได้รับจัดสรร</h2>

  <form method="get" class="filter-form">
    <label>เดือน:
      <select name="month">
        <option value="all">ทั้งหมด</option>
        <?php
        $months = ['มกราคม','กุมภาพันธ์','มีนาคม','เมษายน','พฤษภาคม','มิถุนายน','กรกฎาคม','สิงหาคม','กันยายน','ตุลาคม','พฤศจิกายน','ธันวาคม'];
        for ($m = 1; $m <= 12; $m++):
          $selected = ($month == $m) ? 'selected' : '';
        ?>
          <option value="<?= $m ?>" <?= $selected ?>><?= $months[$m - 1] ?></option>
        <?php endfor; ?>
      </select>
    </label>

    <label>ปี:
      <select name="year">
        <option value="all">ทั้งหมด</option>
        <?php for ($y = date('Y') - 2; $y <= date('Y'); $y++): ?>
          <option value="<?= $y ?>" <?= $year == $y ? 'selected' : '' ?>><?= $y + 543 ?></option>
        <?php endfor; ?>
      </select>
    </label>

    <label>หน่วยงาน:
      <select name="agency">
        <option value="all">ทั้งหมด</option>
        <?php
        $res = $conn->query("SELECT DISTINCT Agency_name AS name FROM request_form WHERE Agency_name IS NOT NULL
                            UNION
                            SELECT DISTINCT Form_agencyname AS name FROM form WHERE Form_agencyname IS NOT NULL
                            ORDER BY name");
        while ($a = $res->fetch_assoc()):
          $selected = ($agency == $a['name']) ? 'selected' : '';
        ?>
          <option value="<?= htmlspecialchars($a['name']) ?>" <?= $selected ?>><?= htmlspecialchars($a['name']) ?></option>
        <?php endwhile; ?>
      </select>
    </label>

    <div class="contract-search">
      <label style="margin: 0;">เลขที่สัญญา:
        <input type="text" name="contract" value="<?= htmlspecialchars($contract) ?>">
      </label>
      <button type="submit">ค้นหา</button>
    </div>
  </form>

  <table>
    <thead>
      <tr>
        <th>ประเภท</th>
        <th>ผู้จัดสรร</th>
        <th>หน่วยงาน</th>
        <th>อุปกรณ์</th>
        <th>สัญญา</th>
        <th>วันที่</th>
        <th>คำขอ</th>
        <th>สถานะ</th>
        <th>จัดการ</th>
      </tr>
    </thead>
    <tbody>
    <?php if ($result->num_rows > 0): ?>
      <?php while($row = $result->fetch_assoc()): ?>
      <tr>
        <td><?= $row['type'] === 'form' ? 'ฟอร์มจัดสรร' : 'ไม่ได้จัดสรร' ?></td>
        <td><?= $row['name'] ?? '-' ?></td>
        <td><?= $row['agency'] ?></td>
        <td><?= $row['device'] ?></td>
        <td><?= $row['contractnum'] ?? '-' ?></td>
        <td><?= formatThaiDateShort($row['form_date']) ?></td>
        <td><?= $row['requestnum'] ?></td>
        <td><?= $row['status'] ?? '-' ?></td>
        <td>
          <?php if ($row['type'] === 'form'): ?>
            <a href="edit_form.php?id=<?= $row['id'] ?>" class="btn-action" title="แก้ไข"><i class='bx bx-edit'></i></a>
            <a href="delete_form.php?id=<?= $row['id'] ?>" class="btn-action" title="ลบ" onclick="return confirm('ยืนยันการลบ?')"><i class='bx bx-trash'></i></a>
          <?php else: ?>
            <a href="edit_request.php?id=<?= $row['id'] ?>" class="btn-action" title="แก้ไข"><i class='bx bx-edit'></i></a>
            <a href="delete_request.php?id=<?= $row['id'] ?>" class="btn-action" title="ลบ" onclick="return confirm('ยืนยันการลบ?')"><i class='bx bx-trash'></i></a>
          <?php endif; ?>
        </td>
      </tr>
      <?php endwhile; ?>
    <?php else: ?>
      <tr><td colspan="9">ไม่พบข้อมูล</td></tr>
    <?php endif; ?>
    </tbody>
  </table>

 
</div>
</body>
</html>