<?php
require_once 'db.php';

// จำนวนฟอร์มจัดสรร/ยังไม่ได้จัดสรร
$total_allocated = (int)$conn->query("SELECT COUNT(*) FROM form")->fetch_row()[0];
$total_not_allocated = (int)$conn->query("SELECT COUNT(*) FROM request_form")->fetch_row()[0];
$total_all_forms = $total_allocated + $total_not_allocated;

// อุปกรณ์
$total_devices = (int)$conn->query("SELECT COUNT(*) FROM devices")->fetch_row()[0];

// Status Doughnut
$status_data = [
    ['status_name'=>'จัดสรรแล้ว','total'=>$total_allocated],
    ['status_name'=>'ไม่ได้จัดสรร','total'=>$total_not_allocated]
];

// Request per Agency
$agencies = [];

// ฟอร์มจัดสรร
$result = $conn->query("SELECT Form_agencyname AS Agency_name, COUNT(*) AS total_allocated FROM form GROUP BY Form_agencyname");
while($row = $result->fetch_assoc()){
    $agencies[$row['Agency_name']]['allocated'] = $row['total_allocated'];
}

// ฟอร์มยังไม่ได้จัดสรร
$result = $conn->query("SELECT Agency_name AS Agency_name, COUNT(*) AS total_not_allocated FROM request_form GROUP BY Agency_name");
while($row = $result->fetch_assoc()){
    $agencies[$row['Agency_name']]['not_allocated'] = $row['total_not_allocated'];
}

// เติมค่า 0 ถ้าไม่มี
foreach($agencies as $agency => $data){
    if(!isset($agencies[$agency]['allocated'])) $agencies[$agency]['allocated'] = 0;
    if(!isset($agencies[$agency]['not_allocated'])) $agencies[$agency]['not_allocated'] = 0;
}

// เตรียม array สำหรับ Chart.js
$agency_labels = array_keys($agencies);
$allocated_data = array_map(fn($a) => $a['allocated'], $agencies);
$not_allocated_data = array_map(fn($a) => $a['not_allocated'], $agencies);



// Top Devices
$device_data=[];
$result = $conn->query("SELECT Device_name, COUNT(*) AS total FROM request_form GROUP BY Device_name ORDER BY total DESC LIMIT 5");
while($row=$result->fetch_assoc()) $device_data[]=$row;

// ฟอร์มต่อเดือน
$months = array_fill(1,12,0);
$result = $conn->query("SELECT MONTH(Request_date) AS month, COUNT(*) AS total FROM request_form GROUP BY MONTH(Request_date)");
while($row=$result->fetch_assoc()) $months[(int)$row['month']]=$row['total'];
$monthly_data=[];
foreach($months as $m=>$total) $monthly_data[]=['month'=>$m,'total'=>$total];

$conn->close();
?>

<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>Dashboard</title>
<link rel="stylesheet" href="dashboard.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link rel="stylesheet" href="assets/css/dd.css" />
</head>
<body>

<h1>📊 Dashboard ระบบบันทึกข้อมูลการขอรับจัดสรรเครื่องคอมพิวเตอร์และอุปกรณ์ประกอบ </h1>

<!-- ฟิลเตอร์ -->
<div class="filter-container">
<label>เดือน:</label>
<select id="filterMonth">
    <option value="0">ทั้งหมด</option>
    <?php
    $thai_months = ["มกราคม","กุมภาพันธ์","มีนาคม","เมษายน","พฤษภาคม","มิถุนายน",
                    "กรกฎาคม","สิงหาคม","กันยายน","ตุลาคม","พฤศจิกายน","ธันวาคม"];
    for($m=1;$m<=12;$m++):
    ?>
        <option value="<?php echo $m; ?>"> <?php echo $thai_months[$m-1]; ?></option>
    <?php endfor; ?>
</select>

<label>ปี:</label>
<select id="filterYear">
    <option value="0">ทั้งหมด</option>
    <?php
    $currentYear = (int)date('Y');
    for($y=$currentYear-5;$y<=$currentYear;$y++):
        $thaiYear = $y + 543;
    ?>
        <option value="<?php echo $y; ?>"><?php echo $thaiYear; ?></option>
    <?php endfor; ?>
</select>

<button id="filterBtn">กรอง</button>
</div>

<!-- Summary Cards -->
<div class="summary-container">
  <div class="card"><h2 class="count" data-target="<?php echo $total_all_forms;?>">0</h2><p>📄 จำนวนฟอร์มทั้งหมด</p></div>
  <div class="card"><h2 class="count" data-target="<?php echo $total_not_allocated;?>">0</h2><p>📝 จำนวน Request</p></div>
  <div class="card"><h2 class="count" data-target="<?php echo $total_devices;?>">0</h2><p>💻 จำนวนอุปกรณ์</p></div>
