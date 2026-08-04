<?php
include("connect.php");

// دریافت لیست کلاس‌ها
$stmt_classes = $connect->prepare("SELECT C_ID, C_Grade, C_Major FROM Classes ORDER BY C_Grade ASC");
$stmt_classes->execute();
$classes = $stmt_classes->fetchAll(PDO::FETCH_ASSOC);
?><!DOCTYPE html><html lang="fa" dir="rtl"><head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <title>ارسال اس‌ام‌اس سفارشی</title>
    <link rel="stylesheet" href="styles/font.css">
    <link rel="stylesheet" href="styles/panel_style.css" />
    <link rel="stylesheet" href="styles/profile_style.css" />
    <link rel="stylesheet" href="js/sweetalert2.min.css">
    <link rel="icon" href="images/icons/rahdanesh.png">
    <link rel="stylesheet" href="styles/adminsms.css">
    <style>
        /* استایل‌های دارک‌مود برای تمامی المان‌های صفحه و بزرگ‌سازی کادرها */
        [data-theme="dark"] body {
            background-color: #0f172a !important;
            color: #f8fafc !important;
        }
        [data-theme="dark"] h2 {
            color: #f8fafc !important;
        }
        [data-theme="dark"] form#smsForm {
            background-color: #1e293b !important;
            color: #f8fafc !important;
            border-color: #334155 !important;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3) !important;
        }
        [data-theme="dark"] select,
        [data-theme="dark"] textarea {
            background-color: #0f172a !important;
            color: #f8fafc !important;
            border-color: #475569 !important;
        }
        [data-theme="dark"] select:focus,
        [data-theme="dark"] textarea:focus {
            border-color: #3b82f6 !important;
        }
        [data-theme="dark"] label {
            color: #cbd5e1 !important;
        }
        [data-theme="dark"] div#recipients_list {
            background-color: #0f172a !important;
            color: #f8fafc !important;
            border-color: #475569 !important;
        }

        /* ساختار عرض فرم، ریسپانسیو و وسط‌چین بودن صحیح */
        .page-container {
            width: 100%;
            max-width: 1300px;
            margin: 30px auto;
            padding: 0 20px;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .page-container h2 {
            width: 100%;
            max-width: 1300px;
            text-align: right;
            margin-bottom: 20px;
        }
        form#smsForm {
            width: 100%;
            max-width: 1300px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            box-sizing: border-box;
        }
        form#smsForm select,
        form#smsForm textarea {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            box-sizing: border-box;
            font-family: inherit;
        }
        form#smsForm div {
            margin-bottom: 20px;
        }
        form#smsForm label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }
    </style>
</head><body>
    <header class="panel-header">
        <div class="panel-container header-wrapper">
            <div class="user-profile-brief">
                <div class="user-avatar-mini">
                    <svg viewBox="0 0 24 24" class="avatar-svg-placeholder">
                        <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z" />
                    </svg>
                </div>
                <div class="user-info-text">
                    <span>پنل مدیریت هنرستان</span>
                    <small>ارسال پیامک</small>
                </div>
            </div>

            <nav class="panel-nav" id="panelNav">
                <a href="admin_panel.php">
                    <svg viewBox="0 0 24 24" class="nav-svg-icon">
                        <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z" />
                    </svg>
                    صفحه نخست
                </a>
                <a href="#" class="active">
                    <svg viewBox="0 0 24 24" class="nav-svg-icon">
                        <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z" />
                    </svg>
                    ارسال پیامک
                </a>
                <a href="admin_panel.php" class="back-link-btn">
                    <svg viewBox="0 0 24 24" class="nav-svg-icon">
                        <path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z" />
                    </svg>
                    بازگشت
                </a>
            </nav>

            <div class="header-actions">
                <button class="theme-toggle" id="themeToggle" title="تغییر حالت شب و روز">
                    <svg viewBox="0 0 24 24" class="theme-svg-icon" id="themeIcon">
                        <path class="moon-path"
                            d="M12.3 2a10 10 0 0 0-1.9 19.8 10 10 0 0 0 11.8-11.8A10 10 0 0 1 12.3 2z" />
                    </svg>
                </button>
            </div>
        </div>
    </header>

<div class="page-container">
<h2>ارسال پیامک اطلاع‌رسانی</h2>

<form id="smsForm">

    <div>
        <label for="recipient_type">گیرندگان:</label>

        <select name="recipient_type" id="recipient_type" required>
            <option value="">-- انتخاب کنید --</option>

            <option value="all_students">همه هنرجویان</option>

            <?php foreach ($classes as $class): ?>
                <option value="class_<?php echo htmlspecialchars($class['C_ID']); ?>">
                    <?php echo htmlspecialchars($class['C_Grade'] . ' ' . $class['C_Major']); ?>
                </option>
            <?php endforeach; ?>

            <option value="teachers">هنرآموزان</option>
        </select>
    </div>


    <div id="recipients_list" style="display:none;"></div>


    <div id="parent_checkbox_wrapper" style="display:none;">
        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
            <input
                type="checkbox"
                name="send_to_parents"
                id="send_to_parents"
                value="1" style="width: 18px; height: 18px;">

            همچنین برای والدین نیز ارسال شود
        </label>
    </div>


    <div>
        <label for="sms_text">
            متن پیامک (حداکثر ۳۰۰ کاراکتر):
        </label>
        <br>

        <textarea
            name="sms_text"
            id="sms_text"
            maxlength="300"
            rows="5"
            required></textarea>

        <div style="margin-top: 5px; font-size: 0.85rem; color: #64748b;">
            <span id="char_count">0</span> / 300 کاراکتر
        </div>
    </div>


    <button type="submit" id="btnSubmit" style="background: #2563eb; color: #fff; border: none; padding: 12px 25px; border-radius: 8px; font-weight: bold; cursor: pointer;">
        ارسال پیامک
    </button>

