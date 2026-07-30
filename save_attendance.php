<?php
include("connect.php");

// تابع ارسال پیامک به اولیا از طریق ملی‌پیامک
function sendAbsentSMS($mobile, $studentName, $date, $courseName) {
    if (empty($mobile)) {
        return;
    }

    $username = "نام_کاربری_ملی_پیامک";
    $password = "کلمه_عبور_ملی_پیامک";
    $from     = "5000xxxx";

    $text = "ولی محترم؛ دانش‌آموز {$studentName} در تاریخ {$date} در درس {$courseName} غایب بوده است.";
    $url  = "https://rest.payamak-panel.com/api/SendSMS/SendSMS";
    
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

// بررسی ارسال شدن فرم
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $course_id      = isset($_POST['course_id'])  ? intval($_POST['course_id'])  : 0;
    $a_date         = isset($_POST['a_date'])     ? trim($_POST['a_date'])     : '';
    $attendanceData = isset($_POST['attendance']) ? $_POST['attendance']        : [];

    if ($course_id > 0 && !empty($a_date) && !empty($attendanceData)) {
        
        try {
            $connect->beginTransaction();

            // ۱. دریافت نام درس
            $stmtCourse = $connect->prepare("SELECT Co_name FROM courses WHERE Co_ID = :coid");
            $stmtCourse->execute([':coid' => $course_id]);
            $courseRow  = $stmtCourse->fetch(PDO::FETCH_ASSOC);
            $courseName = isset($courseRow['Co_name']) ? $courseRow['Co_name'] : 'درس';

            // ۲. حذف ثبت‌های قبلی این جلسه (برای جلوگیری از ثبت تکراری)
            $stmtDel = $connect->prepare("DELETE FROM attendance WHERE A_courseID = :coid AND A_date = :adate");
            $stmtDel->execute([':coid' => $course_id, ':adate' => $a_date]);

            // ۳. آماده‌سازی کوئری‌های درج و دریافت تلفن دانش‌آموز
            $stmtInsert  = $connect->prepare("INSERT INTO attendance (A_studentID, A_date, A_courseID, A_state) VALUES (:sid, :adate, :coid, :astate)");
            $stmtStudent = $connect->prepare("SELECT Stu_fullName, Stu_phone FROM Students WHERE Stu_ID = :sid");

            // ۴. ذخیره تک‌تک وضعیت‌ها
            foreach ($attendanceData as $studentId => $stateValue) {
                
                $studentId  = intval($studentId);
                $stateValue = intval($stateValue); // ۱ = حاضر ، ۰ = غایب

                // ثبت در دیتابیس
                $stmtInsert->execute([
                    ':sid'    => $studentId,
                    ':adate'  => $a_date,
                    ':coid'   => $course_id,
                    ':astate' => $stateValue
                ]);

                // اگر غایب بود (مقدار ۰)، پیامک ارسال شود
                if ($stateValue === 0) {
                    $stmtStudent->execute([':sid' => $studentId]);
                    $studentInfo = $stmtStudent->fetch(PDO::FETCH_ASSOC);

                    if ($studentInfo) {
                        $phone = $studentInfo['Stu_phone'];
                        $name  = $studentInfo['Stu_fullName'];
                        sendAbsentSMS($phone, $name, $a_date, $courseName);
                    }
                }
            }

            $connect->commit();

            echo "<script>
                    alert('حضور و غیاب با موفقیت ثبت شد.');
                    window.location.href = 'admin_attendance.php';
                  </script>";

        } catch (Exception $e) {
            $connect->rollBack();
            echo "خطا در ثبت اطلاعات: " . $e->getMessage();
        }
    }
}
?>