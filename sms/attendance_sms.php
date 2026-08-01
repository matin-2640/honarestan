<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include("../connect.php");

$idsString = isset($_GET["ids"]) ? trim($_GET["ids"]) : '';

if (!empty($idsString)) {

    // تبدیل IDها به آرایه
    $absentIds = explode(',', $idsString);

    $url = 'https://console.melipayamak.com/api/send/shared/08bebad81c6a4c1bab324b7f167cd87f';

    $stmt = $connect->prepare("
        SELECT Stu_fullName, Stu_fatherName, Stu_fatherPhone 
        FROM Students 
        WHERE Stu_ID = ?
    ");

    foreach ($absentIds as $stuId) {

        $stuId = intval(trim($stuId));

        if ($stuId <= 0) {
            continue;
        }

        /*
         * کوکی کاملاً اختصاصی برای همین دانش‌آموز
         * مثال:
         * sms_sent_stu_25
         * sms_sent_stu_38
         */
        $cookieName = "sms_sent_stu_" . $stuId;

        /*
         * اگر برای همین دانش‌آموز در 10 ساعت گذشته
         * پیام ارسال شده باشد، از این دانش‌آموز عبور کن.
         *
         * توجه:
         * کوکی از مرورگر کاربر خوانده می‌شود.
         */
        if (isset($_COOKIE[$cookieName])) {
            continue;
        }

        // دریافت اطلاعات دانش‌آموز
        $stmt->execute([$stuId]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$student || empty($student['Stu_fatherPhone'])) {
            continue;
        }

        $name = $student['Stu_fullName'];
        $father = $student['Stu_fatherName'];
        $phone = $student['Stu_fatherPhone'];

        $data = [
            'bodyId' => 507121,
            'to' => $phone,
            'args' => [
                "$father گرامی ، $name امروز در کلاس درس حضور نیافته است
مدیریت هنرستان راه دانش"
            ]
        ];

        $dataString = json_encode($data, JSON_UNESCAPED_UNICODE);

        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $dataString,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($dataString)
            ]
        ]);

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);

        curl_close($ch);

        /*
         * فقط وقتی درخواست HTTP با موفقیت انجام شده،
         * محدودیت 10 ساعته را ایجاد کن.
         */
        if ($result !== false && $httpCode >= 200 && $httpCode < 300) {

            /*
             * کوکی اختصاصی همین دانش‌آموز
             * به مدت 10 ساعت
             */
            setcookie(
                $cookieName,
                "1",
                [
                    'expires' => time() + (10 * 60 * 60),
                    'path' => '/',
                    'secure' => isset($_SERVER['HTTPS']),
                    'httponly' => true,
                    'samesite' => 'Lax'
                ]
            );

            /*
             * چون setcookie در همین Request هنوز
             * $_COOKIE را تغییر نمی‌دهد، خودمان هم
             * مقدارش را در آرایه قرار می‌دهیم.
             */
            $_COOKIE[$cookieName] = "1";

            // ثبت ارسال موفق در Session
            if (
                !isset($_SESSION["sms_success_students"]) ||
                !is_array($_SESSION["sms_success_students"])
            ) {
                $_SESSION["sms_success_students"] = [];
            }

            $_SESSION["sms_success_students"][$stuId] = time();
        }
    }
}

// بازگشت به صفحه حضور و غیاب
header("Location: ../admin_attendance.php");
exit();
?>