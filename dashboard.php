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
$agency_data = [];
$result = $conn->query("SELECT Agency_name, COUNT(*) AS total FROM request_form GROUP BY Agency_name");
while($row=$result->fetch_assoc()) $agency_data[]=$row;

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
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link rel="stylesheet" href="assets/css/dash.css" />
<style>
body{font-family:sans-serif;background:#2c2c3c;color:white;margin:20px;}
.filter-container{margin-bottom:20px;}
.summary-container{display:flex;gap:20px;margin-bottom:30px;}
.card{background:#3a3a50;padding:20px;border-radius:10px;flex:1;text-align:center;}
.count{font-size:2.5em;margin:10px 0;}
.charts-container{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:20px;}
select, button{padding:5px 10px;border-radius:5px;border:none;}
button{background:#03a9f4;color:white;cursor:pointer;}
</style>
</head>
<body>

<h1>📊 Dashboard ระบบจัดการฟอร์ม</h1>

<!-- ตัวกรอง -->
<!-- ตัวกรอง -->
<div class="filter-container">
<label>เดือน:</label>
<select id="filterMonth">
    <option value="0">ทั้งหมด</option>
    <?php
    $thai_months = ["มกราคม","กุมภาพันธ์","มีนาคม.","เมษายน","พฤษภาคม","มิถุนายน","กรกฎาคม","สิงหาคม","กันยายน","ตุลาคม","พฤศจิกายน","ธันวาคม"];
    for($m=1;$m<=12;$m++):
    ?>
        <option value="<?php echo $m; ?>"> <?php echo $thai_months[$m-1]; ?></option>
    <?php endfor; ?>
</select>

<label style="margin-left:20px;">ปี:</label>
<select id="filterYear">
    <option value="0">ทั้งหมด</option>
    <?php
    $currentYear = (int)date('Y');
    for($y=$currentYear-5;$y<=$currentYear;$y++):
        $thaiYear = $y + 543; // แปลงเป็น พ.ศ.
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
options:{responsive:true,plugins:{legend:{labels:{color:'white'}},tooltip:{enabled:true}},cutout:'60%'},
plugins:[{id:'centerText',afterDraw(chart){const {ctx,width,height}=chart;ctx.save();const total=chart.data.datasets[0].data.reduce((a,b)=>a+b,0);ctx.fillStyle='#fff';ctx.font=`bold ${height/6}px sans-serif`;ctx.textAlign='center';ctx.textBaseline='middle';ctx.fillText(total,width/2,height/2);ctx.restore();}}]
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



// Chart อื่น (Agency / Device) คงค่าเริ่มต้น
const agencyChart = new Chart(document.getElementById('agencyChart'), {
type:'bar',
data:{labels: <?php echo json_encode(array_column($agency_data,'Agency_name')); ?>,
datasets:[{label:'จำนวน Request',data: <?php echo json_encode(array_column($agency_data,'total')); ?>,backgroundColor:'#03a9f4'}]},
options:{plugins:{legend:{labels:{color:'white'}}},scales:{x:{ticks:{color:'white'}},y:{ticks:{color:'white'}}}}
});

const deviceChart = new Chart(document.getElementById('deviceChart'),{
type:'bar',
data:{labels: <?php echo json_encode(array_column($device_data,'Device_name')); ?>,
datasets:[{label:'จำนวนครั้งที่ขอ',data: <?php echo json_encode(array_column($device_data,'total')); ?>,backgroundColor:'#9C27B0'}]},
options:{plugins:{legend:{labels:{color:'white'}}},scales:{x:{ticks:{color:'white'}},y:{ticks:{color:'white'}}}}
});

// Radar Chart (คงเดิม)
const radarChart = new Chart(document.getElementById('radarChart'),{
type:'radar',
data:{labels:['การจัดสรร','การร้องขอ','การตอบกลับ','ความสะดวก','ความถูกต้อง'],
datasets:[{label:'Rating',data:[3.5,4.0,3.8,3.0,4.2],fill:true,backgroundColor:'rgba(255,215,0,0.2)',borderColor:'#ffd700',pointBackgroundColor:'#ffd700'}]},
options:{scales:{r:{pointLabels:{color:'white'},ticks:{color:'white'}}},plugins:{legend:{labels:{color:'white'}}}}
});

// Filter Event
document.getElementById('filterBtn').addEventListener('click',()=>{
const month=document.getElementById('filterMonth').value;
const year=document.getElementById('filterYear').value;
fetch('get_data.php',{
    method:'POST',
    headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body:`month=${month}&year=${year}`
}).then(res=>res.json()).then(data=>{
    // Update Counters
    document.querySelector('.summary-container .card:nth-child(1) .count').setAttribute('data-target',data.total_all_forms);
    document.querySelector('.summary-container .card:nth-child(2) .count').setAttribute('data-target',data.total_not_allocated);
    document.querySelectorAll('.count').forEach(animateCounter);

    // Update Charts
    statusChart.data.datasets[0].data = data.status_data.map(d=>d.total); statusChart.update();
    monthlyChart.data.datasets[0].data = data.monthly_data.map(d=>d.total); monthlyChart.update();

    agencyChart.data.labels = data.agency_data.map(d=>d.Agency_name);
    agencyChart.data.datasets[0].data = data.agency_data.map(d=>d.total);
    agencyChart.update();

    deviceChart.data.labels = data.device_data.map(d=>d.Device_name);
    deviceChart.data.datasets[0].data = data.device_data.map(d=>d.total);
    deviceChart.update();
});
});
</script>
</body>
</html>
