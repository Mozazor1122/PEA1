<?php
require_once 'db.php';

if (isset($_GET['id'], $_GET['status'])) {
    $id = intval($_GET['id']);
    $status = intval($_GET['status']);

    $sql = "UPDATE form SET status_id = ? WHERE Form_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $status, $id);
    $stmt->execute();
    $stmt->close();
}

// รับพารามิเตอร์ปีและสัปดาห์ เพื่อกลับไปหน้า weekly
$year = $_GET['year'] ?? date('Y');
$week = $_GET['week'] ?? date('W');

header("Location: weekly.php?year=$year&week=$week&success=1");
exit;
