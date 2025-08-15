<?php
require_once 'db.php';

$form_id = $_POST['form_id'] ?? null;
$allocator_name   = trim($_POST['Form_alloname'] ?? '');
$form_acname      = trim($_POST['Form_acname'] ?? '');
$contract_number  = trim($_POST['Form_contractnum'] ?? '');
$allocation_date  = trim($_POST['Form_date'] ?? '');
$request_number   = trim($_POST['Form_requestnum'] ?? '');
$agency_name      = trim($_POST['Form_agencyname'] ?? '');
$device_id        = intval($_POST['Device_id'] ?? 0);
$status_id        = intval($_POST['status_id'] ?? 1);
$month            = $_POST['month'] ?? date('m');
$year             = $_POST['year'] ?? date('Y');

if (!$form_id || $allocator_name === '' || $form_acname === '' || $contract_number === '' ||
    $allocation_date === '' || $request_number === '' || $agency_name === '' || $device_id === 0) {
    echo "กรุณากรอกข้อมูลให้ครบถ้วน";
    exit;
}

$stmt = $conn->prepare("UPDATE form SET
    Form_alloname = ?, 
    Form_acname = ?, 
    Form_contractnum = ?, 
    Form_date = ?, 
    Form_requestnum = ?, 
    Form_agencyname = ?, 
    Device_id = ?, 
    status_id = ?
    WHERE Form_id = ?");

$stmt->bind_param("ssssssiii",
    $allocator_name,
    $form_acname,
    $contract_number,
    $allocation_date,
    $request_number,
    $agency_name,
    $device_id,
    $status_id,
    $form_id
);

if ($stmt->execute()) {
    header("Location: monthly.php?month=$month&year=$year&success=1");
    exit;
} else {
    echo "เกิดข้อผิดพลาด: " . $stmt->error;
}
