<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include("connect.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
    $a_date = isset($_POST['a_date']) ? trim($_POST['a_date']) : '';
    $attendanceData = isset($_POST['attendance']) ? $_POST['attendance'] : [];

    if ($course_id > 0 && !empty($a_date) && !empty($attendanceData)) {

        try {
            $connect->beginTransaction();

            // ۱. حذف ثبت‌های قبلی این جلسه
            $stmtDel = $connect->prepare("DELETE FROM attendance WHERE A_courseID = :coid AND A_date = :adate");
            $stmtDel->execute([':coid' => $course_id, ':adate' => $a_date]);

            // ۲. آماده‌سازی کوئری درج
            $stmtInsert = $connect->prepare("INSERT INTO attendance (A_studentID, A_date, A_courseID, A_state) VALUES (:sid, :adate, :coid, :astate)");

            $absentIds = []; // آرایه‌ای برای نگه‌داری آی‌دی غایبین

            // ۳. ذخیره وضعیت‌ها
            foreach ($attendanceData as $studentId => $stateValue) {

                $studentId = intval($studentId);
                $stateValue = intval($stateValue); // ۱ = حاضر ، ۰ = غایب

                // ثبت در دیتابیس
                $stmtInsert->execute([
                    ':sid' => $studentId,
                    ':adate' => $a_date,
                    ':coid' => $course_id,
                    ':astate' => $stateValue
                ]);

                // اگر غایب بود آی‌دی آن ذخیره می‌شود
                if ($stateValue === 0) {
                    $absentIds[] = $studentId;
                }
            }

            $connect->commit();

            // ساخت رشته‌ای از آی‌دی غایبین (مثلاً: ids=12,15,18)
            $idsParam = implode(',', $absentIds);

            // انتقال مستقیم کاربر به فایل ارسال پیامک
            header("Location: sms/Attendance_sms.php?ids=" . urlencode($idsParam));
            exit();

        } catch (Exception $e) {
            $connect->rollBack();
            echo "خطا در ثبت اطلاعات: " . $e->getMessage();
        }
    }
}
?>