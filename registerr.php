<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Register || PEA</title>
<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
<link rel="stylesheet" href="assets/css/style.css" />
</head>
<body>
    <div class="wrapper">
        <div class="login_box">
            <div class="login-header" style="font-size: 25px; color: black;">Register</div>
            <form action="register.php" method="POST" id="registerForm">
                <div class="input_box">
                    <input type="text" id="name" name="name" class="input-field" required autocomplete="name" />
                    <label for="name" class="label">Full Name</label>
                    <i class="bx bx-id-card icon"></i>
                </div>
                <div class="input_box">
                    <input type="text" id="username" name="username" class="input-field" required autocomplete="username" />
                    <label for="username" class="label">Username</label>
                    <i class="bx bx-user icon"></i>
                </div>
                <div class="input_box">
                    <input type="password" id="password" name="password" class="input-field" required autocomplete="new-password" />
                    <label for="password" class="label">Password</label>
                    <i class="bx bx-lock icon"></i>
                </div>
                <div class="input_box">
                    <input type="password" id="password_confirm" name="password_confirm" class="input-field" required autocomplete="new-password" />
                    <label for="password_confirm" class="label">Confirm Password</label>
                    <i class="bx bx-lock icon"></i>
                </div>
                <div class="input_box">
                    <input type="submit" class="input-submit" value="Register" />
                </div>
            </form>
            <div class="register">
                <span>Already have an account? <a href="index.html">Login</a></span>
            </div>
            <div class="message">
                <?php
                if (isset($_GET['error'])) {
                    echo '<p style="color:red; margin-top:10px;">' . htmlspecialchars($_GET['error']) . '</p>';
                }
                if (isset($_GET['success'])) {
                    echo '<p style="color:green; margin-top:10px;">Registration successful. Please login.</p>';
                }
                ?>
            </div>
        </div>
    </div>
</body>
</html>
