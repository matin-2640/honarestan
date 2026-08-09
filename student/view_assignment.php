<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// احراز هویت دانش‌آموز (type == 0)
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
require_once "../teacher/jdf.php";

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

$assignments = [];
if ($class_id > 0) {
    try {
        $query = "SELECT
                    a.id,
                    a.title,
                    a.file_path,
                    a.class_id,
                    a.teacher_id,
                    a.expiration_date,
                    a.description,
                    t.T_fullName,
                    c.C_grade,
                    c.C_major
                  FROM assignments a
                  LEFT JOIN teachers t ON a.teacher_id = t.T_ID
                  LEFT JOIN classes c ON a.class_id = c.C_ID
                  WHERE a.class_id = :class_id
                  ORDER BY a.id DESC";
        $stmt = $connect->prepare($query);
        $stmt->execute(['class_id' => $class_id]);
        $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $assignments = [];
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تکالیف و تمرین‌ها</title>
    <link rel="icon" href="../images/icons/rahdanesh.png">

    <link rel="stylesheet" href="../styles/font.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../styles/view_assignment.css">

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
                <h1><img src="../images/icons/user.png" alt="" id="stu"> تمرین‌های کلاس من</h1>
                <p>لیست تکالیف و پروژه‌های بارگذاری شده </p>
            </div>
            <button class="theme-toggle" id="themeToggle" title="تغییر تم">
                <img src="../images/icons/theme.png" width="25px" height="25px" alt="Theme">
            </button>
        </header>

        <main class="content-area">
            <?php if (empty($assignments)): ?>
                <div class="empty-state">
                    <i class="fa-regular fa-folder-open"></i>
                    <p>هیچ تمرینی برای کلاس شما ثبت نشده است.</p>
                </div>
            <?php else: ?>
                <div class="assignments-grid">
                    <?php
                    // دریافت تاریخ امروز شمسی به عدد یکپارچه جهت مقایسه دقیق
                    $todayYear = intval(jdate('Y'));
                    $todayMonth = intval(jdate('m'));
                    $todayDay = intval(jdate('d'));
                    $todayTotalDays = ($todayYear * 365) + ($todayMonth * 30) + $todayDay;

                    foreach ($assignments as $item):
                        $title = trim($item['title'] ?? 'بدون عنوان');
                        $teacherName = trim($item['T_fullName'] ?? '');
                        if (empty($teacherName)) {
                            $teacherName = 'استاد مربوطه';
                        }

                        $className = trim(($item['C_grade'] ?? '') . ' ' . ($item['C_major'] ?? ''));
                        if (empty($className)) {
                            $className = 'کلاس شما';
                        }

                        $expDate = trim($item['expiration_date'] ?? '');

                        // محاسبه دقیق انقضا با نرمال‌سازی جداکننده‌ها و تبدیل به روز معادل
                        $isExpired = false;
                        if (!empty($expDate) && strtolower($expDate) !== 'null' && strtolower($expDate) !== 'none') {
                            $normalizedExp = str_replace(['-', '.'], '/', $expDate);
                            $expParts = explode('/', $normalizedExp);
                            if (count($expParts) === 3) {
                                $expYear = intval($expParts[0]);
                                $expMonth = intval($expParts[1]);
                                $expDay = intval($expParts[2]);
                                $expTotalDays = ($expYear * 365) + ($expMonth * 30) + $expDay;

                                // اگر تاریخ انقضا قبل از امروز باشد، منقضی شده است
                                if ($expTotalDays < $todayTotalDays) {
                                    $isExpired = true;
                                }
                            }
                        }

                        $filePath = trim($item['file_path'] ?? '');
                        $hasFile = (!empty($filePath) && strtolower($filePath) !== 'none');

                        $modalData = [
                            'title' => $title,
                            'class' => $className,
                            'teacher' => $teacherName,
                            'expiration' => !empty($expDate) ? htmlspecialchars($expDate) : 'نامشخص',
                            'status' => $isExpired ? 'منقضی شده' : 'فعال',
                            'description' => trim($item['description'] ?? ''),
                            'file' => $hasFile ? htmlspecialchars($filePath) : ''
                        ];
                        ?>
                        <div class="assignment-card <?php echo $isExpired ? 'expired' : 'active-status'; ?>"
                            onclick='openAssignmentModal(<?php echo htmlspecialchars(json_encode($modalData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP), ENT_QUOTES, "UTF-8"); ?>)'>

                            <div class="card-header">
                                <h3 class="assignment-title"><?php echo htmlspecialchars($title); ?></h3>
                                <span class="badge <?php echo $isExpired ? 'badge-expired' : 'badge-active'; ?>">
                                    <?php echo $isExpired ? 'منقضی شده' : 'فعال'; ?>
                                </span>
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
                                <div class="info-row">
                                    <i class="fa-regular fa-calendar-days"></i>
                                    <span>مهلت: <?php echo !empty($expDate) ? htmlspecialchars($expDate) : 'نامشخص'; ?></span>
                                </div>
                            </div>

                            <div class="card-footer">
                                <?php if ($hasFile): ?>
                                    <a href="<?php echo htmlspecialchars($filePath); ?>" class="download-link" download
                                        onclick="event.stopPropagation();">
                                        <i class="fa-solid fa-download"></i> دانلود فایل
                                    </a>
                                <?php else: ?>
                                    <span class="no-file-text">این تمرین فایل پیوست ندارد.</span>
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

    <div id="assignmentModal" class="modal-overlay" onclick="closeAssignmentModal(event)">
        <div class="modal-content" onclick="event.stopPropagation();">
            <div class="modal-header">
                <h2 id="modalTitle">عنوان تمرین</h2>
                <button class="modal-close-btn" onclick="closeModalDirect()"><img src="../images/icons/zarb.png" alt=""
                        id="zarb"></button>
            </div>
            <div class="modal-body">
                <div class="modal-meta-grid">
                    <div class="meta-item">
                        <strong>کلاس:</strong> <span id="modalClass">-</span>
                    </div>
                    <div class="meta-item">
                        <strong>استاد:</strong> <span id="modalTeacher">-</span>
                    </div>
                    <div class="meta-item">
                        <strong>مهلت تحویل:</strong> <span id="modalExpiration">-</span>
                    </div>
                    <div class="meta-item">
                        <strong>وضعیت:</strong> <span id="modalStatusBadge" class="badge">-</span>
                    </div>
                </div>

                <div class="modal-description-section">
                    <h4>توضیحات تمرین:</h4>
                    <div id="modalDescription" class="description-text">توضیحاتی برای این تمرین درج نشده است.</div>
                </div>

                <div id="modalFileSection" class="modal-file-section" style="display: none;">
                    <a id="modalDownloadLink" href="#" class="modal-download-btn" download>
                        <i class="fa-solid fa-download"></i> دانلود فایل تمرین
                    </a>
                </div>
            </div>
        </div>
    </div>

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

        function openAssignmentModal(data) {
            document.getElementById('modalTitle').textContent = data.title;
            document.getElementById('modalClass').textContent = data.class;
            document.getElementById('modalTeacher').textContent = data.teacher;
            document.getElementById('modalExpiration').textContent = data.expiration;

            const statusBadge = document.getElementById('modalStatusBadge');
            statusBadge.textContent = data.status;
            if (data.status === 'منقضی شده') {
                statusBadge.className = 'badge badge-expired';
            } else {
                statusBadge.className = 'badge badge-active';
            }

            const descContainer = document.getElementById('modalDescription');
            if (data.description && data.description.trim() !== '') {
                descContainer.textContent = data.description;
            } else {
                descContainer.textContent = 'توضیحاتی برای این تمرین درج نشده است.';
            }

            const fileSection = document.getElementById('modalFileSection');
            const downloadLink = document.getElementById('modalDownloadLink');
            if (data.file && data.file.trim() !== '') {
                downloadLink.href = data.file;
                fileSection.style.display = 'block';
            } else {
                fileSection.style.display = 'none';
            }

            document.getElementById('assignmentModal').classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function closeModalDirect() {
            document.getElementById('assignmentModal').classList.remove('show');
            document.body.style.overflow = 'auto';
        }

        function closeAssignmentModal(event) {
            if (event.target.id === 'assignmentModal') {
                closeModalDirect();
            }
        }

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeModalDirect();
            }
        });
    </script>
</body>

</html>