<?php
session_start();
include("connect.php");

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

$sql = "SELECT Stu_ID FROM Students WHERE Stu_nationalCode = :code LIMIT 1";
$stmt = $connect->prepare($sql);
$stmt->bindParam(":code", $Stu_nationalCode);
$stmt->execute();

if ($stmt->fetch()) {
    $_SESSION["error_dup"] = true;
    header("Location:add_student.php");
    exit();
}

if (!preg_match('/^\d{10}$/', $Stu_nationalCode)) {
    $_SESSION["error_nationalcode"] = true;
    header("Location:add_student.php");
    exit();
}

if (!preg_match('/^09\d{9}$/', $Stu_phone)) {
    $_SESSION["error_phone"] = true;
    header("Location:add_student.php");
    exit();
}

$sql = "INSERT INTO Students ( 
Stu_fullName , Stu_nationalCode , Stu_classID , Stu_phone , Stu_fatherName , Stu_fatherPhone )
VALUES
 ( :Stu_fullName , :Stu_nationalCode ,  :Stu_classID , :Stu_phone , :Stu_fatherName, :Stu_fatherPhone) ";

$stmt = $connect->prepare($sql);

$stmt->bindParam(":Stu_fullName", $Stu_fullName);
$stmt->bindParam(":Stu_nationalCode", $Stu_nationalCode);
$stmt->bindParam(":Stu_classID", $Stu_classID);
$stmt->bindParam(":Stu_phone", $Stu_phone);
$stmt->bindParam(":Stu_fatherName", $Stu_fatherName);
$stmt->bindParam(":Stu_fatherPhone", $Stu_fatherPhone);

$stmt->execute();

$_SESSION["add_student"] = true;
header("location:add_student.php");
exit();
?>