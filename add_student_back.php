<?php
session_start();
include("connect.php");

$Stu_fullName = $_POST['Stu_fullName'];
$Stu_nationalCode = $_POST['Stu_nationalCode'];
$Stu_classID = $_POST['Stu_classID'];
$Stu_phone = $_POST['Stu_phone'];
$Stu_fatherName = $_POST['Stu_fatherName'];
$Stu_fatherPhone = $_POST['Stu_fatherPhone'];

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

header("location:add_student.php");
$_SESSION["add_student"] = true;
exit();
?>