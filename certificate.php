<?php
require_once 'connect.php';
session_start();
if (!(isset($_SESSION["state_login"]) && $_SESSION["type"] == 2 || $_SESSION["type"] == 3) || $_SESSION["type"] == 4) {
    header("location:login.php");
    exit();
}
if (!isset($connect) || !($connect instanceof PDO)) {
    die('خطا: اتصال PDO در فایل connect.php پیدا نشد.');
}
$connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$connect->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

if (
    isset($_GET['action']) &&
    $_GET['action'] === 'get_students'
) {
    header('Content-Type: application/json; charset=utf-8');

    try {
        $classId = (int) ($_GET['class_id'] ?? 0);
        if ($classId <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'کلاس انتخاب‌شده معتبر نیست.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $stmt = $connect->prepare("
            SELECT
                Stu_ID,
                Stu_fullName
            FROM students
            WHERE Stu_classID = :class_id
            ORDER BY Stu_fullName ASC
        ");
        $stmt->execute([
            ':class_id' => $classId
        ]);
        $students = $stmt->fetchAll();

        echo json_encode([
            'success' => true,
            'students' => $students
        ], JSON_UNESCAPED_UNICODE);

    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'خطا در دریافت اطلاعات دانش‌آموزان.'
        ], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    ($_POST['action'] ?? '') === 'add'
) {

    header('Content-Type: application/json; charset=utf-8');
    try {
        $studentId = (int) ($_POST['student_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $type = (int) ($_POST['type'] ?? 0);
        if ($studentId <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'لطفاً دانش‌آموز را انتخاب کنید.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }


        if ($title === '') {
            echo json_encode([
                'success' => false,
                'message' => 'لطفاً عنوان لوح را وارد کنید.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }


        if (!in_array($type, [1, 2, 3], true)) {
            echo json_encode([
                'success' => false,
                'message' => 'نوع لوح انتخاب‌شده معتبر نیست.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $checkStudent = $connect->prepare("
            SELECT Stu_ID
            FROM students
            WHERE Stu_ID = :student_id
            LIMIT 1
        ");
        $checkStudent->execute([
            ':student_id' => $studentId
        ]);
        if (!$checkStudent->fetch()) {
            echo json_encode([
                'success' => false,
                'message' => 'دانش‌آموز انتخاب‌شده پیدا نشد.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $stmt = $connect->prepare("
            INSERT INTO certificate
            (
                title,
                description,
                type,
                student_ID
            )
            VALUES
            (
                :title,
                :description,
                :type,
                :student_id
            )
        ");

        $stmt->execute([
            ':title' => $title,
            ':description' => $description,
            ':type' => $type,
            ':student_id' => $studentId
        ]);

        echo json_encode([
            'success' => true,
            'message' => 'لوح تقدیر با موفقیت ثبت شد.'
        ], JSON_UNESCAPED_UNICODE);

    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'خطایی هنگام ثبت لوح تقدیر رخ داد.'
        ], JSON_UNESCAPED_UNICODE);
    }
    exit;
}



if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    ($_POST['action'] ?? '') === 'edit'
) {
    header('Content-Type: application/json; charset=utf-8');
    try {
        $certificateId = (int) ($_POST['certificate_id'] ?? 0);
        $studentId = (int) ($_POST['student_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $type = (int) ($_POST['type'] ?? 0);
        if ($certificateId <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'شناسه لوح معتبر نیست.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if ($studentId <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'لطفاً دانش‌آموز را انتخاب کنید.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if ($title === '') {
            echo json_encode([
                'success' => false,
                'message' => 'لطفاً عنوان لوح را وارد کنید.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if (!in_array($type, [1, 2, 3], true)) {
            echo json_encode([
                'success' => false,
                'message' => 'نوع لوح انتخاب‌شده معتبر نیست.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $checkCertificate = $connect->prepare("
            SELECT ID
            FROM certificate
            WHERE ID = :id
            LIMIT 1
        ");

        $checkCertificate->execute([
            ':id' => $certificateId
        ]);

        if (!$checkCertificate->fetch()) {
            echo json_encode([
                'success' => false,
                'message' => 'لوح موردنظر پیدا نشد.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $stmt = $connect->prepare("
            UPDATE certificate
            SET
                title = :title,
                description = :description,
                type = :type,
                student_ID = :student_id
            WHERE ID = :id
        ");

        $stmt->execute([
            ':title' => $title,
            ':description' => $description,
            ':type' => $type,
            ':student_id' => $studentId,
            ':id' => $certificateId
        ]);

        echo json_encode([
            'success' => true,
            'message' => 'لوح تقدیر با موفقیت ویرایش شد.'
        ], JSON_UNESCAPED_UNICODE);

    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'خطایی هنگام ویرایش لوح تقدیر رخ داد.'
        ], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    ($_POST['action'] ?? '') === 'delete'
) {
    header('Content-Type: application/json; charset=utf-8');
    try {
        $certificateId = (int) ($_POST['id'] ?? 0);
        if ($certificateId <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'شناسه لوح معتبر نیست.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $stmt = $connect->prepare("
            DELETE FROM certificate
            WHERE ID = :id
        ");

        $stmt->execute([
            ':id' => $certificateId
        ]);

        if ($stmt->rowCount() === 0) {
            echo json_encode([
                'success' => false,
                'message' => 'لوح موردنظر پیدا نشد یا قبلاً حذف شده است.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        echo json_encode([
            'success' => true,
            'message' => 'لوح تقدیر با موفقیت حذف شد.'
        ], JSON_UNESCAPED_UNICODE);

    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'خطایی هنگام حذف لوح تقدیر رخ داد.'
        ], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

try {
    $stmt = $connect->query("
        SELECT
            C_ID,
            C_grade,
            C_major
        FROM classes
        ORDER BY C_grade ASC, C_major ASC
    ");
    $classes = $stmt->fetchAll();
} catch (PDOException $e) {
    $classes = [];
}

try {
    $stmt = $connect->query("
        SELECT
            c.ID,
            c.title,
            c.description,
            c.type,
            c.student_ID,

            s.Stu_fullName,
            s.Stu_classID,

            cl.C_grade,
            cl.C_major

        FROM certificate AS c

        INNER JOIN students AS s
            ON c.student_ID = s.Stu_ID

        LEFT JOIN classes AS cl
            ON s.Stu_classID = cl.C_ID

        ORDER BY c.ID DESC
    ");

    $certificates = $stmt->fetchAll();
} catch (PDOException $e) {
    $certificates = [];
}

function certificateType($type)
{
    switch ((int) $type) {
        case 1:
            return 'آموزشی';
        case 2:
            return 'ورزشی';
        case 3:
            return 'فرهنگ و هنری';
        default:
            return 'نامشخص';
    }
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مدیریت لوح‌های تقدیر</title>
    <link rel="icon" href="images/icons/rahdanesh.png">
    <link rel="stylesheet" href="js/sweetalert2.min.css">
    <script src="js/sweetalert2.min.js"></script>
    <link rel="stylesheet" href="styles/font.css">
    <link rel="stylesheet" href="styles/certificate.css">
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
                        <a href="send_sms.php"><img src="images/icons/sendsms.png" width="20" height="20" /><span>ارسال
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
                        <a href="certificate.php" class="ram-active"><img src="images/icons/manageroles.png" width="20"
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
    <div class="container">
        <div class="page-header">
            <div>
                <h1>مدیریت لوح‌های تقدیر</h1>
                <p>ثبت، ویرایش و مدیریت لوح‌های تقدیر دانش‌آموزان</p>
            </div>
            <a href="admin_panel.php" class="btn-back">بازگشت به صفحه اصلی</a>
        </div>

        <div class="form-box">
            <h2 id="formTitle">افزودن لوح تقدیر جدید</h2>
            <form id="certificateForm">
                <input type="hidden" name="certificate_id" id="certificate_id" value="">
                <div class="form-group">
                    <label for="class_id">کلاس</label>
                    <select name="class_id" id="class_id" required>
                        <option value="">انتخاب کلاس</option>

                        <?php foreach ($classes as $class): ?>
                            <option value="<?= (int) $class['C_ID'] ?>">
                                <?= htmlspecialchars(
                                    $class['C_grade'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>

                                -

                                <?= htmlspecialchars(
                                    $class['C_major'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>

                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="student_id">دانش‌آموز</label>
                    <select name="student_id" id="student_id" required disabled>
                        <option value="">ابتدا کلاس را انتخاب کنید</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="type">نوع لوح</label>
                    <select name="type" id="type" required>
                        <option value="">انتخاب نوع لوح</option>
                        <option value="1">آموزشی</option>
                        <option value="2">ورزشی</option>
                        <option value="3">فرهنگ و هنری</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="title">عنوان لوح</label>
                    <input type="text" name="title" id="title" maxlength="255"
                        placeholder="عنوان لوح تقدیر را وارد کنید" required>
                </div>
                <div class="form-group">
                    <label for="description">توضیحات</label>
                    <textarea name="description" id="description"
                        placeholder="توضیحات لوح تقدیر را وارد کنید..."></textarea>
                </div>
                <button type="submit" class="btn-primary" id="submitButton">ثبت لوح تقدیر</button>
                <button type="button" id="cancelEditButton" class="hidden">انصراف از ویرایش</button>
            </form>
        </div>
        <section>
            <h2>لوح‌های ثبت‌شده</h2>
            <?php if (empty($certificates)): ?>
                <div class="empty-state">هنوز هیچ لوح تقدیری ثبت نشده است.</div>
            <?php else: ?>
                <div class="certificates-grid">
                    <?php foreach ($certificates as $certificate): ?>
                        <article class="certificate-card">
                            <h3>
                                <?= htmlspecialchars(
                                    $certificate['title'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </h3>
                            <p>
                                <strong>دانش‌آموز:</strong>
                                <?= htmlspecialchars(
                                    $certificate['Stu_fullName'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </p>
                            <p>
                                <strong>کلاس:</strong>
                                <?= htmlspecialchars(
                                    $certificate['C_grade'] ?? '-',
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>

                                -

                                <?= htmlspecialchars(
                                    $certificate['C_major'] ?? '-',
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>

                            </p>
                            <p>
                                <strong>نوع:</strong>
                                <?= htmlspecialchars(
                                    certificateType(
                                        $certificate['type']
                                    ),
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </p>
                            <?php if (
                                !empty($certificate['description'])
                            ): ?>
                                <p>
                                    <strong>توضیحات:</strong>
                                    <?= nl2br(
                                        htmlspecialchars(
                                            $certificate['description'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        )
                                    ) ?>
                                </p>
                            <?php endif; ?>
                            <div class="card-actions">

                                <button type="button" class="btn-edit edit-certificate"
                                    data-id="<?= (int) $certificate['ID'] ?>"
                                    data-student="<?= (int) $certificate['student_ID'] ?>"
                                    data-class="<?= (int) $certificate['Stu_classID'] ?>"
                                    data-type="<?= (int) $certificate['type'] ?>" data-title="<?= htmlspecialchars(
                                           $certificate['title'],
                                           ENT_QUOTES,
                                           'UTF-8'
                                       ) ?>" data-description="<?= htmlspecialchars(
                                            $certificate['description'] ?? '',
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>">
                                    ویرایش
                                </button>
                                <button type="button" class="btn-delete delete-certificate"
                                    data-id="<?= (int) $certificate['ID'] ?>">
                                    حذف
                                </button>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>
    <script>
        const form =
            document.getElementById('certificateForm');
        const classSelect =
            document.getElementById('class_id');
        const studentSelect =
            document.getElementById('student_id');
        const typeSelect =
            document.getElementById('type');
        const titleInput =
            document.getElementById('title');
        const descriptionInput =
            document.getElementById('description');
        const certificateIdInput =
            document.getElementById('certificate_id');
        const submitButton =
            document.getElementById('submitButton');
        const cancelEditButton =
            document.getElementById('cancelEditButton');
        const formTitle =
            document.getElementById('formTitle');

        async function loadStudents(
            classId,
            selectedStudentId = null
        ) {
            if (!classId) {
                studentSelect.innerHTML = `
            <option value="">
                ابتدا کلاس را انتخاب کنید
            </option>
        `;
                studentSelect.disabled = true;
                return;
            }
            studentSelect.disabled = true;
            studentSelect.innerHTML = `
        <option value="">
            در حال دریافت دانش‌آموزان...
        </option>
    `;

            try {
                const response = await fetch(
                    '?action=get_students&class_id=' +
                    encodeURIComponent(classId)
                );
                const data =
                    await response.json();

                if (!data.success) {
                    throw new Error(
                        data.message ||
                        'خطا در دریافت اطلاعات.'
                    );
                }

                studentSelect.innerHTML = `
            <option value="">
                انتخاب دانش‌آموز
            </option>
        `;

                if (
                    !data.students ||
                    data.students.length === 0
                ) {
                    studentSelect.innerHTML = `
                <option value="">
                    دانش‌آموزی در این کلاس وجود ندارد
                </option>
            `;
                    return;
                }

                data.students.forEach(student => {
                    const option =
                        document.createElement('option');
                    option.value =
                        student.Stu_ID;
                    option.textContent =
                        student.Stu_fullName;

                    if (
                        selectedStudentId !== null &&
                        String(student.Stu_ID) ===
                        String(selectedStudentId)
                    ) {
                        option.selected = true;
                    }
                    studentSelect.appendChild(option);
                });
                studentSelect.disabled = false;

            } catch (error) {
                console.error(error);
                studentSelect.innerHTML = `
            <option value="">
                خطا در دریافت اطلاعات
            </option>
        `;
                Swal.fire({
                    icon: 'error',
                    title: 'خطا',
                    text:
                        error.message ||
                        'ارتباط با سرور برقرار نشد.',
                    confirmButtonText: 'باشه'
                });
            }
        }

        classSelect.addEventListener(
            'change',
            function () {
                loadStudents(this.value);
            }
        );

        form.addEventListener(
            'submit',
            async function (event) {
                event.preventDefault();
                const certificateId =
                    certificateIdInput.value.trim();
                const action =
                    certificateId
                        ? 'edit'
                        : 'add';
                const formData =
                    new FormData(form);
                formData.append(
                    'action',
                    action
                );
                submitButton.disabled = true;
                Swal.fire({
                    title:
                        action === 'add'
                            ? 'در حال ثبت لوح...'
                            : 'در حال ذخیره تغییرات...',

                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                try {
                    const response =
                        await fetch(
                            '',
                            {
                                method: 'POST',
                                body: formData
                            }
                        );
                    const data =
                        await response.json();
                    Swal.close();
                    if (!data.success) {
                        throw new Error(
                            data.message ||
                            'عملیات با خطا مواجه شد.'
                        );
                    }

                    await Swal.fire({
                        icon: 'success',
                        title: 'موفق',
                        text: data.message,
                        confirmButtonText: 'باشه'
                    });

                    window.location.reload();
                } catch (error) {
                    Swal.close();
                    Swal.fire({
                        icon: 'error',
                        title: 'خطا',
                        text:
                            error.message ||
                            'خطایی در ارتباط با سرور رخ داد.',
                        confirmButtonText: 'باشه'
                    });
                    submitButton.disabled = false;
                }
            }
        );

        document
            .querySelectorAll('.edit-certificate')
            .forEach(button => {
                button.addEventListener(
                    'click',
                    async function () {

                        const id =
                            this.dataset.id;
                        const studentId =
                            this.dataset.student;
                        const classId =
                            this.dataset.class;
                        const type =
                            this.dataset.type;
                        const title =
                            this.dataset.title;
                        const description =
                            this.dataset.description;

                        certificateIdInput.value =
                            id;
                        titleInput.value =
                            title;
                        descriptionInput.value =
                            description;
                        typeSelect.value =
                            type;
                        classSelect.value =
                            classId;

                        await loadStudents(
                            classId,
                            studentId
                        );

                        formTitle.textContent =
                            'ویرایش لوح تقدیر';
                        submitButton.textContent =
                            'ذخیره تغییرات';
                        cancelEditButton.classList.remove(
                            'hidden'
                        );

                        window.scrollTo({
                            top: 0,
                            behavior: 'smooth'
                        });
                    }
                );
            });

        cancelEditButton.addEventListener(
            'click',
            function () {
                resetForm();
            }
        );
        document
            .querySelectorAll('.delete-certificate')
            .forEach(button => {

                button.addEventListener(
                    'click',
                    async function () {
                        const certificateId =
                            this.dataset.id;
                        const result =
                            await Swal.fire({
                                icon: 'warning',
                                title: 'حذف لوح تقدیر',
                                text:
                                    'آیا از حذف این لوح تقدیر مطمئن هستید؟',
                                showCancelButton: true,
                                confirmButtonText:
                                    'بله، حذف شود',
                                cancelButtonText:
                                    'انصراف',
                                reverseButtons: true
                            });

                        if (!result.isConfirmed) {
                            return;
                        }

                        const formData =
                            new FormData();

                        formData.append(
                            'action',
                            'delete'
                        );

                        formData.append(
                            'id',
                            certificateId
                        );

                        Swal.fire({
                            title: 'در حال حذف...',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        try {
                            const response =
                                await fetch(
                                    '',
                                    {
                                        method: 'POST',
                                        body: formData
                                    }
                                );

                            const data =
                                await response.json();

                            Swal.close();
                            if (!data.success) {
                                throw new Error(
                                    data.message ||
                                    'حذف انجام نشد.'
                                );
                            }

                            await Swal.fire({
                                icon: 'success',
                                title: 'حذف شد',
                                text: data.message,
                                confirmButtonText: 'باشه'
                            });


                            window.location.reload();
                        } catch (error) {
                            Swal.close();
                            Swal.fire({
                                icon: 'error',
                                title: 'خطا',
                                text:
                                    error.message ||
                                    'خطایی هنگام حذف رخ داد.',
                                confirmButtonText: 'باشه'
                            });
                        }
                    }
                );
            });

        function resetForm() {
            form.reset();
            certificateIdInput.value =
                '';
            studentSelect.innerHTML = `
        <option value="">
            ابتدا کلاس را انتخاب کنید
        </option>
    `;
            studentSelect.disabled =
                true;
            formTitle.textContent =
                'افزودن لوح تقدیر جدید';
            submitButton.textContent =
                'ثبت لوح تقدیر';
            cancelEditButton.classList.add(
                'hidden'
            );
        }
    </script>

</body>

</html>