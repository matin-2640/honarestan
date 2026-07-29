<?php
include("connect.php");

// تابع ارسال پیامک به دانش‌آموز غایب (آماده اتصال به پنل)
function sendAbsentSMS($mobile, $studentName, $date, $courseName) {
    if (empty($mobile)) return;

    $message = "ولی محترم؛ دانش‌آموز {$studentName} در تاریخ {$date} در درس {$courseName} غایب بوده است.";

    /*
    // محل قرارگیری کد پنل پیامک (کاوه‌نگار / ملی‌پیامک / ...)
    $apiKey = "YOUR_API_KEY";
    $url = "https://api.kavenegar.com/v1/{$apiKey}/sms/send.json";
    */
    
    // ثبت در لاگ جهت تست
    error_log("ارسال پیامک به {$mobile}: {$message}");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = intval($_POST['course_id'] ?? 0);
    $a_date = trim($_POST['a_date'] ?? '');
    $attendanceData = $_POST['attendance'] ?? [];

    if ($course_id > 0 && !empty($a_date) && !empty($attendanceData)) {
        try {
            $connect->beginTransaction();

            // ۱. دریافت نام درس بر اساس Co_name
            $stmtCourse = $connect->prepare("SELECT Co_name FROM courses WHERE Co_ID = :coid");
            $stmtCourse->execute([':coid' => $course_id]);
            $courseRow = $stmtCourse->fetch(PDO::FETCH_ASSOC);
            $courseName = $courseRow['Co_name'] ?? 'درس';

            // ۲. پاکسازی رکوردهای قبلی همین تاریخ و درس
            $stmtDel = $connect->prepare("DELETE FROM attendance WHERE A_courseID = :coid AND A_date = :adate");
            $stmtDel->execute([':coid' => $course_id, ':adate' => $a_date]);

            // ۳. درج وضعیت‌های جدید
            $stmtIns = $connect->prepare("INSERT INTO attendance (A_studentID, A_date, A_courseID, A_state) VALUES (:sid, :adate, :coid, :astate)");
            $stmtStu = $connect->prepare("SELECT Stu_fullName, Stu_phone FROM Students WHERE Stu_ID = :sid");

            foreach ($attendanceData as $studentId => $state) {
                $st = intval($state);
                $sID = intval($studentId);

                $stmtIns->execute([
                    ':sid' => $sID,
                    ':adate' => $a_date,
                    ':coid' => $course_id,
                    ':astate' => $st
                ]);

                // اگر غایب بود (0)، پیامک ارسال شود
                if ($st === 0) {
                    $stmtStu->execute([':sid' => $sID]);
                    $stuData = $stmtStu->fetch(PDO::FETCH_ASSOC);

                    if ($stuData) {
                        $phone = $stuData['Stu_phone'] ?? '';
                        $name = $stuData['Stu_fullName'] ?? '';
                        sendAbsentSMS($phone, $name, $a_date, $courseName);
                    }
                }
            }

            $connect->commit();
            echo "<script>alert('حضور و غیاب با موفقیت ثبت شد.'); window.location.href='admin_attendance.php';</script>";
        } catch (Exception $e) {
            $connect->rollBack();
            echo "خطا در ذخیره‌سازی: " . $e->getMessage();
        }
    }
}