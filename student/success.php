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
    <title>پرداخت موفق</title>
    <link rel="icon" href="../images/icons/rahdanesh.png">
    <link rel="stylesheet" href="../styles/paymentResultStyle.css">
    <link rel="stylesheet" href="../styles/font.css">
</head>

<body>
    <div class="container">
        <div class="success-card">
            <div class="success-icon">✓</div>
            <h1>پرداخت با موفقیت انجام شد</h1>
            <p class="description">تراکنش شما با موفقیت ثبت شد
                خواهد شد.</p>
            <div class="info-box">
                <div class="info-row">
                    <span>شماره سفارش</span>
                    <strong>#
                        111111111
                    </strong>
                </div>
                <div class="info-row">
                    <span>شماره تراکنش</span>
                    <strong>
                        11111111111
                    </strong>
                </div>
            </div>
            <div class="buttons">
                <a href="../panel.php">بازگشت به پنل کاربری</a>
            </div>
        </div>
    </div>

</body>

</html>