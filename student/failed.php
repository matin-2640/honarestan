<?php
session_start();

if (!(isset($_SESSION["state_login"]) && $_SESSION["type"] == 0)) {
    header("location:../login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>پرداخت ناموفق</title>
    <link rel="icon" href="../images/icons/rahdanesh.png">
    <link rel="stylesheet" href="../styles/paymentResultStyle.css">
    <link rel="stylesheet" href="../styles/font.css">

</head>

<body>
    <div class="container">
        <div class="failed-card">
            <div class="failed-icon">✕</div>
            <h1>پرداخت ناموفق بود</h1>
            <p class="description">متأسفانه عملیات پرداخت تکمیل نشد .</p>
            <div class="info-box">
                <div class="failed-message">متن خطا</div>
            </div>
            <div class="buttons">
                <a href="../panel.php">بازگشت به پنل کاربری</a>
            </div>
        </div>
    </div>

</body>

</html>