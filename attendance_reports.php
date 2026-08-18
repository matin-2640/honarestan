<?php
include("connect.php");

$start_date = isset($_GET['start_date']) ? trim($_GET['start_date']) : '';
$end_date = isset($_GET['end_date']) ? trim($_GET['end_date']) : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$hasDateRange = !empty($start_date) && !empty($end_date);

$sql = "SELECT ar.*, s.Stu_fullName, s.Stu_nationalCode, c.C_grade, c.C_major 
        FROM attendance ar
        JOIN students s ON ar.A_studentID = s.Stu_ID
        JOIN classes c ON s.Stu_classID = c.C_ID
        WHERE (ar.A_state = '0' OR ar.A_type = '0')";

$params = [];

if ($hasDateRange) {
    $sql .= " AND ar.A_date BETWEEN :start_date AND :end_date";
    $params[':start_date'] = $start_date;
    $params[':end_date'] = $end_date;
}

if (!empty($search) && $hasDateRange) {
    $sql .= " AND (
        s.Stu_fullName LIKE :search
        OR s.Stu_nationalCode LIKE :search
    )";

    $params[':search'] = '%' . $search . '%';
}

$sql .= " ORDER BY ar.A_date DESC";

$result = null;

if ($hasDateRange) {
    $stmt = $connect->prepare($sql);
    $stmt->execute($params);
    $result = $stmt;
}

