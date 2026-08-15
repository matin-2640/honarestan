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
            <style>
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
                    font-size: 12px;
                    margin-top: 10px;
                    max-width: 160px;
                }
            </style>
            <a href="../teacher_panel.php" id="smsParentBtn" class="btn-view-link">
                بازگشت به پنل مدیر
            </a>
        </form>
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