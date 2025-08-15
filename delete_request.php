<?php
require_once 'db.php';

$id = $_GET['id'] ?? '';
if ($id) {
    $conn->query("DELETE FROM request_form WHERE Request_id = $id");
}
header("Location: totaldata.php");
exit;
