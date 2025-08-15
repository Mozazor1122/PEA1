<?php
require_once 'db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $agency = trim($_POST['agency_name'] ?? '');
    $device = trim($_POST['device_name'] ?? '');
    $number = trim($_POST['request_number'] ?? '');
    $note = trim($_POST['note'] ?? '');

    // ตรวจสอบค่าว่าง
    if ($agency === '' || $device === '' || $number === '') {
        echo json_encode([
            'success' => false,
            'message' => 'กรุณากรอกข้อมูลให้ครบถ้วน'
        ]);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO request_form (Agency_name, Device_name, Request_number, Note) VALUES (?, ?, ?, ?)");
    if (!$stmt) {
        echo json_encode([
            'success' => false,
            'message' => 'Prepare statement error: ' . $conn->error
        ]);
        exit;
    }

    $stmt->bind_param("ssss", $agency, $device, $number, $note);

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'ข้อมูลถูกบันทึกเรียบร้อยแล้ว'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Execute error: ' . $stmt->error
        ]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
}