if (
    isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
) {

    if (!$hasDateRange) {
        echo '<div class="no-record">ابتدا بازه شروع و پایان جستجو را انتخاب کنید.</div>';
        exit;
    }

    if ($result && $result->rowCount() > 0) {

        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {

            $name = $row['Stu_fullName'];
            $national = $row['Stu_nationalCode'];
            $class_name = "پایه " . $row['C_grade'] . " - " . $row['C_major'];
            $date = $row['A_date'];

            echo '<div class="card">';
            echo '<h4>' . htmlspecialchars($name) . '</h4>';
            echo '<p><b>کد ملی:</b> ' . htmlspecialchars($national) . '</p>';
            echo '<p><b>کلاس:</b> ' . htmlspecialchars($class_name) . '</p>';
            echo '<p><b>تاریخ غیبت:</b> ' . htmlspecialchars($date) . '</p>';
            echo '</div>';
        }

    } else {

        echo '<div class="no-record">هیچ موردی برای نمایش یافت نشد.</div>';
    }

    exit;
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>گزارش غیبت‌های دانش‌آموزان</title>

    <link rel="stylesheet" href="styles/font.css">
    <link rel="stylesheet" href="styles/panel_style.css">
    <link rel="stylesheet" href="styles/profile_style.css">
    <link rel="stylesheet" href="styles/attendance_reports.css">
    <link rel="icon" href="images/icons/rahdanesh.png">

    <link rel="stylesheet"
        href="https://unpkg.com/persian-datepicker@1.2.0/dist/css/persian-datepicker.min.css">

    <style>

        [data-theme="dark"] body {
            background-color: #0f172a !important;
            color: #f8fafc !important;
        }

        [data-theme="dark"] h2 {
            color: #f8fafc !important;
        }

        [data-theme="dark"] .container {
            background-color: #1e293b !important;
            color: #f8fafc !important;
            border-color: #334155 !important;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3) !important;
        }

        [data-theme="dark"] input[type="text"],
        [data-theme="dark"] select,
        [data-theme="dark"] textarea {
            background-color: #0f172a !important;
            color: #f8fafc !important;
            border-color: #475569 !important;
        }

        [data-theme="dark"] input[type="text"]:focus,
        [data-theme="dark"] select:focus,
        [data-theme="dark"] textarea:focus {
            border-color: #3b82f6 !important;
        }

        [data-theme="dark"] label {
            color: #e1e4e8 !important;
        }

        [data-theme="dark"] .card {
            background-color: #0f172a !important;
            color: #f8fafc !important;
            border-color: #334155 !important;
        }

        [data-theme="dark"] .no-record {
            color: #94a3b8 !important;
        }

        [data-theme="dark"] .card,
        [data-theme="dark"] .card * {
            color: #ffffff !important;
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

        .container {
            width: 100%;
            max-width: 1300px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            box-sizing: border-box;
        }

        .page-container h2 {
            width: 100%;
            max-width: 1300px;
            text-align: right;
            margin-bottom: 20px;
        }

        .btn-view-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background-color: #2563eb;
            color: #ffffff;
            text-decoration: none;
            padding: 12px 20px;
            border-radius: 8px;
            font-weight: bold;
            font-size: 14px;
            margin-top: 10px;
        }

        .date-error {
            color: #ef4444;
            font-size: 13px;
            margin-top: 8px;
            display: none;
            font-weight: bold;
        }

        .default-message {
            width: 100%;
            text-align: center;
            color: #64748b;
            padding: 30px;
            box-sizing: border-box;
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
            transition: background 0.2s, color 0.2s !important;
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
            transition: opacity 0.3s, visibility 0.3s !important;
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
                    <img src="images/icons/menu.png" width="25" height="25">
                </button>

                <div class="ram-logo">
                    <span>پنل مدیریت</span> | هنرستان راه دانش
                </div>

            </div>

        </header>

        <nav id="ramSidebar" class="ram-sidebar">

            <div class="ram-brand">
                <img src="images/icons/user.png" width="25" height="25">
                <span>داشبورد مدیریت</span>
            </div>

            <div class="ram-nav">

                <ul>

                    <li>
                        <a href="panel.php">
                            <img src="images/icons/first.png" width="20" height="20">
                            <span>خانه</span>
                        </a>
                    </li>

                    <li>
                        <a href="teachers_list.php">
                            <img src="images/icons/teachers.png" width="20" height="20">
                            <span>لیست معلمین</span>
                        </a>
                    </li>

                    <li>
                        <a href="classes_list.php">
                            <img src="images/icons/school.png" width="20" height="20">
                            <span>لیست کلاس ها</span>
                        </a>
                    </li>

                    <li>
                        <a href="courses_list.php">
                            <img src="images/icons/manageroles.png" width="20" height="20">
                            <span>لیست دروس</span>
                        </a>
                    </li>

                    <li>
                        <a href="add_score.php">
                            <img src="images/icons/scorewhite.png" width="20" height="20">
                            <span>ثبت نمره</span>
                        </a>
                    </li>

                    <li>
                        <a href="send_sms.php">
                            <img src="images/icons/sendsms.png" width="20" height="20">
                            <span>ارسال پیام</span>
                        </a>
                    </li>

                    <li>
                        <a href="admin_pass.php">
                            <img src="images/icons/edituser.png" width="20" height="20">
                            <span>تغییر رمز عبور</span>
                        </a>
                    </li>

                    <li>
                        <a href="attendance_reports.php" class="ram-active">
                            <img src="images/icons/visit.png" width="20" height="20">
                            <span>لیست حضور و غیاب</span>
                        </a>
                    </li>

                    <li>
                        <a href="certificate.php">
                            <img src="images/icons/manageroles.png" width="20" height="20">
                            <span>بازگذاری لوح تقدیر</span>
                        </a>
                    </li>

                    <li>
                        <a href="database_reset.php">
                            <img src="images/icons/reset.png" width="20" height="20">
                            <span>پاکسازی اطلاعات سال گذشته</span>
                        </a>
                    </li>

                </ul>

            </div>

            <div class="ram-footer">

                <a href="index.php" class="ram-home">
                    <img src="images/icons/back.png" width="20" height="20">
                    <span>بازگشت به سایت</span>
                </a>

                <a href="logout.php" class="ram-logout">
                    <img src="images/icons/leave.png" width="20" height="20">
                    <span>خروج از حساب</span>
                </a>

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

                    sidebar.classList.contains("ram-open")
                        ? close()
                        : open();
                });

                overlay.addEventListener("click", close);

                root.querySelectorAll(".ram-nav a,.ram-footer a").forEach(function (a) {
                    a.addEventListener("click", close);
                });

                document.addEventListener("keydown", function (e) {
                    if (e.key === "Escape") {
                        close();
                    }
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

        <h2>گزارش غیبت‌های دانش‌آموزان</h2>

        <div class="container">

            <form id="searchForm" onsubmit="return false;">

                <div class="form-group">

                    <label>از تاریخ:</label>

                    <input
                        type="text"
                        name="start_date"
                        id="startDate"
                        value="<?php echo htmlspecialchars($start_date); ?>"
                        placeholder="1403/01/01"
                        autocomplete="off">

                </div>

                <div class="form-group">

                    <label>تا تاریخ:</label>

                    <input
                        type="text"
                        name="end_date"
                        id="endDate"
                        value="<?php echo htmlspecialchars($end_date); ?>"
                        placeholder="1403/12/29"
                        autocomplete="off">

                </div>

                <div class="form-group">

                    <label>جستجو (نام یا کدملی):</label>

                    <input
                        type="text"
                        name="search"
                        id="searchInput"
                        value="<?php echo htmlspecialchars($search); ?>"
                        placeholder="نام یا کد ملی...">

                </div>

                <div id="dateError" class="date-error">
                    تاریخ پایان نمی‌تواند قبل از تاریخ شروع باشد.
                </div>

                <br>

                <a href="admin_panel.php" class="btn-view-link">
                    بازگشت به پنل مدیریت
                </a>

            </form>

            <hr style="border:0; border-top:1px solid #eee; margin:20px 0;">

            <div class="cards-container" id="cardsContainer">

                <?php if (!$hasDateRange): ?>

                    <div class="default-message">
                        ابتدا بازه شروع و پایان جستجو را انتخاب کنید.
                    </div>

                <?php elseif ($result && $result->rowCount() > 0): ?>

                    <?php while ($row = $result->fetch(PDO::FETCH_ASSOC)): ?>

                        <div class="card">

                            <h4>
                                <?php echo htmlspecialchars($row['Stu_fullName']); ?>
                            </h4>

                            <p>
                                <b>کد ملی:</b>
                                <?php echo htmlspecialchars($row['Stu_nationalCode']); ?>
                            </p>

                            <p>
                                <b>کلاس:</b>
                                <?php echo htmlspecialchars("پایه " . $row['C_grade'] . " - " . $row['C_major']); ?>
                            </p>

                            <p>
                                <b>تاریخ غیبت:</b>
                                <?php echo htmlspecialchars($row['A_date']); ?>
                            </p>

                        </div>

                    <?php endwhile; ?>

                <?php else: ?>

                    <div class="no-record">
                        هیچ موردی برای نمایش یافت نشد.
                    </div>

                <?php endif; ?>

            </div>

        </div>

    </div>

    <script src="js/jquery-1.10.2.min.js"></script>

    <script src="https://unpkg.com/persian-date@1.1.0/dist/persian-date.min.js"></script>

    <script src="https://unpkg.com/persian-datepicker@1.2.0/dist/js/persian-datepicker.min.js"></script>

    <script src="https://unpkg.com/lenis@1.3.11/dist/lenis.min.js"></script>

    <script src="js/theme.js"></script>

    <script>

        $(document).ready(function () {

            var searchDebounceTimer;

            var startPicker;
            var endPicker;

            function showDefaultMessage() {

                $('#cardsContainer').html(
                    '<div class="default-message">ابتدا بازه شروع و پایان جستجو را انتخاب کنید.</div>'
                );

            }

            function sendAjaxSearch() {

                var startDate = $('#startDate').val().trim();
                var endDate = $('#endDate').val().trim();
                var search = $('#searchInput').val().trim();

                if (!startDate || !endDate) {

                    showDefaultMessage();

                    return;
                }

                if (startDate > endDate) {

                    $('#dateError').show();

                    return;
                }

                $('#dateError').hide();

                var formData = $('#searchForm').serialize();

                $.ajax({

                    url: window.location.pathname,

                    type: 'GET',

                    data: formData,

                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },

                    beforeSend: function () {

                        $('#cardsContainer').css(
                            'opacity',
                            '0.5'
                        );

                    },

                    success: function (response) {

                        $('#cardsContainer')
                            .html(response)
                            .css('opacity', '1');

                    },

                    error: function () {

                        $('#cardsContainer').css(
                            'opacity',
                            '1'
                        );

                    }

                });

            }

            startPicker = $('#startDate').persianDatepicker({

                format: 'YYYY/MM/DD',

                autoClose: true,

                calendar: {
                    persian: {
                        locale: 'fa'
                    }
                },

                onSelect: function (unix) {

                    if (endPicker) {

                        endPicker.setDate(unix);

                    }

                    var startDate =
                        $('#startDate').val();

                    var endDate =
                        $('#endDate').val();

                    if (
                        endDate &&
                        startDate > endDate
                    ) {

                        $('#endDate').val(startDate);

                    }

                    sendAjaxSearch();

                }

            });

            endPicker = $('#endDate').persianDatepicker({

                format: 'YYYY/MM/DD',

                autoClose: true,

                calendar: {
                    persian: {
                        locale: 'fa'
                    }
                },

                onSelect: function () {

                    var startDate =
                        $('#startDate').val();

                    var endDate =
                        $('#endDate').val();

                    if (
                        startDate &&
                        endDate &&
                        startDate > endDate
                    ) {

                        $('#dateError').show();

                        return;

                    }

                    $('#dateError').hide();

                    sendAjaxSearch();

                }

            });

            $('#searchInput').on(
                'input',
                function () {

                    clearTimeout(searchDebounceTimer);

                    searchDebounceTimer = setTimeout(
                        function () {

                            var startDate =
                                $('#startDate').val().trim();

                            var endDate =
                                $('#endDate').val().trim();

                            if (
                                !startDate ||
                                !endDate
                            ) {

                                showDefaultMessage();

                                return;

                            }

                            sendAjaxSearch();

                        },
                        300
                    );

                }
            );

            $('#startDate, #endDate').on(
                'change',
                function () {

                    var startDate =
                        $('#startDate').val().trim();

                    var endDate =
                        $('#endDate').val().trim();

                    if (
                        startDate &&
                        endDate
                    ) {

                        sendAjaxSearch();

                    } else {

                        showDefaultMessage();

                    }

                }
            );

        });

    </script>

</body>

</html>