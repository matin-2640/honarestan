<?php
include("connect.php");

// دریافت فیلترهای تاریخ و جستجو از طریق متد GET
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// کوئری پایه برای گرفتن غیبت‌ها (بر اساس جدول attendance و ساختار ستون‌های شما)
// فرض بر این است که A_state یا A_type نشان‌دهنده غیبت است (مثلاً مقدار 0)
$sql = "SELECT ar.*, s.Stu_fullName, s.Stu_nationalCode, c.C_grade, c.C_major 
        FROM attendance ar
        JOIN students s ON ar.A_studentID = s.Stu_ID
        JOIN classes c ON s.Stu_classID = c.C_ID
        WHERE (ar.A_state = '0' OR ar.A_type = '0')";

// اعمال فیلتر بازه تاریخ شمسی
if (!empty($start_date) && !empty($end_date)) {
    $sql .= " AND ar.A_date BETWEEN '$start_date' AND '$end_date'";
}

// اعمال فیلتر جستجو (نام یا کد ملی)
if (!empty($search)) {
    $sql .= " AND (s.Stu_fullName LIKE '%$search%' OR s.Stu_nationalCode LIKE '%$search%')";
}

$sql .= " ORDER BY ar.A_date DESC";

$result = $connect->query($sql);
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>گزارش غیبت‌های دانش‌آموزان</title>
    <link rel="stylesheet" href="styles/font.css">
    <link rel="stylesheet" href="styles/panel_style.css" />
    <link rel="stylesheet" href="styles/profile_style.css" />
    <link rel="stylesheet" href="styles/attendance_reports.css">
    <link rel="icon" href="images/icons/rahdanesh.png">
    <link rel="stylesheet" href="https://unpkg.com/persian-datepicker@1.2.0/dist/css/persian-datepicker.min.css">
    <style>
        /* استایل‌های دارک‌مود برای تمامی المان‌های صفحه و بزرگ‌سازی کادرها */
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
                    <small>گزارش غیبت‌ها</small>
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
                    گزارش غیبت‌ها
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
        <h2>گزارش غیبت‌های دانش‌آموزان</h2>

        <div class="container">
            <form method="GET" action="">
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
                    <label>جستجو (نام یا کدملی):</label>
                    <input type="text" name="search" value="<?php echo $search; ?>" placeholder="نام یا کد ملی...">
                </div>

                <div class="form-group" style="vertical-align: bottom;">
                    <button type="submit" class="btn">جستجو و فیلتر</button>
                </div>

                <br>
                <a href="admin_panel.php" id="smsParentBtn" class="btn-view-link">
                    بازگشت به پنل مدیریت
                </a>
            </form>

            <hr style="border:0; border-top:1px solid #eee; margin: 20px 0;">

            <div class="cards-container">
                <?php
                if ($result && $result->rowCount() > 0) {
                    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                        $name = $row['Stu_fullName'];
                        $national = $row['Stu_nationalCode'];
                        $class_name = "پایه " . $row['C_grade'] . " - " . $row['C_major'];
                        $date = $row['A_date'];

                        echo '<div class="card">';
                        echo '<h4>' . $name . '</h4>';
                        echo '<p><b>کد ملی:</b> ' . $national . '</p>';
                        echo '<p><b>کلاس:</b> ' . $class_name . '</p>';
                        echo '<p><b>تاریخ غیبت:</b> ' . $date . '</p>';
                        echo '</div>';
                    }
                } else {
                    echo '<div class="no-record">هیچ موردی برای نمایش یافت نشد.</div>';
                }
                ?>
            </div>

        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://unpkg.com/persian-date@1.1.0/dist/persian-date.min.js"></script>
    <script src="https://unpkg.com/persian-datepicker@1.2.0/dist/js/persian-datepicker.min.js"></script>
    <script src="https://unpkg.com/lenis@1.3.11/dist/lenis.min.js"></script>
    <script type="text/javascript" src="js/theme.js"></script>

    <script>
        $(document).ready(function () {
            // فعال‌سازی تقویم شمسی برای فیلتر بازه زمانی
            $('#startDate, #endDate').persianDatepicker({
                format: 'YYYY/MM/DD',
                autoClose: true,
                calendar: {
                    persian: {
                        locale: 'fa'
                    }
                }
            });
        });
    </script>

</body>

</html>