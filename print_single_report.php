<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!(isset($_SESSION["state_login"]) && $_SESSION["type"] == 2 || $_SESSION["type"] == 3) || $_SESSION["type"] == 4) {
    header("location:login.php");
    exit();
}

$_POST['class_id'] = $_GET['class_id'] ?? 0;
$_POST['term_id'] = $_GET['term_id'] ?? 0;
?>
<!doctype html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>چاپ کارنامه دانش‌آموز</title>
    <link rel="stylesheet" href="styles/report_card.css">
    <link rel="stylesheet" href="styles/font.css">

    <style>
        @media print {

            .single-print-btn,
            .print-action-bar,
            .btn-sms,
            .btn-print,
            button,
            a {
                display: none !important;
            }

            body {
                background: #fff !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            .mymediu-card {
                box-shadow: none !important;
                border: none !important;
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }
        }
    </style>
</head>

<body>

    <main style="padding: 0; max-width: 100%;">
        <?php
        include("get_report_card.php");
        ?>
    </main>

    <script>
        window.onload = function () {
            setTimeout(function () {
                window.print();
            }, 300);
        };
    </script>
</body>

</html>