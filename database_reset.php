<?php
session_start();

if (!(isset($_SESSION["state_login"]) && $_SESSION["type"] == 2 || $_SESSION["type"] == 3) || $_SESSION["type"] == 4) {
    header("location:login.php");
    exit();
}


require_once 'connect.php';

$resetGroups = [
    'courses' => [
        'title' => 'دروس',
        'tables' => ['courses']
    ],
    'classes' => [
        'title' => 'کلاس‌ها',
        'tables' => ['classes']
    ],
    'students' => [
        'title' => 'هنرجویان',
        'tables' => ['students']
    ],
    'teachers' => [
        'title' => 'معلم‌ها',
        'tables' => ['teachers']
    ],
    'notes' => [
        'title' => 'یادداشت‌ها',
        'tables' => ['notes']
    ],
    'assignments' => [
        'title' => 'تکالیف',
        'tables' => ['assignments']
    ],
    'attendance' => [
        'title' => 'حضور و غیاب‌ها',
        'tables' => ['attendance', 'teacher_attendance']
    ],
    'disciplinary' => [
        'title' => 'پرونده‌های انضباطی',
        'tables' => ['disciplinary_records', 'teacher_disciplinary']
    ],
    'gallery' => [
        'title' => 'گالری تصاویر',
        'tables' => ['gallery_albums', 'gallery_images']
    ],
    'news' => [
        'title' => 'اخبار هنرستان',
        'tables' => ['news']
    ],
    'certificate' => [
        'title' => 'لوح تقدیرها',
        'tables' => ['certificate']
    ],
    'grades' => [
        'title' => 'کارنامه‌ها',
        'tables' => ['grades', 'report_license']
    ],
    'messages' => [
        'title' => 'پیام‌های درون گروه‌های کلاسی',
        'tables' => [
            'messages',
            'message_audios',
            'message_files',
            'Voice_Signals',
            'Voice_Participants',
            'Voice_Rooms'
        ]
    ]
];

function generateCaptcha()
{
    $a = random_int(1, 20);
    $b = random_int(1, 20);
    $_SESSION['reset_captcha_answer'] = $a + $b;
    return "$a + $b";
}

function getRealTableNames(PDO $connect, array $wantedTables)
{
    $existingTables = [];
    $stmt = $connect->query("SHOW TABLES");

    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        $existingTables[strtolower($row[0])] = $row[0];
    }

    $result = [];

    foreach ($wantedTables as $wanted) {
        $key = strtolower($wanted);

        if (isset($existingTables[$key])) {
            $result[] = $existingTables[$key];
        }
    }

    return $result;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    $foreignKeysDisabled = false;

    try {
        $selected = $_POST['tables'] ?? [];
        $captchaAnswer = trim($_POST['captcha_answer'] ?? '');

        if (!is_array($selected) || empty($selected)) {
            throw new Exception('حداقل یک مورد را انتخاب کنید.');
        }

        if (
            !isset($_SESSION['reset_captcha_answer']) ||
            $captchaAnswer === '' ||
            (int) $captchaAnswer !== (int) $_SESSION['reset_captcha_answer']
        ) {
            throw new Exception('پاسخ کپچا صحیح نیست.');
        }

        unset($_SESSION['reset_captcha_answer']);

        $allTablesToDelete = [];

        foreach ($selected as $groupKey) {
            if (!isset($resetGroups[$groupKey])) {
                throw new Exception('گزینه نامعتبر ارسال شده است.');
            }

            foreach ($resetGroups[$groupKey]['tables'] as $table) {
                $allTablesToDelete[] = $table;
            }
        }

        $allTablesToDelete = array_unique($allTablesToDelete);
        $realTables = getRealTableNames($connect, $allTablesToDelete);

        if (empty($realTables)) {
            throw new Exception('هیچ‌کدام از جدول‌های انتخاب‌شده در دیتابیس پیدا نشد.');
        }

        $connect->exec("SET FOREIGN_KEY_CHECKS = 0");
        $foreignKeysDisabled = true;

        $deletedTables = [];

        foreach ($realTables as $tableName) {
            $safeTableName = '`' . str_replace('`', '``', $tableName) . '`';

            $connect->exec("DELETE FROM $safeTableName");
            $connect->exec("ALTER TABLE $safeTableName AUTO_INCREMENT = 1");

            $deletedTables[] = $tableName;
        }

        $connect->exec("SET FOREIGN_KEY_CHECKS = 1");
        $foreignKeysDisabled = false;

        echo json_encode([
            'success' => true,
            'message' => 'اطلاعات جدول‌های انتخاب‌شده با موفقیت پاک شد.',
            'tables' => $deletedTables
        ], JSON_UNESCAPED_UNICODE);

        exit;

    } catch (Throwable $e) {
        if ($foreignKeysDisabled) {
            try {
                $connect->exec("SET FOREIGN_KEY_CHECKS = 1");
            } catch (Throwable $ignore) {
            }
        }

        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }
}

