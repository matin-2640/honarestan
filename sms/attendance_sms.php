<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include("../connect.php");

$idsString = isset($_GET["ids"]) ? trim($_GET["ids"]) : '';

if (!empty($idsString)) {
    // تبدیل رشته آی‌دی‌ها به آرایه
    $absentIds = explode(',', $idsString);

    $url = 'https://console.melipayamak.com/api/send/shared/08bebad81c6a4c1bab324b7f167cd87f';

    $stmt = $connect->prepare("SELECT Stu_fullName, Stu_fatherName, Stu_fatherPhone FROM Students WHERE Stu_ID = ?");

    foreach ($absentIds as $stuId) {
        $stuId = intval($stuId);
        if ($stuId <= 0)
            continue;

        $stmt->execute([$stuId]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($student && !empty($student['Stu_fatherPhone'])) {
            $name = $student['Stu_fullName'];
            $father = $student['Stu_fatherName'];
            $phone = $student['Stu_fatherPhone'];

            $data = array(
                'bodyId' => 507121,
                'to' => $phone,
                'args' => [
                    "
جناب $father عزیز $name امروز در کلاس درس حضور نیافته است
مدیریت هنرستان راه دانش
"
                ]
            );

            $data_string = json_encode($data);

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data_string)
            ));

            curl_exec($ch);
            curl_close($ch);

            // بروزرسانی سشن
            if (!isset($_SESSION["sms_success_students"]) || !is_array($_SESSION["sms_success_students"])) {
                $_SESSION["sms_success_students"] = array();
            }
            $_SESSION["sms_success_students"][$stuId] = time();
        }
    }
}

// نمایش آلرت ثبت موفق و بازگشت به مدیریت حضور و غیاب
echo "<script>
        alert('حضور و غیاب با موفقیت ثبت و پیامک‌های غیبت ارسال شد.');
        window.location.href = '../admin_attendance.php';
      </script>";
exit();
?>