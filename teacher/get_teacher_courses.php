<?php
session_start();
include("../connect.php");

if (!(isset($_SESSION["state_login"]) && $_SESSION["type"] == 1)) {
    header("location:../login.php");
    exit();
}

$teacher_id = $_SESSION["ID"] ?? 0;
$class_id = intval($_POST['class_id'] ?? 0);

if ($teacher_id > 0 && $class_id > 0) {
    try {
        $stmt = $connect->prepare("SELECT Co_ID, Co_name, Co_type FROM courses WHERE Co_teacherID = ? AND Co_classID = ?");
        $stmt->execute([$teacher_id, $class_id]);
        $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($courses);
    } catch (PDOException $e) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([]);
    }
}
?>