$captchaQuestion = generateCaptcha();
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ریست دیتابیس هنرستان</title>
    <link rel="icon" href="images/icons/rahdanesh.png">
    <link rel="stylesheet" href="js/sweetalert2.min.css">
    <link rel="stylesheet" href="styles/font.css">
    <script src="js/sweetalert2.min.js"></script>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: Tahoma, Arial, sans-serif;
            background: #f3f4f6;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 25px;
        }

        .reset-container {
            width: 100%;
            max-width: 750px;
            background: #fff;
            border-radius: 18px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, .08);
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            margin: 0 0 10px;
            font-size: 25px;
            color: #1f2937;
        }

        .header p {
            margin: 0;
            color: #6b7280;
            font-size: 14px;
            line-height: 1.8;
        }

        .warning {
            background: #fff7ed;
            border: 1px solid #fed7aa;
            color: #9a3412;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 25px;
            line-height: 1.9;
            font-size: 14px;
        }

        .groups {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }

        .group {
            position: relative;
        }

        .group input {
            position: absolute;
            opacity: 0;
        }

        .group label {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 15px;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            cursor: pointer;
            transition: .2s;
            background: #fff;
            color: #374151;
        }

        .group label:hover {
            border-color: #ef4444;
            background: #fffafa;
        }

        .check {
            width: 21px;
            height: 21px;
            border: 2px solid #d1d5db;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .check::after {
            content: "✓";
            color: white;
            font-size: 14px;
            display: none;
        }

        .group input:checked+label {
            border-color: #ef4444;
            background: #fef2f2;
            color: #991b1b;
        }

        .group input:checked+label .check {
            background: #ef4444;
            border-color: #ef4444;
        }

        .group input:checked+label .check::after {
            display: block;
        }

        .delete-btn {
            width: 100%;
            border: 0;
            border-radius: 12px;
            padding: 15px;
            margin-top: 25px;
            background: #dc2626;
            color: #fff;
            font-size: 16px;
            font-family: inherit;
            cursor: pointer;
            transition: .2s;
        }

        .delete-btn:hover {
            background: #b91c1c;
        }

        .delete-btn:disabled {
            opacity: .6;
            cursor: not-allowed;
        }

        @media (max-width: 600px) {
            .groups {
                grid-template-columns: 1fr;
            }

            .reset-container {
                padding: 20px;
            }
        }
    </style>
</head>

