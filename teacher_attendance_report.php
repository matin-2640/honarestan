<?php
session_start();
if (!(isset($_SESSION["state_login"]) && ($_SESSION["type"] == 2 || $_SESSION["type"] == 3)) || $_SESSION["type"] == 4) {
    header("location:login.php");
    exit();
}
include("connect.php");

// دریافت فیلترهای تاریخ و جستجو از طریق متد GET
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// کوئری دریافت داده‌ها
$sql = "SELECT 
            ta.AT_date,
            ta.AT_type,
            ta.AT_state,
            s.Stu_ID,
            s.Stu_fullName,
            c.C_grade,
            c.C_major,
            t.T_ID AS T_ID,
            t.T_fullName,
            co.Co_ID,
            co.Co_name
        FROM teacher_attendance ta
        JOIN students s ON ta.AT_studentID = s.Stu_ID
        JOIN classes c ON s.Stu_classID = c.C_ID
        JOIN teachers t ON ta.AT_teacherID = t.T_ID
        JOIN courses co ON ta.AT_courseID = co.Co_ID
        WHERE 1=1";

if (!empty($start_date) && !empty($end_date)) {
    $sql .= " AND ta.AT_date BETWEEN :start_date AND :end_date";
}

if (!empty($search)) {
    $sql .= " AND (s.Stu_fullName LIKE :search OR s.Stu_nationalCode LIKE :search OR t.T_fullName LIKE :search OR co.Co_name LIKE :search)";
}

$sql .= " ORDER BY ta.AT_date DESC";

$stmt = $connect->prepare($sql);

if (!empty($start_date) && !empty($end_date)) {
    $stmt->bindValue(':start_date', $start_date);
    $stmt->bindValue(':end_date', $end_date);
}
if (!empty($search)) {
    $search_param = '%' . $search . '%';
    $stmt->bindValue(':search', $search_param);
}

$stmt->execute();
$raw_records = $stmt->fetchAll(PDO::FETCH_ASSOC);

// تابع رندر کردن کارت‌ها جهت استفاده همزمان در AJAX و بارگذاری اولیه
function renderCards($records)
{
    if (!empty($records)) {
        foreach ($records as $row) {
            $name = $row['Stu_fullName'];
            $class_name = "پایه " . $row['C_grade'] . " - " . $row['C_major'];
            $course_name = $row['Co_name'];
            $teacher_name = $row['T_fullName'];
            $date = $row['AT_date'];
            $at_type = $row['AT_type'];

            echo '<div class="card">';
            echo '<h4>' . htmlspecialchars($name) . '</h4>';
            echo '<p><b>کلاس:</b> ' . htmlspecialchars($class_name) . '</p>';
            echo '<p><b>نام درس:</b> ' . htmlspecialchars($course_name) . '</p>';
            echo '<p><b>نام معلم:</b> ' . htmlspecialchars($teacher_name) . '</p>';
            echo '<p><b>تاریخ:</b> ' . htmlspecialchars($date) . '</p>';

            if ($at_type == 2) {
                echo '<div class="late-badge">آخر زنگ</div>';
            }

            echo '</div>';
        }
    } else {
        echo '<div class="no-record">هیچ موردی برای نمایش یافت نشد.</div>';
    }
}

// در صورتی که درخواست از طریق AJAX ارسال شده باشد فقط بخش کارت‌ها برگردانده شود
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    renderCards($raw_records);
    exit();
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>گزارش حضور و غیاب معلم</title>
    <link rel="stylesheet" href="styles/font.css">
    <link rel="stylesheet" href="styles/panel_style.css" />
    <link rel="stylesheet" href="styles/profile_style.css" />
    <link rel="stylesheet" href="styles/attendance_reports.css">
    <link rel="icon" href="images/icons/rahdanesh.png">
    <link rel="stylesheet" href="https://unpkg.com/persian-datepicker@1.2.0/dist/css/persian-datepicker.min.css">
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

        .late-badge {
            display: inline-block;
            background-color: #ef4444;
            color: #ffffff !important;
            padding: 4px 12px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: bold;
            margin-top: 8px;
            text-align: center;
        }

        .date-error {
            color: #ef4444;
            font-size: 13px;
            margin-top: 8px;
            display: none;
            font-weight: bold;
        }
    </style>
