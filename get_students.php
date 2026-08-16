<?php
include("connect.php");

header('Content-Type: application/json; charset=utf-8');

if (isset($_GET['class_id'])) {
    $class_id = intval($_GET['class_id']);
    $students = [];

    $possible_columns = ['Stu_classID', 'C_ID', 'class_id', 'Class_ID', 'stu_class_id'];

    foreach ($possible_columns as $col) {
        try {
            $stmt = $connect->prepare("SELECT Stu_ID, Stu_fullName FROM Students WHERE {$col} = ? ORDER BY Stu_fullName ASC");
            $stmt->execute([$class_id]);
            $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

            break;
        } catch (PDOException $e) {
            continue;
        }
    }

    echo json_encode($students, JSON_UNESCAPED_UNICODE);
    exit;
}
?>