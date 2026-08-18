<?php
session_start();

if (file_exists("connect.php")) {
    include("connect.php");
} else {
    echo json_encode(['status' => 'error', 'message' => 'خطای سیستمی: فایل اتصال یافت نشد.']);
    exit;
}

if (file_exists("sms/code.php")) {
    include("sms/code.php");
}

header('Content-Type: application/json; charset=utf-8');

$action = isset($_POST['action']) ? $_POST['action'] : '';

try {
    if ($action == 'check_user') {
        $nationalCode = isset($_POST['national_code']) ? trim($_POST['national_code']) : '';
        $userType = isset($_POST['user_type']) ? $_POST['user_type'] : '';

        if ($userType == 'admin') {
            $stmt = $connect->prepare("SELECT Ad_phone FROM admin WHERE Ad_nationalCode = :code");
        } else {
            $stmt = $connect->prepare("SELECT T_phone FROM Teachers WHERE T_nationalCode = :code");
        }

        $stmt->execute([':code' => $nationalCode]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $phone = ($userType == 'admin') ? $user['Ad_phone'] : $user['T_phone'];
            
            $maskedPhone = substr($phone, 0, 3) . '*****' . substr($phone, -3);

            echo json_encode([
                'status' => 'success',
                'masked_phone' => $maskedPhone
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'کاربری با این کد ملی یافت نشد.'
            ]);
        }
        exit;
    }

    if ($action == 'send_sms') {
        $nationalCode = isset($_POST['national_code']) ? trim($_POST['national_code']) : '';
        $userType = isset($_POST['user_type']) ? $_POST['user_type'] : '';

        if ($userType == 'admin') {
            $stmt = $connect->prepare("SELECT Ad_phone FROM admin WHERE Ad_nationalCode = :code");
        } else {
            $stmt = $connect->prepare("SELECT T_phone FROM Teachers WHERE T_nationalCode = :code");
        }
        $stmt->execute([':code' => $nationalCode]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $phone = ($userType == 'admin') ? $user['Ad_phone'] : $user['T_phone'];
            
            $randomCode = rand(100000, 999999);
            $_SESSION['reset_otp'] = $randomCode;

            if (function_exists('sendOtpSms')) {
                $smsSent = sendOtpSms($phone, $randomCode);
            } else {
                $smsSent = false; 
            }

            if ($smsSent) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'خطا در ارسال پیامک. لطفاً مجدداً تلاش کنید.']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'اطلاعات کاربر یافت نشد.']);
        }
        exit;
    }

    if ($action == 'verify_code') {
        $enteredCode = isset($_POST['code']) ? trim($_POST['code']) : '';

        if (isset($_SESSION['reset_otp']) && $_SESSION['reset_otp'] == $enteredCode) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'کد تایید وارد شده اشتباه است.']);
        }
        exit;
    }

    if ($action == 'change_password') {
        $newPassword = isset($_POST['new_password']) ? trim($_POST['new_password']) : '';
        $nationalCode = isset($_POST['national_code']) ? trim($_POST['national_code']) : '';
        $userType = isset($_POST['user_type']) ? $_POST['user_type'] : '';

        if ($userType == 'admin') {
            $stmt = $connect->prepare("UPDATE admin SET Ad_password = :pass WHERE Ad_nationalCode = :code");
        } else {
            $stmt = $connect->prepare("UPDATE Teachers SET T_Password = :pass WHERE T_nationalCode = :code");
        }

        $res = $stmt->execute([
            ':pass' => $newPassword,
            ':code' => $nationalCode
        ]);

        if ($res) {
            unset($_SESSION['reset_otp']);
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'خطا در به‌روزرسانی رمز عبور.']);
        }
        exit;
    }

} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'خطا در ارتباط با دیتابیس. لطفاً بعداً تلاش کنید.']);
    exit;
}
?>
