<?php
session_start();
if (!(isset($_SESSION["state_login"]) && $_SESSION["type"] == 0)) {
    header("location:../login.php");
    exit();
}
?>
<!doctype html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>مشاهده کارنامه دانش‌آموز | پورتال هنرستان</title>
    <link rel="icon" href="../images/icons/rahdanesh.png">
    <link rel="stylesheet" href="../styles/report_card.css">
    <link rel="stylesheet" href="../styles/font.css">
    <script src="../js/jquery-1.10.2.min.js"></script>
</head>

<body>
    <header class="panel-header">
        <div class="panel-container">
            <h1>سامانه مشاهده کارنامه دانش‌آموزی</h1>
        </div>
    </header>

    <main class="panel-container">
        <section class="filter-card">
            <div class="filter-grid" style="display: flex; flex-wrap: wrap; gap: 15px;">
                <div class="form-group" style="flex: 1; min-width: 250px;">
                    <label for="G_term">انتخاب دوره (ترم):</label>
                    <select id="G_term" name="G_term" class="input-field" required style="width: 100%;">
                        <option value="" disabled selected hidden>دوره مورد نظر را انتخاب کنید...</option>
                        <option value="1">ماهانه - مهر و آبان</option>
                        <option value="2">ماهانه - آذر</option>
                        <option value="3">نوبت اول (دی ماه)</option>
                        <option value="4">ماهانه - اسفند</option>
                        <option value="5">ماهانه - فروردین و اردیبهشت</option>
                        <option value="6">نوبت دوم (خرداد ماه)</option>
                    </select>
                </div>
            </div>
        </section>

        <div id="report_card_results">
            <div class="placeholder-msg">لطفاً دوره تحصیلی مورد نظر را جهت دریافت کارنامه انتخاب کنید.</div>
        </div>

        <a href="../login.php" style="background-color: darkblue; margin-top: 15px;" class="btn-sms">بازگشت به پنل</a>
    </main>

    <script>
        $(document).ready(function () {
            $('#G_term').on('change', function () {
                var termID = $(this).val();

                if (termID) {
                    $('#report_card_results').html('<div class="loading-msg">در حال پردازش و دریافت اطلاعات کارنامه...</div>');

                    $.ajax({
                        url: 'get_student_report_card.php',
                        type: 'POST',
                        data: {
                            term_id: termID
                        },
                        dataType: 'html',
                        success: function (response) {
                            $('#report_card_results').html(response);
                        },
                        error: function () {
                            $('#report_card_results').html('<div class="error-msg">خطا در دریافت اطلاعات از سرور.</div>');
                        }
                    });
                }
            });
        });
    </script>
</body>

</html>
