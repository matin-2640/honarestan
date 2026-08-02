<?php
/**
 * تابع ارسال کد تایید با استفاده از سامانه ملی‌پیامک
 * 
 * @param string $phone شماره همراه دریافت‌کننده
 * @param string $code  کد 6 رقمی تولید شده
 * @return bool
 */
function sendOtpSms($phone, $code) {
    $url = 'https://console.melipayamak.com/api/send/shared/08bebad81c6a4c1bab324b7f167cd87f';

    $data = array(
        'bodyId' => 507121,
        'to' => $phone,
        'args' => [
            "$code"
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
    $curl_error = curl_errno($ch);

    curl_close($ch);

    // اگر خطایی در اجرای کرل نباشد، خروجی موفقیت‌آمیز برمی‌گرداند
    if ($curl_error) {
        return false;
    }

    return true;
}
?>
