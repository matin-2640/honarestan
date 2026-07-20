<?php
session_start();

if (!(isset($_SESSION["state_login"]) && $_SESSION["type"] <= 2)) {
    header("location:login.php");
    exit();
}

include("connect.php");

if (!isset($_GET["id"])) {
    $_SESSION["error"] = true;
header("location:teachers_list.php");
    exit();
}

$id = $_GET["id"];

$sql = "DELETE FROM teachers WHERE T_ID = :id";
$stmt = $connect->prepare($sql);
$stmt->bindParam(":id", $id);
$stmt->execute();

$_SESSION["success"] = true;
header("location:teachers_list.php");
exit();
?>