</head>

<body>

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
                    <small>گزارش حضور و غیاب معلم</small>
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
                    گزارش حضور و غیاب معلم
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
                    <img src="images/icons/theme.png" width="25px" height="25px" />
                </button>
            </div>
        </div>
    </header>

    <div class="page-container">
        <h2>گزارش حضور و غیاب معلم</h2>

        <div class="container">
            <form id="filterForm">
                <div class="form-group">
                    <label>از تاریخ:</label>
                    <input type="text" name="start_date" id="startDate" value="<?php echo $start_date; ?>"
                        placeholder="1403/01/01" autocomplete="off">
                </div>

                <div class="form-group">
                    <label>تا تاریخ:</label>
                    <input type="text" name="end_date" id="endDate" value="<?php echo $end_date; ?>"
                        placeholder="1403/12/29" autocomplete="off">
                </div>

                <div class="form-group">
                    <label>جستجو (نام، کدملی، معلم یا درس):</label>
                    <input type="text" name="search" id="searchInput" value="<?php echo $search; ?>"
                        placeholder="عبارت مورد نظر را تایپ کنید...">
                </div>

                <div id="dateError" class="date-error">تاریخ پایان نباید قبل از تاریخ شروع باشد.</div>

                <br>
                <a href="admin_panel.php" id="smsParentBtn" class="btn-view-link">
                    بازگشت به پنل مدیریت
                </a>
            </form>

            <hr style="border:0; border-top:1px solid #eee; margin: 20px 0;">

            <div class="cards-container" id="cardsContainer">
                <?php renderCards($raw_records); ?>
            </div>

        </div>
    </div>

    <script src="js/jquery-1.10.2.min.js"></script>
    <script src="https://unpkg.com/persian-date@1.1.0/dist/persian-date.min.js"></script>
    <script src="https://unpkg.com/persian-datepicker@1.2.0/dist/js/persian-datepicker.min.js"></script>
    <script src="https://unpkg.com/lenis@1.3.11/dist/lenis.min.js"></script>
    <script type="text/javascript" src="js/theme.js"></script>

    <script>
        $(document).ready(function () {
            var searchTimeout;

            // تابع ارسال AJAX
            function fetchFilteredData() {
                var startDate = $('#startDate').val();
                var endDate = $('#endDate').val();

                // بررسی برابری یا بزرگتر بودن تاریخ پایان از تاریخ شروع
                if (startDate && endDate && startDate > endDate) {
                    $('#dateError').show();
                    return;
                } else {
                    $('#dateError').hide();
                }

                var formData = $('#filterForm').serialize();

                $.ajax({
                    url: window.location.pathname,
                    type: 'GET',
                    data: formData,
                    beforeSend: function () {
                        $('#cardsContainer').css('opacity', '0.5');
                    },
                    success: function (response) {
                        $('#cardsContainer').html(response).css('opacity', '1');
                    },
                    error: function () {
                        $('#cardsContainer').css('opacity', '1');
                    }
                });
            }

            // راه‌اندازی تاریخ‌پیکر پایان
            var endDatePicker = $('#endDate').persianDatepicker({
                format: 'YYYY/MM/DD',
                autoClose: true,
                calendar: {
                    persian: { locale: 'fa' }
                },
                onSelect: function () {
                    fetchFilteredData();
                }
            });

            // راه‌اندازی تاریخ‌پیکر شروع و محدودسازی تاریخ پایان
            $('#startDate').persianDatepicker({
                format: 'YYYY/MM/DD',
                autoClose: true,
                calendar: {
                    persian: { locale: 'fa' }
                },
                onSelect: function (unix) {
                    endDatePicker.setDate(unix);
                    fetchFilteredData();
                }
            });

            // اجرای فیلتر آنی هنگام تایپ در ورودی جستجو (با تاخیر ۳۰۰ میلی‌ثانیه‌ای جهت بهینه‌سازی)
            $('#searchInput').on('input', function () {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function () {
                    fetchFilteredData();
                }, 300);
            });

            // دستیابی به تغییرات احتمالی دستی در تاریخ‌ها
            $('#startDate, #endDate').on('change', function () {
                fetchFilteredData();
            });
        });
    </script>

</body>

</html>