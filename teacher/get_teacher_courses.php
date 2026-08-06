<?php
session_start();
include("../connect.php");

// بررسی وضعیت لاگین و دسترسی معلم
if (!(isset($_SESSION["state_login"]) && $_SESSION["type"] <= 2)) {
    exit("دسترسی غیرمجاز");
}

$teacher_id = $_SESSION["ID"] ?? 0;
$class_id = intval($_POST['class_id'] ?? 0);

if ($teacher_id > 0 && $class_id > 0) {
    try {
        // دریافت درس‌هایی که متعلق به این معلم و این کلاس هستند
        $stmt = $connect->prepare("SELECT Co_ID, Co_name, Co_type FROM courses WHERE Co_teacherID = ? AND Co_classID = ?");
        $stmt->execute([$teacher_id, $class_id]);
        $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // ارسال خروجی به صورت JSON
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($courses);
    } catch (PDOException $e) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([]);
    }
}
?>