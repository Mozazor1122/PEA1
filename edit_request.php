<?php
require_once 'db.php';

$id = $_GET['id'] ?? '';
if (!$id) {
    echo "ไม่พบรหัสคำขอ"; exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $agency = $_POST['Agency_name'];
    $device = $_POST['Device_name'];
    $requestnum = $_POST['Request_number'];
    $requestdate = $_POST['Request_date'];

    $stmt = $conn->prepare("UPDATE request_form SET Agency_name=?, Device_name=?, Request_number=?, Request_date=? WHERE Request_id=?");
    $stmt->bind_param("ssssi", $agency, $device, $requestnum, $requestdate, $id);
    $stmt->execute();

    header("Location: totaldata.php");
    exit;
}

$agencyResult = $conn->query("SELECT DISTINCT Agency_name FROM request_form WHERE Agency_name IS NOT NULL AND Agency_name <> '' ORDER BY Agency_name ASC");
$deviceResult = $conn->query("SELECT Device_name FROM devices ORDER BY Device_name ASC");
$request = $conn->query("SELECT * FROM request_form WHERE Request_id = $id")->fetch_assoc();

?>

<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8" />
  <title>แก้ไขคำขอ</title>
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="assets/css/edit_request.css" />
</head>
<body>
  <div class="form-container">
    <h2>แก้ไขฟอร์มไม่ได้จัดสรร</h2>
    <form method="post">

      <label>หน่วยงาน:</label>
      <input list="agency-list" name="Agency_name" value="<?= htmlspecialchars($request['Agency_name']) ?>" required>
      <datalist id="agency-list">
        <?php while($row = $agencyResult->fetch_assoc()): ?>
          <option value="<?= htmlspecialchars($row['Agency_name']) ?>">
        <?php endwhile; ?>
      </datalist>

      <label>อุปกรณ์:</label>
      <select name="Device_name" required>
        <option value="">-- เลือกอุปกรณ์ --</option>
        <?php
          $deviceResult->data_seek(0);
          while($row = $deviceResult->fetch_assoc()):
            $selected = ($row['Device_name'] === $request['Device_name']) ? 'selected' : '';
        ?>
          <option value="<?= htmlspecialchars($row['Device_name']) ?>" <?= $selected ?>><?= htmlspecialchars($row['Device_name']) ?></option>
        <?php endwhile; ?>
      </select>

      <label>เลขคำขอ:</label>
      <input type="text" name="Request_number" value="<?= htmlspecialchars($request['Request_number']) ?>" required>

      <label>วันที่คำขอ:</label>
      <input type="date" name="request_date" value="<?= htmlspecialchars($request['request_date']) ?>" required>

     <div class="form-buttons-center">
      <button type="submit" class="btn-save">บันทึก</button>
      <a href="totaldata.php" class="btn-back">ย้อมกลับ</a>
  </div>
    </form>
  </div>
</body>
</html>
