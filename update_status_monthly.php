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

// รับพารามิเตอร์เดือนและปี เพื่อกลับไปหน้า monthly
$month = $_GET['month'] ?? date('m');
$year = $_GET['year'] ?? date('Y');

header("Location: monthly.php?month=$month&year=$year&success=1");
exit;
