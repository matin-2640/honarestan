<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!(isset($_SESSION["state_login"]) && $_SESSION["type"] == 0)) {
    header("location:../login.php");
    exit();
}

$session_student_id = 0;
if (isset($_SESSION["ID"])) {
    $session_student_id = intval($_SESSION["ID"]);
} elseif (isset($_SESSION["student_id"])) {
    $session_student_id = intval($_SESSION["student_id"]);
} elseif (isset($_SESSION["user_id"])) {
    $session_student_id = intval($_SESSION["user_id"]);
}

if ($session_student_id <= 0) {
    header("location:../login.php");
    exit();
}

require_once "../connect.php";

$class_id = 0;
try {
    $stmt = $connect->prepare("SELECT Stu_classID FROM students WHERE Stu_ID = :student_id LIMIT 1");
    $stmt->execute(['student_id' => $session_student_id]);
    $studentData = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($studentData && isset($studentData['Stu_classID'])) {
        $class_id = intval($studentData['Stu_classID']);
    }
} catch (PDOException $e) {
    $class_id = 0;
}

$notes = [];
if ($class_id > 0) {
    try {
        $query = "SELECT 
                    n.id,
                    n.title,
                    n.file_path,
                    n.class_id,
                    n.teacher_id,
                    t.T_fullName,
                    c.C_grade,
                    c.C_major
                  FROM notes n
                  LEFT JOIN teachers t ON n.teacher_id = t.T_ID
                  LEFT JOIN classes c ON n.class_id = c.C_ID
                  WHERE n.class_id = :class_id
                  ORDER BY n.id DESC";
        $stmt = $connect->prepare($query);
        $stmt->execute(['class_id' => $class_id]);
        $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $notes = [];
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>جزوه‌های درسی</title>
    <link rel="icon" href="../images/icons/rahdanesh.png">
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet"
        type="text/css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../styles/view_note.css">
    <script>
        (function () {
            const savedTheme = localStorage.getItem("theme") || "light";
            document.documentElement.setAttribute("data-theme", savedTheme);
        })();
    </script>
</head>

<body>

    <div class="page-container">
        <header class="page-header">
            <div class="header-title-wrapper">
                <h1><i class="fa-solid fa-file-lines"></i> جزوه‌های کلاس من</h1>
                <p>فایل‌ها و مستندات آموزشی بارگذاری‌شده </p>
            </div>
            <button class="theme-toggle" id="themeToggle" title="تغییر تم">
                <img src="../images/icons/theme.png" width="25px" height="25px" alt="Theme">
            </button>
        </header>

        <main class="content-area">
            <?php if (empty($notes)): ?>
                <div class="empty-state">
                    <i class="fa-regular fa-folder-open"></i>
                    <p>هیچ جزوه‌ای برای کلاس شما ثبت نشده است.</p>
                </div>
            <?php else: ?>
                <div class="notes-grid">
                    <?php foreach ($notes as $item):
                        $title = trim($item['title'] ?? 'بدون عنوان');
                        $teacherName = trim($item['T_fullName'] ?? '');
                        if (empty($teacherName)) {
                            $teacherName = 'استاد مربوطه';
                        }

                        $className = trim(($item['C_grade'] ?? '') . ' ' . ($item['C_major'] ?? ''));
                        if (empty($className)) {
                            $className = 'کلاس شما';
                        }

                        $filePath = trim($item['file_path'] ?? '');
                        $hasFile = (!empty($filePath) && strtolower($filePath) !== 'none');
                        ?>
                        <div class="note-card">
                            <div class="card-header">
                                <h3 class="note-title"><i class="fa-regular fa-file-pdf"></i>
                                    <?php echo htmlspecialchars($title); ?></h3>
                            </div>

                            <div class="card-body">
                                <div class="info-row">
                                    <i class="fa-solid fa-chalkboard"></i>
                                    <span>کلاس: <?php echo htmlspecialchars($className); ?></span>
                                </div>
                                <div class="info-row">
                                    <i class="fa-solid fa-chalkboard-user"></i>
                                    <span>استاد: <?php echo htmlspecialchars($teacherName); ?></span>
                                </div>
                            </div>

                            <div class="card-footer">
                                <?php if ($hasFile): ?>
                                    <a href="<?php echo htmlspecialchars($filePath); ?>" class="download-link" download>
                                        <i class="fa-solid fa-download"></i> دانلود جزوه
                                    </a>
                                <?php else: ?>
                                    <span class="no-file-text">این جزوه فایل پیوست ندارد.</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>
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
            display: inline-flex;
            margin-top: 10px;
            max-width: 160px;
        }
    </style>
    <a href="../admin_panel.php" id="smsParentBtn" class="btn-view-link">
        بازگشت به پنل مدیریت
    </a>
    <script src="../js/theme.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const themeToggleBtn = document.getElementById("themeToggle");
            if (themeToggleBtn) {
                themeToggleBtn.addEventListener("click", function () {
                    let currentTheme = document.documentElement.getAttribute("data-theme");
                    let newTheme = currentTheme === "dark" ? "light" : "dark";
                    document.documentElement.setAttribute("data-theme", newTheme);
                    localStorage.setItem("theme", newTheme);
                });
            }
        });
    </script>
</body>

</html>