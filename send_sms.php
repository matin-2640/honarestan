<?php
include("connect.php");

// دریافت لیست کلاس‌ها از دیتابیس برای پر کردن اپشن‌ها
$stmt_classes = $connect->prepare("SELECT C_ID, C_Grade, C_Major FROM Classes ORDER BY C_Grade ASC");
$stmt_classes->execute();
$classes = $stmt_classes->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <title>ارسال اس‌ام‌اس سفارشی</title>
    <link rel="stylesheet" href="styles/font.css">
    <link rel="stylesheet" href="js/sweetalert2.min.css">
    <link rel="icon" href="images/icons/rahdanesh.png">
    <link rel="stylesheet" href="styles/adminsms.css">
</head>

<body>

    <a href="admin_panel.php" class="btn-back">بازگشت به پنل مدیریت</a>

    <h2>ارسال پیامک اطلاع‌رسانی</h2>

    <form id="smsForm">
        <div>
            <label for="recipient_type">گیرندگان:</label>
            <select name="recipient_type" id="recipient_type" required>
                <option value="">-- انتخاب کنید --</option>
                <option value="all_students">همه هنرجویان</option>

                <!-- لیست کلاس‌ها -->
                <?php foreach ($classes as $class): ?>
                    <option value="class_<?php echo $class['C_ID']; ?>">
                        <?php echo $class['C_Grade'] . ' ' . $class['C_Major']; ?>
                    </option>
                <?php endforeach; ?>

                <option value="teachers">هنرآموزان</option>
            </select>
        </div>

        <!-- چک‌باکس والدین (در حالت اولیه مخفی) -->
        <div id="parent_checkbox_wrapper" style="display: none;">
            <label>
                <input type="checkbox" name="send_to_parents" id="send_to_parents" value="1">
                همچنین برای والدین نیز ارسال شود
            </label>
        </div>

        <div>
            <label for="sms_text">متن پیامک (حداکثر ۳۰۰ کاراکتر):</label><br>
            <textarea name="sms_text" id="sms_text" maxlength="300" rows="5" required></textarea>
            <div>
                <span id="char_count">0</span> / 300 کاراکتر
            </div>
        </div>

        <button type="submit" id="btnSubmit">ارسال پیامک</button>
    </form>
    <script src="js/sweetalert2.min.js"></script>
    <script src="js/jquery-1.10.2.min.js"></script>
    <script>
        $(document).ready(function () {

            // مدیریت نمایش / عدم نمایش چک‌باکس والدین
            $('#recipient_type').on('change', function () {
                var selectedValue = $(this).val();

                if (selectedValue === 'teachers' || selectedValue === '') {
                    $('#parent_checkbox_wrapper').hide();
                    $('#send_to_parents').prop('checked', false);
                } else {
                    $('#parent_checkbox_wrapper').show();
                }
            });

            // شمارش کاراکترهای متن
            $('#sms_text').on('input', function () {
                var length = $(this).val().length;
                $('#char_count').text(length);
            });

            // ارسال فرم با AJAX
            $('#smsForm').on('submit', function (e) {
                e.preventDefault();

                var recipient = $('#recipient_type').val();
                var text = $('#sms_text').val().trim();

                if (recipient === '') {
                    Swal.fire('خطا', 'لطفاً گیرنده پیامک را انتخاب کنید.', 'error');
                    return;
                }

                if (text === '') {
                    Swal.fire('خطا', 'لطفاً متن پیامک را وارد کنید.', 'error');
                    return;
                }

                $('#btnSubmit').prop('disabled', true).text('در حال ارسال...');

                $.ajax({
                    url: 'process_sms.php',
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function (response) {
                        $('#btnSubmit').prop('disabled', false).text('ارسال پیامک');

                        if (response.status === 'success') {
                            Swal.fire('موفقیت‌آمیز', response.message, 'success');
                            $('#smsForm')[0].reset();
                            $('#parent_checkbox_wrapper').hide();
                            $('#char_count').text('0');
                        } else {
                            Swal.fire('خطا', response.message, 'error');
                        }
                    },
                    error: function () {
                        $('#btnSubmit').prop('disabled', false).text('ارسال پیامک');
                        Swal.fire('خطا', 'مشکلی در ارتباط با سرور به وجود آمد.', 'error');
                    }
                });
            });

        });
    </script>

</body>

</html>