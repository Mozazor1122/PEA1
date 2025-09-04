<?php
require_once 'db.php';

$month = isset($_POST['month']) ? (int)$_POST['month'] : 0;
$year  = isset($_POST['year']) ? (int)$_POST['year'] : 0;

// --- เงื่อนไข WHERE ---
$where = [];
if($month>0) $where[] = "MONTH(Request_date) = $month";
if($year>0)  $where[] = "YEAR(Request_date) = $year";
$where_sql = $where ? "WHERE ".implode(' AND ',$where) : "";

// ฟอร์มจัดสรร (ไม่ขึ้นกับเดือน/ปี)
$total_allocated = (int)$conn->query("SELECT COUNT(*) FROM form")->fetch_row()[0];

// ฟอร์มยังไม่ได้จัดสรร (filtered)
$total_not_allocated = (int)$conn->query("SELECT COUNT(*) FROM request_form $where_sql")->fetch_row()[0];
$total_all_forms = $total_allocated + $total_not_allocated;

// Status Doughnut
$status_data = [
    ['status_name'=>'จัดสรรแล้ว','total'=>$total_allocated],
    ['status_name'=>'ไม่ได้จัดสรร','total'=>$total_not_allocated]
];

// Request per Agency (filtered)
$agency_data = [];
$sql_agency = "SELECT Agency_name, COUNT(*) AS total FROM request_form $where_sql GROUP BY Agency_name";
$result = $conn->query($sql_agency);
while($row = $result->fetch_assoc()) $agency_data[] = $row;

// Top Devices (filtered)
$device_data = [];
$sql_device = "SELECT Device_name, COUNT(*) AS total FROM request_form $where_sql GROUP BY Device_name ORDER BY total DESC LIMIT 5";
$result = $conn->query($sql_device);
while($row = $result->fetch_assoc()) $device_data[] = $row;

// ฟอร์มต่อเดือน (filtered)
$months = array_fill(1,12,0);
$sql_monthly = "SELECT MONTH(Request_date) AS month, COUNT(*) AS total FROM request_form $where_sql GROUP BY MONTH(Request_date)";
$result = $conn->query($sql_monthly);
while($row = $result->fetch_assoc()) $months[(int)$row['month']] = (int)$row['total'];
$monthly_data = [];
foreach($months as $m=>$total) $monthly_data[]=['month'=>$m,'total'=>$total];

$conn->close();

echo json_encode([
    'total_all_forms'=>$total_all_forms,
    'total_not_allocated'=>$total_not_allocated,
    'status_data'=>$status_data,
    'agency_data'=>$agency_data,
    'device_data'=>$device_data,
    'monthly_data'=>$monthly_data
]);
?>
