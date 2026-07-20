<?php
session_start();
include("connect.php");

$id = $_POST["Co_ID"];
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

$sql = "UPDATE courses SET
Co_name = :Co_name,
Co_num = :Co_num,
Co_classID = :Co_classID,
Co_teacherID = :Co_teacherID,
Co_type = :Co_type
WHERE Co_ID = :Co_ID";

$stmt = $connect->prepare($sql);

$stmt->bindParam(":Co_ID", $id);
$stmt->bindParam(":Co_name", $Co_name);
$stmt->bindParam(":Co_num", $Co_num);
$stmt->bindParam(":Co_classID", $Co_classID);
$stmt->bindParam(":Co_teacherID", $Co_teacherID);
$stmt->bindParam(":Co_type", $Co_type);

$stmt->execute();

$_SESSION["edit_course"] = true;
header("Location: edit_course.php?id=" . $id);
exit();
?>