<body>
    <div class="reset-container">
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
                            <a href="send_sms.php"><img src="images/icons/sendsms.png" width="20"
                                    height="20" /><span>ارسال
                                    پیام</span></a>
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
                            <a href="database_reset.php" class="ram-active"><img src="images/icons/reset.png" width="20"
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
        <div class="header">
            <h1>ریست دیتابیس هنرستان</h1>
            <p>اطلاعات بخش‌هایی که انتخاب می‌کنید به صورت کامل حذف خواهد شد.</p>
        </div>

        <div class="warning">
            <strong>⚠️ هشدار:</strong>
            این عملیات قابل بازگشت نیست. قبل از انجام ریست، حتماً از دیتابیس نسخه پشتیبان تهیه کنید.
        </div>

        <form id="resetForm">
            <div class="groups">
                <?php foreach ($resetGroups as $key => $group): ?>
                    <div class="group">
                        <input type="checkbox" id="group_<?php echo htmlspecialchars($key); ?>" name="tables[]"
                            value="<?php echo htmlspecialchars($key); ?>">
                        <label for="group_<?php echo htmlspecialchars($key); ?>">
                            <span class="check"></span>
                            <span><?php echo htmlspecialchars($group['title']); ?></span>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>

            <button type="submit" class="delete-btn" id="deleteBtn">
                حذف اطلاعات انتخاب‌شده
            </button>
        </form>
                    <a href="admin_panel.php">
                <button type="submit" class="delete-btn" style="background-color:blue;">
                    بازگشت به پنل مدیر
                </button>
            </a>
    </div>

    <script>
        const form = document.getElementById('resetForm');
        const deleteBtn = document.getElementById('deleteBtn');

        form.addEventListener('submit', async function (e) {
            e.preventDefault();

            const selected = document.querySelectorAll('input[name="tables[]"]:checked');

            if (selected.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'هیچ گزینه‌ای انتخاب نشده',
                    text: 'حداقل یک بخش را برای ریست انتخاب کنید.',
                    confirmButtonText: 'باشه'
                });
                return;
            }

            const result = await Swal.fire({
                icon: 'warning',
                title: 'مطمئنی؟',
                text: 'اطلاعات بخش‌های انتخاب‌شده برای همیشه حذف می‌شوند و قابل بازگشت نیستند.',
                showCancelButton: true,
                confirmButtonText: 'بله، حذف کن',
                cancelButtonText: 'انصراف',
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                reverseButtons: true
            });

            if (!result.isConfirmed) {
                return;
            }

            const captchaResult = await Swal.fire({
                title: 'تأیید نهایی',
                html: `
            <div style="font-size:14px;margin-bottom:15px;">
                برای تأیید حذف، حاصل عبارت زیر را وارد کنید:
            </div>
            <div style="font-size:26px;font-weight:bold;direction:ltr;margin-bottom:15px;">
                <?php echo htmlspecialchars($captchaQuestion); ?>
            </div>
        `,
                input: 'text',
                inputPlaceholder: 'جواب را وارد کنید',
                inputAttributes: {
                    autocomplete: 'off',
                    inputmode: 'numeric'
                },
                showCancelButton: true,
                confirmButtonText: 'تأیید و حذف',
                cancelButtonText: 'انصراف',
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                reverseButtons: true,
                inputValidator: (value) => {
                    if (!value || value.trim() === '') {
                        return 'جواب کپچا را وارد کنید.';
                    }

                    if (!/^\d+$/.test(value.trim())) {
                        return 'لطفاً فقط عدد وارد کنید.';
                    }
                }
            });

            if (!captchaResult.isConfirmed) {
                return;
            }

            deleteBtn.disabled = true;
            deleteBtn.textContent = 'در حال حذف...';

            const formData = new FormData(form);
            formData.append('captcha_answer', captchaResult.value);

            try {
                const response = await fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    await Swal.fire({
                        icon: 'success',
                        title: 'انجام شد',
                        text: data.message,
                        confirmButtonText: 'باشه'
                    });

                    form.reset();
                } else {
                    await Swal.fire({
                        icon: 'error',
                        title: 'عملیات انجام نشد',
                        text: data.message,
                        confirmButtonText: 'باشه'
                    });
                }
            } catch (error) {
                await Swal.fire({
                    icon: 'error',
                    title: 'خطا',
                    text: 'در ارتباط با سرور مشکلی به وجود آمد.',
                    confirmButtonText: 'باشه'
                });
            } finally {
                deleteBtn.disabled = false;
                deleteBtn.textContent = 'حذف اطلاعات انتخاب‌شده';
            }
        });
    </script>
</body>

</html>