</form>
</div>


<script src="js/sweetalert2.min.js"></script>
<script src="js/jquery-1.10.2.min.js"></script>
<script src="https://unpkg.com/lenis@1.3.11/dist/lenis.min.js"></script>
<script type="text/javascript" src="js/theme.js"></script>

<script>

    $(document).ready(function () {


        /*
         * تغییر نوع گیرنده
         */
        $('#recipient_type').on('change', function () {

            var selectedValue = $(this).val();

            $('#recipients_list')
                .hide()
                .html('');

            $('#send_to_parents').prop('checked', false);


            /*
             * کلاس
             */
            if (selectedValue.indexOf('class_') === 0) {

                $('#parent_checkbox_wrapper').show();

                $('#recipients_list')
                    .show()
                    .html('در حال دریافت هنرجویان...');


                $.ajax({

                    url: 'process_sms.php',

                    type: 'POST',

                    dataType: 'json',

                    data: {
                        action: 'get_recipients',
                        recipient_type: selectedValue
                    },

                    success: function (response) {

                        if (response.status === 'success') {

                            $('#recipients_list').html(response.html);

                        } else {

                            $('#recipients_list').html(
                                '<div>' + response.message + '</div>'
                            );
                        }
                    },

                    error: function () {

                        $('#recipients_list').html(
                            '<div>خطا در دریافت لیست هنرجویان.</div>'
                        );
                    }
                });

            }


            /*
             * هنرآموزان
             */
            else if (selectedValue === 'teachers') {

                $('#parent_checkbox_wrapper').hide();

                $('#recipients_list')
                    .show()
                    .html('در حال دریافت هنرآموزان...');


                $.ajax({

                    url: 'process_sms.php',

                    type: 'POST',

                    dataType: 'json',

                    data: {
                        action: 'get_recipients',
                        recipient_type: 'teachers'
                    },

                    success: function (response) {

                        if (response.status === 'success') {

                            $('#recipients_list').html(response.html);

                        } else {

                            $('#recipients_list').html(
                                '<div>' + response.message + '</div>'
                            );
                        }
                    },

                    error: function () {

                        $('#recipients_list').html(
                            '<div>خطا در دریافت لیست هنرآموزان.</div>'
                        );
                    }
                });

            }


            /*
             * همه هنرجویان
             */
            else if (selectedValue === 'all_students') {

                $('#parent_checkbox_wrapper').show();

            }


            /*
             * حالت خالی
             */
            else {

                $('#parent_checkbox_wrapper').hide();
            }

        });


        /*
         * شمارش کاراکتر
         */
        $('#sms_text').on('input', function () {

            $('#char_count').text($(this).val().length);

        });


        /*
         * ارسال فرم
         */
        $('#smsForm').on('submit', function (e) {

            e.preventDefault();


            var recipient = $('#recipient_type').val();

            var text = $('#sms_text').val().trim();


            if (recipient === '') {

                Swal.fire(
                    'خطا',
                    'لطفاً گیرنده پیامک را انتخاب کنید.',
                    'error'
                );

                return;
            }


            if (text === '') {

                Swal.fire(
                    'خطا',
                    'لطفاً متن پیامک را وارد کنید.',
                    'error'
                );

                return;
            }


            /*
             * اگر کلاس یا معلم انتخاب شده،
             * حداقل یک نفر باید تیک خورده باشد.
             */
            if (
                recipient.indexOf('class_') === 0 ||
                recipient === 'teachers'
            ) {

                var selectedCount =
                    $('#recipients_list input.recipient-checkbox:checked').length;


                if (selectedCount === 0) {

                    Swal.fire(
                        'خطا',
                        'لطفاً حداقل یک نفر را برای ارسال پیامک انتخاب کنید.',
                        'error'
                    );

                    return;
                }
            }


            $('#btnSubmit')
                .prop('disabled', true)
                .text('در حال ارسال...');


            $.ajax({

                url: 'process_sms.php',

                type: 'POST',

                data: $(this).serialize(),

                dataType: 'json',

                success: function (response) {

                    $('#btnSubmit')
                        .prop('disabled', false)
                        .text('ارسال پیامک');


                    if (response.status === 'success') {

                        Swal.fire(
                            'موفقیت‌آمیز',
                            response.message,
                            'success'
                        );


                        $('#smsForm')[0].reset();

                        $('#recipients_list')
                            .hide()
                            .html('');

                        $('#parent_checkbox_wrapper').hide();

                        $('#char_count').text('0');

                    } else {

                        Swal.fire(
                            'خطا',
                            response.message,
                            'error'
                        );
                    }
                },


                error: function () {

                    $('#btnSubmit')
                        .prop('disabled', false)
                        .text('ارسال پیامک');


                    Swal.fire(
                        'خطا',
                        'مشکلی در ارتباط با سرور به وجود آمد.',
                        'error'
                    );
                }
            });

        });

    });

</script>

</body></html>
