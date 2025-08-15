<?php
require_once 'db.php';

$sql = "SELECT * FROM request_form ORDER BY Request_date DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8" />
  <title>รายการคำขอรับจัดสรร</title>
  <link href="../PEA1/assets/css/view_request.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Sarabun&display=swap" rel="stylesheet">
</head>
<body>
  <div class="container">

    <!-- ปุ่มย้อนกลับ แยกบรรทัด -->
    <a href="main.html" class="btn-back">ย้อนกลับ</a>

    <!-- หัวข้อกึ่งกลาง -->
    <h2>รายการคำขอรับจัดสรร</h2>

    <table>
      <thead>
        <tr>
          <th>ลำดับ</th>
          <th>หน่วยงาน</th>
          <th>รายการอุปกรณ์</th>
          <th>เลขที่บันทึก</th>
          <th>หมายเหตุ</th>
          <th>วันที่ขอรับจัดสรร</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $i = 1;
        if ($result->num_rows > 0):
          while($row = $result->fetch_assoc()):
        ?>
          <tr>
            <td data-label="ลำดับ"><?= $i++ ?></td>
            <td data-label="หน่วยงาน"><?= htmlspecialchars($row['Agency_name']) ?></td>
            <td data-label="รายการอุปกรณ์"><?= htmlspecialchars($row['Device_name']) ?></td>
            <td data-label="เลขที่บันทึก"><?= htmlspecialchars($row['Request_number']) ?></td>
            <td data-label="หมายเหตุ"><?= htmlspecialchars($row['Note']) ?></td>
            <td data-label="วันที่ขอรับจัดสรร"><?= htmlspecialchars($row['request_date']) ?></td>
          </tr>
        <?php
          endwhile;
        else:
        ?>
          <tr>
            <td colspan="6" style="text-align:center;">ไม่มีข้อมูล</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</body>
</html>
