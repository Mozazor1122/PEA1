<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'db.php';

// ตรวจสอบการเชื่อมต่อฐานข้อมูล
if (!$conn) {
    http_response_code(500);
    echo "เชื่อมต่อฐานข้อมูลล้มเหลว: " . mysqli_connect_error();
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // รับข้อมูลจากฟอร์ม
    $device_name      = trim($_POST['device_name'] ?? '');
    $allocator_name   = trim($_POST['allocator_name'] ?? '');
    $form_acname      = trim($_POST['form_acname'] ?? '');
    $contract_number  = trim($_POST['contract_number'] ?? '');
    $allocation_date  = trim($_POST['allocation_date'] ?? '');
    $request_number   = trim($_POST['request_number'] ?? '');
    $Form_agencyname  = trim($_POST['Form_agencyname'] ?? '');

    // ตรวจสอบว่ากรอกครบหรือไม่
    if (
        $device_name === '' || $allocator_name === '' || $form_acname === '' ||
        $contract_number === '' || $allocation_date === '' ||
        $request_number === '' || $Form_agencyname === ''
    ) {
        http_response_code(400);
        echo 'กรุณากรอกข้อมูลให้ครบถ้วน';
        exit;
    }

    // --- Debug: แสดงค่าที่ได้รับจากฟอร์ม ---
    error_log("DEBUG: device_name = '$device_name'");

    // แปลงชื่ออุปกรณ์เป็น Device_id
    $stmt_device = $conn->prepare("SELECT Device_id FROM devices WHERE Device_name = ?");
    if (!$stmt_device) {
        http_response_code(500);
        echo "Prepare statement ล้มเหลว: " . $conn->error;
        exit;
    }

    $stmt_device->bind_param("s", $device_name);
    $stmt_device->execute();
    $result_device = $stmt_device->get_result();

    if ($result_device->num_rows === 0) {
        http_response_code(400);
        echo "ไม่พบอุปกรณ์ที่เลือก: '$device_name'";
        exit;
    }

    $device_row = $result_device->fetch_assoc();
    $device_id = $device_row['Device_id'];
    $stmt_device->close();

    // ค่าคงที่อื่นๆ
    $admin_id = $_SESSION['admin_id'] ?? 1;
    $status_id = 1; // จัดสรรแล้ว

    // เตรียมคำสั่ง insert
    $stmt = $conn->prepare("INSERT INTO form 
      (Form_alloname, Form_acname, Form_contractnum, Form_date, Form_requestnum, Form_agencyname, Admin_id, Device_id, status_id) 
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

    if (!$stmt) {
        http_response_code(500);
        echo 'Prepare failed: ' . $conn->error;
        exit;
    }

    $stmt->bind_param(
        "ssssssiii",
        $allocator_name,
        $form_acname,
        $contract_number,
        $allocation_date,
        $request_number,
        $Form_agencyname,
        $admin_id,
        $device_id,
        $status_id
    );

    if ($stmt->execute()) {
        echo "บันทึกข้อมูลเรียบร้อยแล้ว";
    } else {
        http_response_code(500);
        echo "เกิดข้อผิดพลาด: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    http_response_code(405);
    echo "Method not allowed";
}
?>
