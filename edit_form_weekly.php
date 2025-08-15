<?php
require_once 'db.php';

$form_id = $_GET['id'] ?? null;
if (!$form_id) {
    echo "ไม่พบข้อมูลที่ต้องการแก้ไข";
    exit;
}

$year = $_GET['year'] ?? date('Y');
$week = $_GET['week'] ?? date('W');

// ดึงข้อมูลฟอร์ม
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
?>

<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8" />
  <title>แก้ไขรายการ (รายสัปดาห์)</title>
  <link rel="stylesheet" href="../PEA1/assets/css/fstyle.css" />
</head>
<body>

  <form method="post" action="update_form_weekly.php" id="editFormWeekly">
    <h2>แก้ไขรายการจัดสรร (สัปดาห์ที่ <?= htmlspecialchars($week) ?> ปี <?= htmlspecialchars($year) ?>)</h2><br>

    <input type="hidden" name="form_id" value="<?= htmlspecialchars($form['Form_id']) ?>">
    <input type="hidden" name="year" value="<?= htmlspecialchars($year) ?>">
    <input type="hidden" name="week" value="<?= htmlspecialchars($week) ?>">

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

<label>รายการอุปกรณ์:
  <select name="Device_id" required>
    <option value="">-- เลือกรายการ --</option>
    <?php
      $devices = $conn->query("SELECT * FROM devices");
      while ($d = $devices->fetch_assoc()):
        $selected = ($form['Device_id'] == $d['Device_id']) ? 'selected' : '';
    ?>
      <option value="<?= htmlspecialchars($d['Device_id']) ?>" <?= $selected ?>>
        <?= htmlspecialchars($d['Device_name']) ?>
      </option>
    <?php endwhile; ?>
  </select>
</label><br><br>


    <label>สถานะ:
      <select name="status_id" required>
        <?php
          // ดึงสถานะจากฐานข้อมูลใหม่อีกครั้ง เพราะ $statuses ยังไม่ได้โหลดในนี้
          $statuses = $conn->query("SELECT * FROM status");
          while ($s = $statuses->fetch_assoc()):
            $selected = ($s['status_id'] == $form['status_id']) ? 'selected' : '';
        ?>
          <option value="<?= htmlspecialchars($s['status_id']) ?>" <?= $selected ?>>
            <?= htmlspecialchars($s['status_name']) ?>
          </option>
        <?php endwhile; ?>
      </select>
    </label><br><br>

    <div class="form-buttons">
      <button type="submit">บันทึกการแก้ไข</button>
      <button type="button" class="button" onclick="location.href='weekly.php?year=<?= htmlspecialchars($year) ?>&week=<?= htmlspecialchars($week) ?>'">ย้อนกลับ</button>
    </div>

  </form>

</body>
</html>
