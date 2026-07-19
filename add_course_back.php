<?php
session_start();
include("connect.php");

$Co_name = $_POST['Co_name'];
$Co_num = intval($_POST['Co_num']);
$Co_classID = $_POST['Co_classID'];
$Co_teacherID = $_POST['Co_teacherID'];
$Co_type = $_POST['Co_type'];

// check values : {
if (!isset($_POST["Co_name"], $_POST["Co_num"], $_POST["Co_classID"], $_POST["Co_teacherID"])) {
    $_SESSION["send_error"] = true;
    exit();
}

$sql = "SELECT Co_ID FROM courses WHERE Co_name = :Co_name LIMIT 1";
$stmt = $connect->prepare($sql);
$stmt->bindParam(":Co_name", $Co_name);
$stmt->execute();

if ($stmt->fetch()) {
    $_SESSION["error_dup"] = true;
    header("Location:add_course.php");
    exit();
}

$sql = "INSERT INTO courses ( 
Co_name , Co_num , Co_classID , Co_teacherID , Co_type )
VALUES
 ( :Co_name , :Co_num  , :Co_classID , :Co_teacherID , :Co_type) ";

$stmt = $connect->prepare($sql);

$stmt->bindParam(":Co_name", $Co_name);
$stmt->bindParam(":Co_num", $Co_num);
$stmt->bindParam(":Co_classID", $Co_classID);
$stmt->bindParam(":Co_teacherID", $Co_teacherID);
$stmt->bindParam(":Co_type", $Co_type);

$stmt->execute();

$_SESSION["add_course"] = true;
header("location:add_course.php");
exit();
?>