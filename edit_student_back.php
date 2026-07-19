<?php
session_start();
include("connect.php");

$id = $_POST["Stu_ID"];

$Stu_fullName = $_POST['Stu_fullName'];
$Stu_nationalCode = $_POST['Stu_nationalCode'];
$Stu_classID = $_POST['Stu_classID'];
$Stu_phone = $_POST['Stu_phone'];
$Stu_fatherName = $_POST['Stu_fatherName'];
$Stu_fatherPhone = $_POST['Stu_fatherPhone'];

// check values : {
if (!isset($_POST["Stu_fullName"], $_POST["Stu_nationalCode"], $_POST["Stu_classID"], $_POST["Stu_phone"])) {
    $_SESSION["send_error"] = true;
    exit();
}

if (!preg_match('/^\d{10}$/', $Stu_nationalCode)) {
    $_SESSION["error_nationalcode"] = true;
header("Location: edit_student.php?id=" . $id);
    exit();
}

if (!preg_match('/^09\d{9}$/', $Stu_phone)) {
    $_SESSION["error_phone"] = true;
header("Location: edit_student.php?id=" . $id);
    exit();
}

$sql = "UPDATE Students SET
Stu_fullName = :Stu_fullName,
Stu_nationalCode = :Stu_nationalCode ,
Stu_classID = :Stu_classID,
Stu_phone = :Stu_phone,
Stu_fatherName = :Stu_fatherName,
Stu_fatherPhone = :Stu_fatherPhone
WHERE Stu_ID = :Stu_ID";

$stmt = $connect->prepare($sql);

$stmt->bindParam(":Stu_fullName", $Stu_fullName);
$stmt->bindParam(":Stu_nationalCode", $Stu_nationalCode);
$stmt->bindParam(":Stu_classID", $Stu_classID);
$stmt->bindParam(":Stu_phone", $Stu_phone);
$stmt->bindParam(":Stu_fatherName", $Stu_fatherName);
$stmt->bindParam(":Stu_fatherPhone", $Stu_fatherPhone);
$stmt->bindParam(":Stu_ID", $id);

$stmt->execute();

$_SESSION["edit_student"] = true;
header("Location: edit_student.php?id=" . $id);
exit();
?>