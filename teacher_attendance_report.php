<?php
include("connect.php");

// دریافت فیلترهای تاریخ و جستجو از طریق متد GET
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// کوئری برای دریافت رکوردهای خام حضور و غیاب با مشخص کردن صحیح کلیدها
$sql = "SELECT 
            ta.AT_date,
            ta.AT_type,
            ta.AT_state,
            s.Stu_ID,
            s.Stu_fullName,
            s.Stu_nationalCode,
            c.C_grade,
            c.C_major,
            t.T_ID AS T_ID,
            t.T_fullName,
            co.Co_ID,
            co.Co_name,
            co.Co_type
        FROM teacher_attendance ta
        JOIN students s ON ta.AT_studentID = s.Stu_ID
        JOIN classes c ON s.Stu_classID = c.C_ID
        JOIN teachers t ON ta.AT_teacherID = t.T_ID
        JOIN courses co ON ta.AT_courseID = co.Co_ID
        WHERE 1=1";

// اعمال فیلتر بازه تاریخ شمسی
if (!empty($start_date) && !empty($end_date)) {
    $sql .= " AND ta.AT_date BETWEEN :start_date AND :end_date";
}

// اعمال فیلتر جستجو
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

// تفکیک و گروه‌بندی دقیق اطلاعات در آرایه PHP
$grouped_reports = [];
foreach ($raw_records as $row) {
    // استفاده ایمن از کلیدها با روش پیش‌فرض در صورت عدم وجود
    $t_id = isset($row['T_ID']) ? $row['T_ID'] : '';
    $key = $row['AT_date'] . '_' . $row['Stu_ID'] . '_' . $row['Co_ID'] . '_' . $t_id;

    if (!isset($grouped_reports[$key])) {
        $grouped_reports[$key] = [
            'Stu_fullName' => $row['Stu_fullName'],
            'Stu_nationalCode' => $row['Stu_nationalCode'],
            'C_grade' => $row['C_grade'],
            'C_major' => $row['C_major'],
            'T_fullName' => $row['T_fullName'],
            'Co_name' => $row['Co_name'],
            'Co_type' => $row['Co_type'],
            'AT_date' => $row['AT_date'],
            'first_state' => null,
            'last_state' => null
        ];
    }

    // تنظیم وضعیت اول زنگ (AT_type = 1)
    if ($row['AT_type'] == 1) {
        $grouped_reports[$key]['first_state'] = $row['AT_state'];
    } 
    // تنظیم وضعیت آخر زنگ (AT_type = 2 و فقط برای دروس پودمانی با Co_type = 1)
    elseif ($row['AT_type'] == 2 && $row['Co_type'] == 1) {
        $grouped_reports[$key]['last_state'] = $row['AT_state'];
    }
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
                    <label>جستجو (نام، کدملی، معلم یا درس):</label>
                    <input type="text" name="search" value="<?php echo $search; ?>" placeholder="جستجو...">
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
                if (!empty($grouped_reports)) {
                    foreach ($grouped_reports as $row) {
                        $name = $row['Stu_fullName'];
                        $national = $row['Stu_nationalCode'];
                        $class_name = "پایه " . $row['C_grade'] . " - " . $row['C_major'];
                        $teacher_name = $row['T_fullName'];
                        $course_name = $row['Co_name'];
                        $date = $row['AT_date'];
                        $co_type = $row['Co_type'];

                        echo '<div class="card">';
                        echo '<h4>' . $name . '</h4>';
                        echo '<p><b>کد ملی:</b> ' . $national . '</p>';
                        echo '<p><b>کلاس:</b> ' . $class_name . '</p>';
                        echo '<p><b>نام معلم:</b> ' . $teacher_name . '</p>';
                        echo '<p><b>نام درس:</b> ' . $course_name . '</p>';
                        echo '<p><b>تاریخ:</b> ' . $date . '</p>';

                        // قانون دقیق نمایش بر اساس نوع درس و مقادیر دیتابیس
                        if ($co_type == 1) {
                            // دروس پودمانی: نمایش دو وضعیت (اول و آخر زنگ)
                            // اگر AT_state برابر 0 باشد غایب و در غیر این صورت حاضر
                            $first_state_text = ($row['first_state'] !== null && $row['first_state'] == 0) ? 'غایب ❌' : 'حاضر ✅';
                            $last_state_text  = ($row['last_state'] !== null && $row['last_state'] == 0) ? 'غایب ❌' : 'حاضر ✅';

                            echo '<p><b>وضعیت اول زنگ:</b> ' . $first_state_text . '</p>';
                            echo '<p><b>وضعیت آخر زنگ:</b> ' . $last_state_text . '</p>';
                        } else {
                            // دروس غیرپودمانی: فقط و فقط یک وضعیت حضور (بر اساس AT_type = 1) و جلوگیری از نمایش بخش دوم
                            $attendance_state_text = ($row['first_state'] !== null && $row['first_state'] == 0) ? 'غایب ❌' : 'حاضر ✅';

                            echo '<p><b>وضعیت حضور:</b> ' . $attendance_state_text . '</p>';
                        }

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
