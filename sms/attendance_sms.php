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

        // نام کوکی اختصاصی ۱۰ ساعته برای هر دانش‌آموز
        $cookieName = "sms_sent_stu_" . $stuId;

        // اگر کوکی ارسال پیامک از قبل وجود داشته باشد، ارسال مجدد انجام نمی‌شود
        if (isset($_COOKIE[$cookieName])) {
            continue;
        }

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
$father گرامی ، $name امروز در کلاس درس حضور نیافته است
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

            $result = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            // در صورت ارسال یا درخواست موفق، کوکی ۱۰ ساعته ایجاد می‌شود
            if ($httpCode == 200 || $result !== false) {
                setcookie($cookieName, "1", time() + (10 * 3600), "/");
            }

            // بروزرسانی سشن
            if (!isset($_SESSION["sms_success_students"]) || !is_array($_SESSION["sms_success_students"])) {
                $_SESSION["sms_success_students"] = array();
            }
            $_SESSION["sms_success_students"][$stuId] = time();
        }
    }
}

// بازگشت مستقیم به مدیریت حضور و غیاب (بدون آلرت قدیمی)
header("Location: ../admin_attendance.php");
exit();
?>