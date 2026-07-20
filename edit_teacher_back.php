<?php
session_start();
include("connect.php");

$id = $_POST["T_ID"];
$T_fullName = $_POST['T_fullName'];
$T_nationalCode = $_POST['T_nationalCode'];
$T_phone = $_POST['T_phone'];

// check values : {
if (!isset($_POST["T_phone"], $_POST["T_nationalCode"], $_POST["T_phone"])) {
    $_SESSION["send_error"] = true;
    header("Location: edit_teacher.php?id=" . $id);
    exit();
}


if (!preg_match('/^\d{10}$/', $T_nationalCode)) {
    $_SESSION["error_nationalcode"] = true;
    header("Location: edit_teacher.php?id=" . $id);
    exit();
}

if (!preg_match('/^09\d{9}$/', $T_phone)) {
    $_SESSION["error_phone"] = true;
    header("Location: edit_teacher.php?id=" . $id);
    exit();
}

$sql = "UPDATE teachers SET
T_fullName = :T_fullName,
T_nationalCode = :T_nationalCode,
T_phone = :T_phone
WHERE T_ID = :T_ID";
$stmt = $connect->prepare($sql);

$stmt->bindParam(":T_ID", $id);
$stmt->bindParam(":T_fullName", $T_fullName);
$stmt->bindParam(":T_nationalCode", $T_nationalCode);
$stmt->bindParam(":T_phone", $T_phone);

$stmt->execute();

$_SESSION["edit_teacher"] = true;
header("Location: edit_teacher.php?id=" . $id);
exit();
?>