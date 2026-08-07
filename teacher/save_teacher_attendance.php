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
    
    // متغیر A_state برای تعیین اول زنگ (0) یا آخر زنگ (1) استفاده می‌شود
    $session_state = intval($_POST['A_type'] ?? 0); 

    // اطلاعات حضور و غیاب همه دانش آموزان
    $attendance = $_POST['attendance'] ?? [];

    if ($course_id > 0 && !empty($date)) {

        try {

            $connect->beginTransaction();

            // پاک کردن رکوردهای قبلی بر اساس درس، تاریخ و وضعیت زمان (A_state)
            $stmt_delete = $connect->prepare("
                DELETE FROM attendance 
                WHERE A_courseID = ? 
                AND A_date = ?
                AND A_state = ?
            ");

            $stmt_delete->execute([
                $course_id,
                $date,
                $session_state
            ]);

            // درج غایبین جدید (A_state مقدار 0 یا 1 را برای اول/آخر زنگ ذخیره می‌کند)
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
                    1,
                    ?
                )
            ");

            foreach ($attendance as $student_id => $state) {

                $student_id = intval($student_id);
                $state = intval($state);

                // فقط اگر غایب بود ذخیره شود (حاضر = 1 ، غایب = 0)
                if ($state == 0) {
                    $stmt_insert->execute([
                        $student_id,
                        $date,
                        $course_id,
                        $session_state
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
