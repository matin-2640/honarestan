<?php
include("connect.php");
session_start();
if (!(isset($_SESSION["state_login"]) && $_SESSION["type"] == 2 || $_SESSION["type"] == 3) || $_SESSION["type"] == 4) {
    header("location:login.php");
    exit();
}
$stmt_classes = $connect->prepare("SELECT C_ID, C_Grade, C_Major FROM Classes ORDER BY C_Grade ASC");
$stmt_classes->execute();
$classes = $stmt_classes->fetchAll(PDO::FETCH_ASSOC);
?><!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
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
</head>

<body>
    <style>
        #rahdaneshAdminMenu,
        #rahdaneshAdminMenu * {
            box-sizing: border-box;
        }

        #rahdaneshAdminMenu {
            direction: rtl;
            font-family: Tahoma, Arial, sans-serif;
        }

        #rahdaneshAdminMenu .ram-header {
            position: fixed;
            top: 0;
            right: 0;
            left: 0;
            width: 100%;
            height: 70px;
            z-index: 2147483646;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            background: #fff;
            border-bottom: 1px solid #e5e7eb;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
        }

        #rahdaneshAdminMenu .ram-header-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        #rahdaneshAdminMenu .ram-logo {
            font-size: 17px;
            font-weight: 800;
            color: #1f2937;
            white-space: nowrap;
        }

        #rahdaneshAdminMenu .ram-logo span {
            color: #2563eb;
        }

        #rahdaneshAdminMenu .ram-toggle {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: 42px !important;
            height: 42px !important;
            min-width: 42px !important;
            padding: 0 !important;
            margin: 0 !important;
            border: 0 !important;
            border-radius: 8px !important;
            background: #e5e7eb !important;
            cursor: pointer !important;
            outline: 0 !important;
            box-shadow: none !important;
        }

        #rahdaneshAdminMenu .ram-toggle:hover {
            background: #2563eb !important;
        }

        #rahdaneshAdminMenu .ram-toggle img {
            display: block !important;
            width: 25px !important;
            height: 25px !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        #rahdaneshAdminMenu .ram-sidebar {
            position: fixed !important;
            top: 70px !important;
            right: -280px !important;
            bottom: 0 !important;
            width: 280px !important;
            height: calc(100vh - 70px) !important;
            z-index: 2147483647 !important;
            display: flex !important;
            flex-direction: column !important;
            justify-content: space-between !important;
            padding: 20px 0 !important;
            margin: 0 !important;
            background: #1e293b !important;
            color: #f8fafc !important;
            box-shadow: -5px 0 20px rgba(0, 0, 0, 0.25) !important;
            transition: right 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
            overflow: hidden !important;
        }

        #rahdaneshAdminMenu .ram-sidebar.ram-open {
            right: 0 !important;
        }

        #rahdaneshAdminMenu .ram-brand {
            display: flex !important;
            align-items: center !important;
            gap: 10px !important;
            padding: 0 20px 15px !important;
            margin: 0 !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;
            color: #fff !important;
            font-size: 16px !important;
            font-weight: 700 !important;
        }

        #rahdaneshAdminMenu .ram-brand img {
            display: block !important;
            width: 25px !important;
            height: 25px !important;
            flex: none !important;
        }

        #rahdaneshAdminMenu .ram-nav {
            flex: 1 !important;
            padding: 20px 10px !important;
            margin: 0 !important;
            overflow-y: auto !important;
            overflow-x: hidden !important;
        }

        #rahdaneshAdminMenu .ram-nav ul {
            display: block !important;
            list-style: none !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        #rahdaneshAdminMenu .ram-nav li {
            display: block !important;
            padding: 0 !important;
            margin: 0 0 8px !important;
        }

        #rahdaneshAdminMenu .ram-nav a {
            display: flex !important;
            align-items: center !important;
            gap: 12px !important;
            width: 100% !important;
            padding: 12px 15px !important;
            margin: 0 !important;
            border: 0 !important;
            border-radius: 8px !important;
            background: transparent !important;
            color: #cbd5e1 !important;
            font-size: 14px !important;
            font-weight: 500 !important;
            text-decoration: none !important;
            line-height: normal !important;
            box-shadow: none !important;
            transition:
                background 0.2s,
                color 0.2s !important;
        }

        #rahdaneshAdminMenu .ram-nav a:hover,
        #rahdaneshAdminMenu .ram-nav a.ram-active {
            background: #2563eb !important;
            color: #fff !important;
        }

        #rahdaneshAdminMenu .ram-nav a img {
            display: block !important;
            width: 20px !important;
            height: 20px !important;
            flex: none !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        #rahdaneshAdminMenu .ram-footer {
            display: flex !important;
            flex-direction: column !important;
            gap: 10px !important;
            padding: 15px !important;
            margin: 0 !important;
            border-top: 1px solid rgba(255, 255, 255, 0.1) !important;
        }

        #rahdaneshAdminMenu .ram-footer a {
            display: flex !important;
            align-items: center !important;
            gap: 10px !important;
            width: 100% !important;
            padding: 10px !important;
            margin: 0 !important;
            border-radius: 6px !important;
            font-size: 13px !important;
            text-decoration: none !important;
            line-height: normal !important;
        }

        #rahdaneshAdminMenu .ram-footer a img {
            display: block !important;
            width: 20px !important;
            height: 20px !important;
            flex: none !important;
        }

        #rahdaneshAdminMenu .ram-home {
            background: rgba(255, 255, 255, 0.05) !important;
            color: #e2e8f0 !important;
        }

        #rahdaneshAdminMenu .ram-home:hover {
            background: rgba(255, 255, 255, 0.15) !important;
        }

        #rahdaneshAdminMenu .ram-logout {
            background: rgba(220, 38, 38, 0.15) !important;
            color: #fca5a5 !important;
        }

        #rahdaneshAdminMenu .ram-logout:hover {
            background: rgba(239, 68, 68, 0.4) !important;
            color: #fff !important;
        }

        #rahdaneshAdminMenu .ram-overlay {
            position: fixed !important;
            top: 70px !important;
            right: 0 !important;
            bottom: 0 !important;
            left: 0 !important;
            width: 100% !important;
            height: calc(100vh - 70px) !important;
            z-index: 2147483645 !important;
            background: rgba(0, 0, 0, 0.5) !important;
            opacity: 0 !important;
            visibility: hidden !important;
            pointer-events: none !important;
            transition:
                opacity 0.3s,
                visibility 0.3s !important;
        }

        #rahdaneshAdminMenu .ram-overlay.ram-show {
            opacity: 1 !important;
            visibility: visible !important;
            pointer-events: auto !important;
        }

        @media (max-width: 600px) {
            #rahdaneshAdminMenu .ram-header {
                height: 60px;
                padding: 0 12px;
            }

            #rahdaneshAdminMenu .ram-logo {
                font-size: 14px;
            }

            #rahdaneshAdminMenu .ram-toggle {
                width: 40px !important;
                height: 40px !important;
                min-width: 40px !important;
            }

            #rahdaneshAdminMenu .ram-sidebar {
                top: 60px !important;
                width: 280px !important;
                height: calc(100vh - 60px) !important;
                right: -280px !important;
            }

            #rahdaneshAdminMenu .ram-overlay {
                top: 60px !important;
                height: calc(100vh - 60px) !important;
            }
        }
    </style>

    <div id="rahdaneshAdminMenu">
        <header class="ram-header">
            <div class="ram-header-right">
                <button type="button" id="ramToggle" class="ram-toggle">
                    <img src="images/icons/menu.png" width="25" height="25" />
                </button>
                <div class="ram-logo"><span>پنل مدیریت</span> | هنرستان راه دانش</div>
            </div>
        </header>

        <nav id="ramSidebar" class="ram-sidebar">
            <div class="ram-brand">
                <img src="images/icons/user.png" width="25" height="25" /><span>داشبورد مدیریت</span>
            </div>

            <div class="ram-nav">
                <ul>
                    <li>
                        <a href="panel.php"><img src="images/icons/first.png" width="20"
                                height="20" /><span>خانه</span></a>
                    </li>
                    <li>
                        <a href="teachers_list.php"><img src="images/icons/teachers.png" width="20"
                                height="20" /><span>لیست معلمین</span></a>
                    </li>
                    <li>
                        <a href="classes_list.php"><img src="images/icons/school.png" width="20"
                                height="20" /><span>لیست کلاس ها</span></a>
                    </li>
                    <li>
                        <a href="courses_list.php"><img src="images/icons/manageroles.png" width="20"
                                height="20" /><span>لیست دروس</span></a>
                    </li>
                    <li>
                        <a href="add_score.php"><img src="images/icons/scorewhite.png" width="20"
                                height="20" /><span>ثبت نمره</span></a>
                    </li>
                    <li>
                        <a href="send_sms.php" class="ram-active"><img src="images/icons/sendsms.png" width="20"
                                height="20" /><span>ارسال پیام</span></a>
                    </li>
                    <li>
                        <a href="admin_pass.php"><img src="images/icons/edituser.png" width="20"
                                height="20" /><span>تغییر رمز عبور</span></a>
                    </li>
                    <li>
                        <a href="attendance_reports.php"><img src="images/icons/visit.png" width="20"
                                height="20" /><span>لیست حضور و غیاب</span></a>
                    </li>
                    <li>
                        <a href="certificate.php"><img src="images/icons/manageroles.png" width="20"
                                height="20" /><span>بازگذاری لوح تقدیر</span></a>
                    </li>
                    <li>
                        <a href="database_reset.php"><img src="images/icons/reset.png" width="20"
                                height="20" /><span>پاکسازی اطلاعات سال گذشته</span></a>
                    </li>
                </ul>
            </div>

            <div class="ram-footer">
                <a href="index.php" class="ram-home"><img src="images/icons/back.png" width="20"
                        height="20" /><span>بازگشت به سایت</span></a>
                <a href="logout.php" class="ram-logout"><img src="images/icons/leave.png" width="20"
                        height="20" /><span>خروج از حساب</span></a>
            </div>
        </nav>

        <div id="ramOverlay" class="ram-overlay"></div>
    </div>

    <script>
        (function () {
            function initRahdaneshMenu() {
                const root = document.getElementById("rahdaneshAdminMenu");
                if (!root) return;
                const toggle = root.querySelector("#ramToggle");
                const sidebar = root.querySelector("#ramSidebar");
                const overlay = root.querySelector("#ramOverlay");
                if (!toggle || !sidebar || !overlay) return;
                function open() {
                    sidebar.classList.add("ram-open");
                    overlay.classList.add("ram-show");
                }
                function close() {
                    sidebar.classList.remove("ram-open");
                    overlay.classList.remove("ram-show");
                }
                toggle.addEventListener("click", function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    sidebar.classList.contains("ram-open") ? close() : open();
                });
                overlay.addEventListener("click", close);
                root.querySelectorAll(".ram-nav a,.ram-footer a").forEach(function (a) {
                    a.addEventListener("click", close);
                });
                document.addEventListener("keydown", function (e) {
                    if (e.key === "Escape") close();
                });
            }
            if (document.readyState === "loading") {
                document.addEventListener("DOMContentLoaded", initRahdaneshMenu);
            } else {
                initRahdaneshMenu();
            }
        })();
    </script>
    <br><br>

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
                    <input type="checkbox" name="send_to_parents" id="send_to_parents" value="1"
                        style="width: 18px; height: 18px;">

                    همچنین برای والدین نیز ارسال شود
                </label>
            </div>


            <div>
                <label for="sms_text">
                    متن پیامک :
                </label>
                <br>

                <textarea name="sms_text" id="sms_text" maxlength="300" rows="5" required></textarea>

                <div style="margin-top: 5px; font-size: 0.85rem; color: #64748b;">
                    <span id="char_count">0</span> / 300 کاراکتر
                </div>
            </div>


            <button type="submit" id="btnSubmit"
                style="background: #2563eb; color: #fff; border: none; padding: 12px 25px; border-radius: 8px; font-weight: bold; cursor: pointer;">
                ارسال پیامک
            </button>
            <br>
            <a href="admin_panel.php">
                <button type="submit" id="btnSubmit"
                    style="background: #2563eb; color: #fff; border: none; padding: 12px 25px; border-radius: 8px; font-weight: bold; cursor: pointer;">
                    بازگشت به پنل مدیریت
                </button>
            </a>
        </form>
    </div>


    <script src="js/sweetalert2.min.js"></script>
    <script src="js/jquery-1.10.2.min.js"></script>
    <script src="https://unpkg.com/lenis@1.3.11/dist/lenis.min.js"></script>
    <script type="text/javascript" src="js/theme.js"></script>

    <script>

        $(document).ready(function () {

            $('#recipient_type').on('change', function () {

                var selectedValue = $(this).val();

                $('#recipients_list')
                    .hide()
                    .html('');

                $('#send_to_parents').prop('checked', false);

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


                else if (selectedValue === 'all_students') {

                    $('#parent_checkbox_wrapper').show();

                }

                else {

                    $('#parent_checkbox_wrapper').hide();
                }

            });


            $('#sms_text').on('input', function () {

                $('#char_count').text($(this).val().length);

            });

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

</body>

</html>