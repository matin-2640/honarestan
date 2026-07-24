<?php
session_start();
if (!(isset($_SESSION["state_login"]) && $_SESSION["type"] <= 2)) {
    header("location:login.php");
    exit();
}

include("connect.php");

try {
    $stmt_classes = $connect->prepare("SELECT C_ID, C_Grade, C_Major FROM Classes ORDER BY C_Grade ASC");
    $stmt_classes->execute();
    $classList = $stmt_classes->fetchAll(PDO::FETCH_ASSOC);
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
    <link rel="stylesheet" href="styles/font.css">
    <link rel="icon" href="images/icons/rahdanesh.png">
    <link rel="stylesheet" href="styles/report_card.css">
    <script src="js/jquery-1.10.2.min.js"></script>
</head>

<body>
    <header class="panel-header">
        <div class="panel-container">
            <h1>سامانه صدور کارنامه دانش‌آموزان</h1>
        </div>
    </header>

    <main class="panel-container">
        <!-- بخش فیلتر انتخاب کلاس و ترم -->
        <section class="filter-card">
            <div class="filter-grid">
                <div class="form-group">
                    <label for="C_ID">انتخاب کلاس:</label>
                    <select id="C_ID" name="C_ID" class="input-field" required>
                        <option value="" disabled selected hidden>انتخاب کنید...</option>
                        <?php foreach ($classList as $cls): ?>
                            <option value="<?php echo $cls['C_ID']; ?>">
                                <?php echo "پایه " . $cls['C_Grade'] . " - " . $cls['C_Major']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="G_term">انتخاب دوره (ترم):</label>
                    <select id="G_term" name="G_term" class="input-field" required>
                        <option value="" disabled selected hidden>انتخاب کنید...</option>
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

        <!-- محل نمایش کارنامه‌ها -->
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

                if (classID && termID) {
                    $('#report_card_results').html('<div class="loading-msg">در حال پردازش و دریافت اطلاعات...</div>');

                    $.ajax({
                        url: 'get_report_card.php',
                        type: 'POST',
                        data: {
                            class_id: classID,
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
            }

            $('#C_ID, #G_term').on('change', function () {
                fetchReportCards();
            });
        });
    </script>
</body>

</html>