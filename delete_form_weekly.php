<?php
require_once 'db.php';

$form_id = $_GET['id'] ?? null;
$week    = $_GET['week'] ?? date('W');
$year    = $_GET['year'] ?? date('Y');

if (!$form_id) {
    echo "ไม่พบ ID ที่ต้องการลบ";
    exit;
}

$stmt = $conn->prepare("DELETE FROM form WHERE Form_id = ?");
$stmt->bind_param("i", $form_id);

if ($stmt->execute()) {
    header("Location: weekly.php?week=$week&year=$year&deleted=1");
    exit;
} else {
    echo "เกิดข้อผิดพลาดในการลบข้อมูล: " . $stmt->error;
}
