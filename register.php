<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if ($username === '' || $password === '' || $password_confirm === '' || $name === '') {
        header('Location: registerr.php?error=Please fill in all fields');
        exit;
    }

    if ($password !== $password_confirm) {
        header('Location: registerr.php?error=Passwords do not match');
        exit;
    }

    // ตรวจสอบว่ามี username นี้แล้วหรือยัง
    $stmt = $conn->prepare("SELECT Admin_id FROM admin WHERE Admin_user = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        header('Location: registerr.php?error=Username already ');
        exit;
    }

    // สร้าง ID แบบสุ่ม
    $admin_id = rand(10000, 99999);

    // บันทึกข้อมูล (ยังไม่ใช้ password_hash เพราะรหัสผ่านในฐานข้อมูลเป็น int)
    $stmt = $conn->prepare("INSERT INTO admin (Admin_id, Admin_name, Admin_user, Admin_pass) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("issi", $admin_id, $name, $username, $password);

    if ($stmt->execute()) {
        header('Location: registerr.php?success=1');
        exit;
    } else {
        header('Location: registerr.php?error=Registration failed, try again');
        exit;
    }
} else {
    header('Location: registerr.php');
    exit;
}
?>
