<?php
require_once 'db.php';

// ดึงรายการอุปกรณ์ทั้งหมดจากตาราง devices
$devices = [];
$result = $conn->query("SELECT * FROM devices ORDER BY Device_name");
while ($row = $result->fetch_assoc()) {
    $devices[] = $row;
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8" />
  <title>ฟอร์มไม่ได้รับจัดสรร</title>
  <link href="https://fonts.googleapis.com/css2?family=Sarabun&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="assets/css/request_form.css" />
  <style>
    label {
      display: block;
      margin-bottom: 5px;
      font-weight: 600;
    }
  </style>
  <link href="https://fonts.googleapis.com/css2?family=Sarabun&display=swap" rel="stylesheet">
</head>
<body>
  <form id="requestForm" class="form-box" method="post">
    <h2>แบบฟอร์มไม่ได้รับจัดสรร</h2>

    <label for="agency_name">ชื่อหน่วยงาน</label>
    <input type="text" id="agency_name" name="agency_name" required />

    <label for="device_name">รายการอุปกรณ์</label>
    <select id="device_name" name="device_name" required>
      <option value="">-- เลือกรายการอุปกรณ์ --</option>
      <?php foreach ($devices as $device): ?>
        <option value="<?= htmlspecialchars($device['Device_name']) ?>">
          <?= htmlspecialchars($device['Device_name']) ?>
        </option>
      <?php endforeach; ?>
    </select>

    <label for="request_number">เลขที่บันทึกขอรับจัดสรร</label>
    <input type="text" id="request_number" name="request_number" required />

    <label for="note">หมายเหตุ (ถ้ามี)</label>
    <textarea id="note" name="note"></textarea>

    <div class="button-group" style="display: flex; justify-content: center; gap: 10px; margin-top: 10px;">
      <button type="submit" class="action-btn">บันทึก</button>
      <a href="main.html" class="button-back">ย้อนกลับ</a>
    </div>
  </form>

  <!-- SweetAlert2 CDN -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <script>
    document.getElementById('requestForm').addEventListener('submit', function(e) {
      e.preventDefault();

      const formData = new FormData(this);

      fetch('save_request.php', {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          Swal.fire({
            icon: 'success',
            title: 'บันทึกสำเร็จ',
            text: data.message,
            confirmButtonText: 'ตกลง'
          });
          this.reset();
        } else {
          Swal.fire({
            icon: 'error',
            title: 'เกิดข้อผิดพลาด',
            text: data.message
          });
        }
      })
      .catch(err => {
        Swal.fire({
          icon: 'error',
          title: 'ข้อผิดพลาดในการเชื่อมต่อ',
          text: err.message || err
        });
      });
    });
  </script>
</body>
</html>
