<?php
session_start();
include("../connect.php");

if (!(isset($_SESSION["state_login"]) && $_SESSION["type"] == 1)) {
    header("location:../login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $teacher_id = $_SESSION["ID"] ?? 0;
    $course_id = intval($_POST['G_courseID'] ?? 0);
    $date = $_POST['date'] ?? '';

    $stmt_course = $connect->prepare("SELECT Co_type FROM courses WHERE Co_ID = ?");
    $stmt_course->execute([$course_id]);
    $course_info = $stmt_course->fetch(PDO::FETCH_ASSOC);

    if ($course_info && $course_info['Co_type'] != "0") {
        $at_type = 1;
    } else {
        $at_type = intval($_POST['A_type'] ?? 1);
        if ($at_type != 2) {
            $at_type = 1;
        }
    }

    $attendance = $_POST['attendance'] ?? [];

    if ($course_id > 0 && !empty($date) && $teacher_id > 0) {

        try {
            $connect->beginTransaction();
         $stmt_delete = $connect->prepare("
                DELETE FROM teacher_attendance 
                WHERE AT_courseID = ? 
                AND AT_date = ?
                AND AT_type = ?
            ");

            $stmt_delete->execute([
                $course_id,
                $date,
                $at_type
            ]);
          $stmt_insert = $connect->prepare("
                INSERT INTO teacher_attendance
                (
                    AT_studentID,
                    AT_courseID,
                    AT_teacherID,
                    AT_date,
                    AT_state,
                    AT_type
                )
                VALUES
                (
                    ?,
                    ?,
                    ?,
                    ?,
                    0,
                    ?
                )
            ");

            foreach ($attendance as $student_id => $state) {

                $student_id = intval($student_id);
                $state = intval($state);

                if ($state == 0) {
                    $stmt_insert->execute([
                        $student_id,
                        $course_id,
                        $teacher_id,
                        $date,
                        $at_type
                    ]);
                }
            }

            $connect->commit();
            $_SESSION['attendance_success'] = true;

        } catch (PDOException $e) {
            $connect->rollBack();
            $_SESSION['attendance_error'] = true;
        }

    }

}

header("location: attendance.php");
exit();
?>