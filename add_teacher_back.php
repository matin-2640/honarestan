<?php
session_start();
include("connect.php");

$T_fullName = $_POST['T_fullName'];
$T_nationalCode = $_POST['T_nationalCode'];
$T_phone = $_POST['T_phone'];
$T_password = 123456;

// check values : {
if (!isset($_POST["T_phone"], $_POST["T_nationalCode"], $_POST["T_phone"])) {
    $_SESSION["send_error"] = true;
    header("Location:add_teacher.php");
    exit();
}

$sql = "SELECT T_ID FROM teachers WHERE T_nationalCode = :code LIMIT 1";
$stmt = $connect->prepare($sql);
$stmt->bindParam(":code", $T_nationalCode);
$stmt->execute();

if ($stmt->fetch()) {
    $_SESSION["error_dup"] = true;
    header("Location:add_teacher.php");
    exit();
}

if (!preg_match('/^\d{10}$/', $T_nationalCode)) {
    $_SESSION["error_nationalcode"] = true;
    header("Location:add_teacher.php");
    exit();
}

if (!preg_match('/^09\d{9}$/', $T_phone)) {
    $_SESSION["error_phone"] = true;
    header("Location:add_teacher.php");
    exit();
}

$sql = "INSERT INTO teachers ( 
T_fullName , T_nationalCode , T_phone , T_password  )
VALUES
 ( :T_fullName , :T_nationalCode ,  :T_phone ,:T_password ) ";

$stmt = $connect->prepare($sql);

$stmt->bindParam(":T_fullName", $T_fullName);
$stmt->bindParam(":T_nationalCode", $T_nationalCode);
$stmt->bindParam(":T_phone", $T_phone);
$stmt->bindParam(":T_password", $T_password);

$stmt->execute();

$_SESSION["add_teacher"] = true;
header("location:add_teacher.php");
exit();
?>