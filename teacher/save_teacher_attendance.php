<?php
session_start();
include("../connect.php");

if (!(isset($_SESSION["state_login"]) && $_SESSION["type"] == 1)) {
    header("location:login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $course_id = intval($_POST['G_courseID'] ?? 0);
    $date = $_POST['date'] ?? '';
    $type = intval($_POST['A_type'] ?? 1);

    // اطلاعات حضور و غیاب همه دانش آموزان
    // مثال: attendance[13]=1 یا attendance[13]=0
    $attendance = $_POST['attendance'] ?? [];


    if ($course_id > 0 && !empty($date)) {

        try {

            $connect->beginTransaction();


            // فقط همین جلسه را پاک می‌کنیم
            // اول زنگ و آخر زنگ جدا هستند
            $stmt_delete = $connect->prepare("
                DELETE FROM attendance 
                WHERE A_courseID = ? 
                AND A_date = ?
                AND A_type = ?
            ");

            $stmt_delete->execute([
                $course_id,
                $date,
                $type
            ]);


            // فقط غایبین ذخیره می‌شوند
            $stmt_insert = $connect->prepare("
                INSERT INTO attendance
                (
                    A_studentID,
                    A_date,
                    A_courseID,
                    A_type,
                    A_state
                )
                VALUES
                (
                    ?,
                    ?,
                    ?,
                    ?,
                    0
                )
            ");



            foreach ($attendance as $student_id => $state) {


                $student_id = intval($student_id);
                $state = intval($state);


                // فقط اگر غایب بود ذخیره شود
                // حاضر = 1
                // غایب = 0
                if ($state == 0) {


                    $stmt_insert->execute([
                        $student_id,
                        $date,
                        $course_id,
                        $type
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