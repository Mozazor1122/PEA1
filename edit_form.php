<?php
require_once 'db.php';

$id = $_GET['id'] ?? '';
if (!$id) {
    echo "ไม่พบรหัสฟอร์ม"; exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['Form_alloname'];
    $agency = $_POST['Form_agencyname'];
    $contract = $_POST['Form_contractnum'];
    $requestnum = $_POST['Form_requestnum'];
    $device_id = $_POST['Device_id'];
    $form_date = $_POST['Form_date']; // รูปแบบ YYYY-MM-DD

    // อัปเดตข้อมูล
    $stmt = $conn->prepare("UPDATE form SET Form_alloname=?, Form_agencyname=?, Form_contractnum=?, Form_requestnum=?, Device_id=?, Form_date=? WHERE Form_id=?");
    $stmt->bind_param("ssssisi", $name, $agency, $contract, $requestnum, $device_id, $form_date, $id);
    $stmt->execute();

    header("Location: total_form.php");
    exit;
}

// ดึงข้อมูลฟอร์ม + รายการอุปกรณ์เพื่อสร้าง dropdown
$form = $conn->query("SELECT * FROM form WHERE Form_id = $id")->fetch_assoc();
$devices = $conn->query("SELECT Device_id, Device_name FROM devices");
?>

<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8" />
  <title>แก้ไขฟอร์มจัดสรร</title>
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="assets/css/edit_request.css" />
</head>
<body>
  <div class="form-container">
    <h2>แก้ไขฟอร์มจัดสรร</h2>
    <form method="post">
      <label>ผู้จัดสรร:</label>
      <input type="text" name="Form_alloname" value="<?= htmlspecialchars($form['Form_alloname']) ?>" required>

      <label>หน่วยงาน:</label>
      <input type="text" name="Form_agencyname" value="<?= htmlspecialchars($form['Form_agencyname']) ?>" required>

      <label>เลขที่สัญญา:</label>
      <input type="text" name="Form_contractnum" value="<?= htmlspecialchars($form['Form_contractnum']) ?>" required>

      <label>เลขคำขอ:</label>
      <input type="text" name="Form_requestnum" value="<?= htmlspecialchars($form['Form_requestnum']) ?>" required>

      <label>วันที่ฟอร์ม:</label>
      <input type="date" name="Form_date" value="<?= htmlspecialchars($form['Form_date']) ?>" required>

      <label>อุปกรณ์:</label>
      <select name="Device_id" required>
        <?php while ($device = $devices->fetch_assoc()): ?>
          <option value="<?= $device['Device_id'] ?>" <?= ($device['Device_id'] == $form['Device_id']) ? 'selected' : '' ?>>
            <?= htmlspecialchars($device['Device_name']) ?>
          </option>
        <?php endwhile; ?>
      </select>

      <div class="form-buttons-center">
      <button type="submit" class="btn-save">บันทึก</button>
      <a href="totaldata.php" class="btn-back">ย้อมกลับ</a>
      </div>
    </form>
  </div>
</body>
</html>
