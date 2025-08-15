<?php
require_once 'db.php';

$form_id = $_GET['id'] ?? null;
$month = $_GET['month'] ?? date('m');
$year = $_GET['year'] ?? date('Y');

if (!$form_id) {
    echo "ไม่พบข้อมูลที่ต้องการแก้ไข";
    exit;
}

$stmt = $conn->prepare("SELECT * FROM form WHERE Form_id = ?");
$stmt->bind_param("i", $form_id);
$stmt->execute();
$result = $stmt->get_result();
$form = $result->fetch_assoc();

if (!$form) {
    echo "ไม่พบข้อมูลในระบบ";
    exit;
}

// ดึงชื่อแอดมิน สำหรับเลือกผู้จัดสรร
$sqlAdmins = "SELECT Admin_name FROM admin";
$resultAdmins = $conn->query($sqlAdmins);

$devices = $conn->query("SELECT * FROM devices");
$statuses = $conn->query("SELECT * FROM status");
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>แก้ไขรายการ (รายเดือน)</title>
    <link rel="stylesheet" href="../PEA1/assets/css/fstyle.css" />
</head>
<body>

<div class="container">

<h2>แก้ไขรายการจัดสรร (รายเดือน)</h2>

<form method="post" action="update_form_monthly.php">
    <input type="hidden" name="form_id" value="<?= htmlspecialchars($form['Form_id']) ?>">
    <input type="hidden" name="month" value="<?= htmlspecialchars($month) ?>">
    <input type="hidden" name="year" value="<?= htmlspecialchars($year) ?>">

    <label>ชื่อผู้จัดสรร:
      <select name="Form_alloname" required>
        <option value="">-- เลือกผู้จัดสรร --</option>
        <?php while ($row = $resultAdmins->fetch_assoc()):
          $selected = ($form['Form_alloname'] == $row['Admin_name']) ? 'selected' : '';
        ?>
          <option value="<?= htmlspecialchars($row['Admin_name']) ?>" <?= $selected ?>>
            <?= htmlspecialchars($row['Admin_name']) ?>
          </option>
        <?php endwhile; ?>
      </select>
    </label><br><br>

    <label>ชื่อผู้ขอจัดสรร:
      <input type="text" name="Form_acname" value="<?= htmlspecialchars($form['Form_acname']) ?>" required>
    </label><br><br>

    <label>เลขที่สัญญา:
      <input type="text" name="Form_contractnum" value="<?= htmlspecialchars($form['Form_contractnum']) ?>" required>
    </label><br><br>

    <label>วันที่จัดสรร:
      <input type="date" name="Form_date" value="<?= htmlspecialchars($form['Form_date']) ?>" required>
    </label><br><br>

    <label>เลขที่บันทึกขอรับจัดสรร:
      <input type="text" name="Form_requestnum" value="<?= htmlspecialchars($form['Form_requestnum']) ?>" required>
    </label><br><br>

    <label>ชื่อหน่วยงาน:
      <input type="text" name="Form_agencyname" value="<?= htmlspecialchars($form['Form_agencyname']) ?>" required>
    </label><br><br>

    <label>อุปกรณ์:
      <select name="Device_id" required>
        <?php while ($d = $devices->fetch_assoc()): ?>
          <option value="<?= htmlspecialchars($d['Device_id']) ?>" <?= ($d['Device_id'] == $form['Device_id']) ? 'selected' : '' ?>>
            <?= htmlspecialchars($d['Device_name']) ?>
          </option>
        <?php endwhile; ?>
      </select>
    </label><br><br>

    <label>สถานะ:
      <select name="status_id" required>
        <?php while ($s = $statuses->fetch_assoc()): ?>
          <option value="<?= htmlspecialchars($s['status_id']) ?>" <?= ($s['status_id'] == $form['status_id']) ? 'selected' : '' ?>>
            <?= htmlspecialchars($s['status_name']) ?>
          </option>
        <?php endwhile; ?>
      </select>
    </label><br><br>
    <div class="form-buttons">
    <button type="submit">บันทึกการแก้ไข</button>
    <a class="button" href="monthly.php?month=<?= htmlspecialchars($month) ?>&year=<?= htmlspecialchars($year) ?>">ย้อนกลับ</a>
</div>
</form>



</div>
</body>
</html>
