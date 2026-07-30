<?php
include("connect.php");

// تابع ارسال پیامک ملی‌پنل به غایبین
function sendAbsentSMS($mobile, $studentName, $date, $courseName) {
    if (empty($mobile)) return;

    $username = "نام_کاربری_ملی_پیامک";
    $password = "کلمه_عبور_ملی_پیامک";
    $from     = "5000xxxx";

    $text = "ولی محترم؛ دانش‌آموز {$studentName} در تاریخ {$date} در درس {$courseName} غایب بوده است.";
    $url = "https://rest.payamak-panel.com/api/SendSMS/SendSMS";
    
    $data = array(
        'username' => $username,
        'password' => $password,
        'to'       => $mobile,
        'from'     => $from,
        'text'     => $text,
        'isflash'  => false
    );

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    $response = curl_exec($ch);
    curl_close($ch);

    return $response;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = intval($_POST['course_id'] ?? 0);
    $a_date = trim($_POST['a_date'] ?? '');
    $attendanceData = $_POST['attendance'] ?? [];

    if ($course_id > 0 && !empty($a_date) && !empty($attendanceData)) {
        try {
            $connect->beginTransaction();

            // دریافت نام درس
            $stmtCourse = $connect->prepare("SELECT Co_name FROM courses WHERE Co_ID = :coid");
            $stmtCourse->execute([':coid' => $course_id]);
            $courseRow = $stmtCourse->fetch(PDO::FETCH_ASSOC);
            $courseName = $courseRow['Co_name'] ?? 'درس';

            // حذف رکوردهای قبلی این تاریخ و درس
            $stmtDel = $connect->prepare("DELETE FROM attendance WHERE A_courseID = :coid AND A_date = :adate");
            $stmtDel->execute([':coid' => $course_id, ':adate' => $a_date]);

            // آماده‌سازی کوئری‌های درج و دریافت تلفن
            $stmtIns = $connect->prepare("INSERT INTO attendance (A_studentID, A_date, A_courseID, A_state) VALUES (:sid, :adate, :coid, :astate)");
            $stmtStu = $connect->prepare("SELECT Stu_fullName, Stu_phone FROM Students WHERE Stu_ID = :sid");

            foreach ($attendanceData as $studentId => $stateValue) {
                // تبدیل مقدار فرم به عدد صحیح (حاضر = 1 ، غایب = 0)
                $st = intval($stateValue); 
                $sID = intval($studentId);

                // ذخیره توی دیتابیس (A_state)
                $stmtIns->execute([
                    ':sid' => $sID,
                    ':adate' => $a_date,
                    ':coid' => $course_id,
                    ':astate' => $st
                ]);

                // اگر مقدار غایب (0) ثبت شد، پیامک ارسال می‌شود
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