<?php
session_start();
if (!(isset($_SESSION["state_login"]) && $_SESSION["type"] == 1)) {
    header("location:../login.php");
    exit();
}

include("../connect.php");

if (!isset($_POST["T_password"], $_POST["T_newPassword"])) {
    $_SESSION["send_error"] = true;
    header("location:change_pass.php");
    exit();
}

$T_password = trim($_POST["T_password"]);
$T_newPassword = trim($_POST["T_newPassword"]);

$id = $_SESSION["ID"];   // شناسه مدیر بعد از ورود

// بررسی رمز فعلی
$sql = "SELECT * FROM teachers
        WHERE T_ID = :id AND T_password = :password
        LIMIT 1";

$stmt = $connect->prepare($sql);
$stmt->bindParam(":id", $id);
$stmt->bindParam(":password", $T_password);
$stmt->execute();

if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
    $_SESSION["error_dup"] = true;
    header("location:change_pass.php");
    exit();
}

// تغییر رمز
$sql = "UPDATE teachers
        SET T_password = :newPassword
        WHERE T_ID = :id";

$stmt = $connect->prepare($sql);
$stmt->bindParam(":newPassword", $T_newPassword);
$stmt->bindParam(":id", $id);
$stmt->execute();

$_SESSION["change"] = true;

header("location:change_pass.php");
exit();
?>