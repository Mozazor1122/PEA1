<?php
// ดึงชื่อแอดมินจากฐานข้อมูล
require_once '../PEA1/db.php'; // เปลี่ยน path ถ้าไม่ตรง

$sql = "SELECT Admin_name FROM admin";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8" />
  <title>ฟอร์มจัดสรรอุปกรณ์</title>
  <link rel="stylesheet" href="../PEA1/assets/css/fstyle.css" />
</head>
<body>

  <form id="allocationForm">
    <h2>แบบฟอร์มจัดสรรอุปกรณ์</h2><br>

    <label>รายการอุปกรณ์:
      <select name="device_name" required>
        <option value="">-- เลือกรายการ --</option>
        <option value="เครื่องบันทึกการเก็บเงิน">เครื่องบันทึกการเก็บเงิน</option>
        <option value="เครื่องคอมพิวเตอร์">เครื่องคอมพิวเตอร์</option>
        <option value="เครื่องโน๊ตบุ๊ค">เครื่องโน๊ตบุ๊ค</option>
        <option value="เครื่องปริ้นเตอร์">เครื่องปริ้นเตอร์</option>
      </select>
    </label><br><br>

    <label>ชื่อผู้จัดสรร:
      <select name="allocator_name" required>
        <option value="">-- เลือกผู้จัดสรร --</option>
        <?php while ($row = $result->fetch_assoc()): ?>
          <option value="<?= htmlspecialchars($row['Admin_name']) ?>">
            <?= htmlspecialchars($row['Admin_name']) ?>
          </option>
        <?php endwhile; ?>
      </select>
    </label><br><br>


    <label>เลขที่สัญญา: <input type="text" name="contract_number" required></label><br>
    <!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <title>เลือกวันที่แบบไทย</title>
<link href="https://fonts.googleapis.com/css2?family=Sarabun&display=swap" rel="stylesheet">
  <!-- CSS ของ Flatpickr -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
</head>
<body>

  <label>วันที่จัดสรร:
    <input type="text" id="thai-date" name="allocation_date" required>
  </label>

  <!-- Flatpickr JS และ Thai Locale -->
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/th.js"></script>

  <!-- Flatpickr + แสดงปี พ.ศ. -->
  <script>
    flatpickr("#thai-date", {
      locale: "th",
      disableMobile: true,
      altInput: true, // ช่องแสดงผลแยก
      altFormat: "j F Y", // แสดงวันที่แบบไทย
      dateFormat: "Y-m-d", // ค่าที่ใช้จริงตอนส่งฟอร์ม (ค.ศ.)

      onValueUpdate: function(selectedDates, dateStr, instance) {
        const date = selectedDates[0];
        if (date && instance.altInput) {
          const buddhistYear = date.getFullYear() + 543;
          const day = date.getDate();
          const monthName = instance.l10n.months.longhand[date.getMonth()];
          instance.altInput.value = `${day} ${monthName} ${buddhistYear}`;
        }
      }
    });
  </script><br>

</body>
</html>

    <label>เลขที่บันทึกขอรับจัดสรร: <input type="text" name="request_number" required></label><br><br>
    <label>ชื่อหน่วยงาน: <input type="text" name="Form_agencyname" required></label><br><br>
    <label>ชื่อผู้รับจัดสรร: <input type="text" name="form_acname" required></label><br><br>

    <div class="form-buttons">
      <button type="submit">บันทึกข้อมูล</button>
      <a href="main.html" class="btn-back">ย้อมกลับ</a>
    </div>
  </form>

  <script>
    document.getElementById('allocationForm').addEventListener('submit', function(e) {
      e.preventDefault();

      const form = e.target;
      const formData = new FormData(form);

      fetch('insert.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.text())
      .then(data => {
        alert(data);
        if(data.includes('เรียบร้อย')) {
          form.reset();
        }
      })
      .catch(error => {
        alert('เกิดข้อผิดพลาด: ' + error);
      });
    });
  </script>
</body>
</html>