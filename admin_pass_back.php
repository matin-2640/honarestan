<?php
session_start();

if (!(isset($_SESSION["state_login"]) && $_SESSION["type"] == 2 || $_SESSION["type"] == 3) || $_SESSION["type"] == 4) {
    header("location:login.php");
    exit();
}

include("connect.php");

if (!isset($_POST["Ad_password"], $_POST["Ad_newPassword"])) {
    $_SESSION["send_error"] = true;
    header("location:admin_pass.php");
    exit();
}

$Ad_password = trim($_POST["Ad_password"]);
$Ad_newPassword = trim($_POST["Ad_newPassword"]);

$id = $_SESSION["ID"];   // شناسه مدیر بعد از ورود

// بررسی رمز فعلی
$sql = "SELECT * FROM admin
        WHERE Ad_ID = :id AND Ad_password = :password
        LIMIT 1";

$stmt = $connect->prepare($sql);
$stmt->bindParam(":id", $id);
$stmt->bindParam(":password", $Ad_password);
$stmt->execute();

if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
    $_SESSION["error_dup"] = true;
    header("location:admin_pass.php");
    exit();
}

// تغییر رمز
$sql = "UPDATE admin
        SET Ad_password = :newPassword
        WHERE Ad_ID = :id";

$stmt = $connect->prepare($sql);
$stmt->bindParam(":newPassword", $Ad_newPassword);
$stmt->bindParam(":id", $id);
$stmt->execute();

$_SESSION["change"] = true;

header("location:admin_pass.php");
exit();
?>