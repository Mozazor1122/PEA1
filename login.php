<!-- <?php
session_start();
// ถ้า login แล้วให้ไปหน้า main.html เลย
if (isset($_SESSION['admin_id'])) {
    header('Location: main.html');
    exit;
}

$error_msg = '';
if (isset($_GET['error'])) {
    $error_msg = htmlspecialchars($_GET['error']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Login || PEA</title>
<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
<link rel="stylesheet" href="assets/css/style.css" />
</head>
<body>
    <div class="wrapper">
        <div class="login_box">
            <div class="login-header"><span>Login</span></div>

            <?php if ($error_msg): ?>
                <p style="color: red; text-align: center;"><?php echo $error_msg; ?></p>
            <?php endif; ?>

            <form action="login.php" method="POST" id="loginForm">
                <div class="input_box">
                    <input type="text" name="username" class="input-field" required autocomplete="username" />
                    <label class="label">Username</label>
                    <i class="bx bx-user icon"></i>
                </div>
                <div class="input_box">
                    <input type="password" name="password" class="input-field" required autocomplete="current-password" />
                    <label class="label">Password</label>
                    <i class="bx bx-lock icon"></i>
                </div>
                <div class="input_box">
                    <input type="submit" class="input-submit" value="Login" />
                </div>
            </form>
            <div class="register">
                <span>Don't have an account? <a href="register.html">Register</a></span>
            </div>
        </div>
    </div>
</body>
</html> -->




<?php
session_start();
include 'db.php'; // ไฟล์เชื่อมฐานข้อมูล

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = (int)$_POST['password']; // password เป็น int ตามโครงสร้างฐานข้อมูล

    $sql = "SELECT * FROM admin WHERE Admin_user = '$username' AND Admin_pass = $password";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) == 1) {
        $admin = mysqli_fetch_assoc($result);
        $_SESSION['admin_id'] = $admin['Admin_id'];
        $_SESSION['admin_name'] = $admin['Admin_name'];

        header('Location: main.html');
        exit;
    } else {
        header('Location: index.php?error=ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง');
        exit;
    }
} else {
    header('Location: index.php');
    exit;
}
?>