</div>

<!-- Charts -->
<div class="charts-container">
  <div class="card"><canvas id="statusChart"></canvas></div>
  <div class="card"><canvas id="agencyChart"></canvas></div>
  <div class="card"><canvas id="deviceChart"></canvas></div>
  <div class="card"><canvas id="radarChart"></canvas></div>
  <div class="card"><canvas id="monthlyChart"></canvas></div>
</div>

<script>
// Counter
function animateCounter(el){
    const target=+el.getAttribute('data-target');
    const update=()=>{
        const count=+el.innerText;
        const inc=Math.ceil(target/100);
        if(count<target){ el.innerText=count+inc; setTimeout(update,20);}
        else el.innerText=target;
    };update();
}
document.querySelectorAll('.count').forEach(animateCounter);

// Doughnut Chart
const statusChart = new Chart(document.getElementById('statusChart'),{
type:'doughnut',
data:{labels: <?php echo json_encode(array_column($status_data,'status_name')); ?>,
datasets:[{data: <?php echo json_encode(array_column($status_data,'total')); ?>,backgroundColor:['#4CAF50','#FF9800']}]},
options:{responsive:true,plugins:{legend:{labels:{color:'white'}}},cutout:'60%'},
});

// Line Chart
const thaiMonths = ["ม.ค.","ก.พ.","มี.ค.","เม.ย.","พ.ค.","มิ.ย.","ก.ค.","ส.ค.","ก.ย.","ต.ค.","พ.ย.","ธ.ค."];
const monthlyChart = new Chart(document.getElementById('monthlyChart'),{
    type:'line',
    data:{
        labels: <?php echo json_encode(array_map(fn($m)=>$m['month'],$monthly_data)); ?>.map(m => thaiMonths[m-1]),
        datasets:[{
            label:'จำนวนฟอร์มต่อเดือน',
            data: <?php echo json_encode(array_column($monthly_data,'total')); ?>,
            backgroundColor:'rgba(3,169,244,0.2)',
            borderColor:'#03a9f4',
            borderWidth:2,
            fill:true,
            tension:0.3
        }]
    },
    options:{
        plugins:{legend:{labels:{color:'white'}}},
        scales:{x:{ticks:{color:'white'}},y:{ticks:{color:'white'}}}
    }
});

// Agency
// Agency chart แบบ grouped
const agencyChart = new Chart(document.getElementById('agencyChart'), {
    type: 'bar',       // ยังคงเป็น bar chart
    data: {
        labels: <?php echo json_encode($agency_labels); ?>,
        datasets: [
            {
                label: 'จัดสรรแล้ว',
                data: <?php echo json_encode($allocated_data); ?>,
                backgroundColor: '#4CAF50'
            },
            {
                label: 'ไม่ได้จัดสรร',
                data: <?php echo json_encode($not_allocated_data); ?>,
                backgroundColor: '#FF9800'
            }
        ]
    },
    options: {
       indexAxis: 'y',             // แท่งแนวนอน
        responsive: true,           // ปรับตาม container
        maintainAspectRatio: false, // ไม่ยึดอัตราส่วนเดิม
        plugins: {
            legend: { labels: { color: 'white' } }
        },
        scales: {
            x: { ticks: { color: 'white' } },
            y: { ticks: { color: 'white' } }
        }
    }
});
// Device
const deviceChart = new Chart(document.getElementById('deviceChart'),{
type:'bar',
data:{labels: <?php echo json_encode(array_column($device_data,'Device_name')); ?>,
datasets:[{label:'จำนวนครั้งที่ขอ',data: <?php echo json_encode(array_column($device_data,'total')); ?>,backgroundColor:'#9C27B0'}]},
options:{plugins:{legend:{labels:{color:'white'}}},scales:{x:{ticks:{color:'white'}},y:{ticks:{color:'white'}}}}
});

// Radar
const radarChart = new Chart(document.getElementById('radarChart'),{
type:'radar',
data:{labels:['การจัดสรร','การร้องขอ','การตอบกลับ','ความสะดวก','ความถูกต้อง'],
datasets:[{label:'Rating',data:[3.5,4.0,3.8,3.0,4.2],fill:true,backgroundColor:'rgba(255,215,0,0.2)',borderColor:'#ffd700',pointBackgroundColor:'#ffd700'}]},
options:{scales:{r:{pointLabels:{color:'white'},ticks:{color:'white'}}},plugins:{legend:{labels:{color:'white'}}}}
});
</script>
</body>
</html>
