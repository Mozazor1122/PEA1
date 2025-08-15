<?php
require_once 'db.php';

$year = date('Y');

$sql = "SELECT * FROM form WHERE YEAR(Form_date) = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $year);
$stmt->execute();
$result = $stmt->get_result();
?>

<h2>ข้อมูลรายปี (ปี <?php echo $year; ?>)</h2>
<table border="1" cellpadding="5" cellspacing="0">
  <thead>
    <tr>
      <th>ชื่อผู้จัดสรร</th>
      <th>รายการอุปกรณ์</th>
      <th>เลขที่สัญญา</th>
      <th>วันที่จัดสรร</th>
      <th>เลขที่บันทึกขอรับจัดสรร</th>
      <th>ชื่อหน่วยงาน</th>
    </tr>
  </thead>
  <tbody>
<?php while($row = $result->fetch_assoc()): ?>
    <tr>
      <td><?php echo htmlspecialchars($row['Form_alloname']); ?></td>
      <td><?php echo htmlspecialchars($row['Form_acname']); ?></td>
      <td><?php echo htmlspecialchars($row['Form_contractnum']); ?></td>
      <td><?php echo htmlspecialchars($row['Form_date']); ?></td>
      <td><?php echo htmlspecialchars($row['Form_requestnum']); ?></td>
      <td><?php echo htmlspecialchars($row['Form_agencyname']); ?></td>
    </tr>
<?php endwhile; ?>
  </tbody>
</table>
