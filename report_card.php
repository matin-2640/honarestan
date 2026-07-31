<?php
session_start();

// بررسی لاگین بودن کاربر
if (!isset($_SESSION["state_login"]) || $_SESSION["type"] > 2) {
    header("location:login.php");
    exit();
}

include("connect.php");

// دریافت لیست کلاس‌ها
try {
    $stmt = $connect->prepare("SELECT C_ID, C_Grade, C_Major FROM Classes ORDER BY C_Grade ASC");
    $stmt->execute();
    $classList = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $classList = [];
}
?>
<!doctype html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>صدور کارنامه | پورتال هنرستان</title>
    <link rel="icon" href="images/icons/rahdanesh.png">
    <link rel="stylesheet" href="styles/report_card.css">
    <link rel="stylesheet" href="styles/font.css">
    <script src="js/jquery-1.10.2.min.js"></script>
</head>

<body>
    <header class="panel-header">
        <div class="panel-container">
            <h1>سامانه صدور کارنامه دانش‌آموزان</h1>
        </div>
    </header>

    <main class="panel-container">
        <section class="filter-card">
            <div class="filter-grid" style="display: flex; flex-wrap: wrap; gap: 15px;">
                <div class="form-group" style="flex: 1; min-width: 200px;">
                    <label for="C_ID">انتخاب کلاس:</label>
                    <select id="C_ID" name="C_ID" class="input-field" required style="width: 100%;">
                        <option value="" disabled selected hidden>انتخاب کنید...</option>
                        <?php foreach ($classList as $cls): ?>
                            <option value="<?php echo $cls['C_ID']; ?>">
                                <?php echo "پایه " . $cls['C_Grade'] . " - " . $cls['C_Major']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group" style="flex: 1; min-width: 200px;">
                    <label for="G_term">انتخاب دوره (ترم):</label>
                    <select id="G_term" name="G_term" class="input-field" required style="width: 100%;">
                        <option value="" disabled selected hidden>انتخاب کنید...</option>
                        <option value="1">ماهانه - مهر و آبان</option>
                        <option value="2">ماهانه - آذر</option>
                        <option value="3">نوبت اول (دی ماه)</option>
                        <option value="4">ماهانه - اسفند</option>
                        <option value="5">ماهانه - فروردین و اردیبهشت</option>
                        <option value="6">نوبت دوم (خرداد ماه)</option>
                    </select>
                </div>

                <div class="form-group" style="width: 100%; margin-top: 10px;">
                    <label for="motivational_text" style="font-weight: bold; display: block; margin-bottom: 6px;">پیام مدیر:</label>
                    <textarea id="motivational_text" name="motivational_text" class="input-field" rows="3" style="width: 100%; box-sizing: border-box; resize: vertical; padding: 10px;" placeholder="متن پیام مدیر را وارد کنید..."></textarea>
                </div>
            </div>
        </section>

        <div id="report_card_results">
            <div class="placeholder-msg">لطفاً کلاس و دوره مورد نظر را انتخاب کنید.</div>
        </div>

        <a href="login.php" style="background-color: darkblue;" class="btn-sms">بازگشت به پنل</a>
    </main>

    <script>
        $(document).ready(function () {
            function fetchReportCards() {
                var classID = $('#C_ID').val();
                var termID = $('#G_term').val();
                var customText = $('#motivational_text').val();

                if (classID && termID) {
                    $('#report_card_results').html('<div class="loading-msg">در حال پردازش و دریافت اطلاعات...</div>');

                    $.ajax({
                        url: 'get_report_card.php',
                        type: 'POST',
                        data: {
                            class_id: classID,
                            term_id: termID,
                            custom_text: customText
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
            }

            $('#C_ID, #G_term').on('change', function () {
                fetchReportCards();
            });

            var timer;
            $('#motivational_text').on('keyup', function () {
                clearTimeout(timer);
                timer = setTimeout(fetchReportCards, 500);
            });
        });
    </script>
</body>

</html>
