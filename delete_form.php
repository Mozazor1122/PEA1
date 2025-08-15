<?php
require_once 'db.php';

$id = $_GET['id'] ?? '';
if ($id) {
    $conn->query("DELETE FROM form WHERE Form_id = $id");
}
header("Location: total_form.php");
exit;
