<?php
session_start();
include("../connect.php");

$T_ID = $_GET["id"];

$sql = "SELECT * FROM teachers WHERE T_ID = ?";
$stmt = $connect->prepare($sql);
$stmt->execute([$T_ID]);

$teacher = $stmt->fetch(PDO::FETCH_ASSOC);

$name = $teacher['T_fullName'];
$phone = $teacher['T_phone'];

$url = 'https://console.melipayamak.com/api/send/shared/08bebad81c6a4c1bab324b7f167cd87f';

$data = array(
    'bodyId' => 507121,
    'to' => $phone,
    'args' => [
        "جناب  $name عزیز لطفا نسبت به ثبت نمره هنرجویان اقدام فرمایید"
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

if (curl_errno($ch)) {
    echo "Curl Error: " . curl_error($ch);
} else {
    echo $result;
}

curl_close($ch);

// ۱. بررسی یا ساخت آرایه اصلی سشن بدون دستکاری مقادیر قبلی
if (!isset($_SESSION["sms_success_teachers"]) || !is_array($_SESSION["sms_success_teachers"])) {
    $_SESSION["sms_success_teachers"] = array();
}

// ۲. ذخیره/بروزرسانی زمان ارسال فقط برای همین معلم (بدون آسیب به بقیه)
$_SESSION["sms_success_teachers"][$T_ID] = time();

header("location:../report_card.php");
exit();
?>