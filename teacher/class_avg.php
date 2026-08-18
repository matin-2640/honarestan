<?php
session_start();
require_once '../connect.php';

if (!(isset($_SESSION["state_login"]) && $_SESSION["type"] == 1)) {
    header("location:../login.php");
    exit();
}

$teacher_id = $_SESSION['ID'];

if (isset($_POST['action'])) {
    header('Content-Type: application/json; charset=utf-8');

    if ($_POST['action'] === 'get_courses') {
        $class_id = intval($_POST['class_id']);

        $stmt = $connect->prepare("SELECT Co_ID, Co_name, Co_type FROM courses WHERE Co_teacherID = ? AND Co_classID = ?");
        $stmt->execute([$teacher_id, $class_id]);
        $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($courses);
        exit();
    }

    if ($_POST['action'] === 'calculate_average') {
        $course_id = intval($_POST['course_id']);
        $term = intval($_POST['term']);

        $sql = "SELECT 
                    AVG(CAST(TRIM(TRAILING '.' FROM G_num) AS DECIMAL(4,2))) as average, 
                    COUNT(G_ID) as total_grades 
                FROM grades 
                WHERE G_courseID = ? AND G_term = ?";

        $stmt_avg = $connect->prepare($sql);
        $stmt_avg->execute([$course_id, $term]);
        $result = $stmt_avg->fetch(PDO::FETCH_ASSOC);

        if ($result && $result['total_grades'] > 0 && $result['average'] !== null) {
            $avg = round($result['average'], 2);
            echo json_encode([
                'status' => 'success',
                'average' => $avg,
                'count' => $result['total_grades']
            ]);
        } else {
            echo json_encode([
                'status' => 'empty',
                'message' => 'هیچ نمره‌ای برای این درس در این دوره ثبت نشده است.'
            ]);
        }
        exit();
    }
}

$stmt_classes = $connect->prepare("
    SELECT DISTINCT c.C_ID, c.C_grade, c.C_major 
    FROM classes c
    INNER JOIN courses co ON c.C_ID = co.Co_classID
    WHERE co.Co_teacherID = ?
");
$stmt_classes->execute([$teacher_id]);
$teacher_classes = $stmt_classes->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>میانگین نمرات کلاس</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../styles/font.css" />
    <link rel="stylesheet" href="../styles/class_avg.css" />
    <link rel="icon" href="../images/icons/rahdanesh.png" />
</head>

<body>

    <div id="loader">
        <p>در حال بارگذاری...</p>
    </div>

    <style>
        .teacher-menu-header,
        .teacher-menu-header *,
        .teacher-sidebar,
        .teacher-sidebar *,
        .teacher-sidebar-overlay {
            box-sizing: border-box;
        }

        .teacher-menu-header {
            width: 100%;
            height: 64px;
            position: fixed;
            top: 0;
            right: 0;
            left: 0;
            z-index: 10000;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            background: var(--bg-card, #fff);
            border-bottom: 1px solid var(--border-color, #e2e8f0);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            direction: rtl;
        }

        .teacher-menu-toggle,
        .teacher-theme-toggle {
            width: 44px;
            height: 44px;
            padding: 0;
            border: 0;
            background: transparent;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .teacher-menu-toggle:hover,
        .teacher-theme-toggle:hover {
            background: var(--bg-main, #f8fafc);
        }

        .teacher-menu-logo {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--text-main, #0f172a);
            font-weight: 700;
            font-size: 1.1rem;
        }

        .teacher-sidebar {
            width: 270px;
            position: fixed;
            top: 64px;
            right: -280px;
            bottom: 0;
            z-index: 10001;
            display: flex;
            flex-direction: column;
            background: var(--bg-card, #fff);
            border-left: 1px solid var(--border-color, #e2e8f0);
            box-shadow: -4px 0 20px rgba(0, 0, 0, 0.08);
            transition: right 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            direction: rtl;
        }

        .teacher-sidebar.teacher-active {
            right: 0;
        }

        .teacher-sidebar-brand {
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            color: var(--color-primary, #2563eb);
            font-size: 1.1rem;
            font-weight: 800;
            border-bottom: 1px solid var(--border-color, #e2e8f0);
        }

        .teacher-sidebar-nav {
            flex: 1;
            padding: 16px 12px;
            overflow-y: auto;
        }

        .teacher-sidebar-nav ul {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .teacher-sidebar-nav li {
            margin: 0;
            padding: 0;
        }

        .teacher-sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            border-radius: 8px;
            color: var(--text-muted, #64748b);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: 0.2s;
        }

        .teacher-sidebar-nav a:hover,
        .teacher-sidebar-nav a.teacher-current {
            background: var(--color-primary, #2563eb);
            color: #fff;
        }

        .teacher-sidebar-nav img,
        .teacher-sidebar-brand img,
        .teacher-sidebar-footer img {
            flex-shrink: 0;
        }

        .teacher-sidebar-footer {
            padding: 16px;
            border-top: 1px solid var(--border-color, #e2e8f0);
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .teacher-sidebar-footer a {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .teacher-back-home {
            background: var(--bg-main, #f8fafc);
            color: var(--text-main, #0f172a);
        }

        .teacher-back-home:hover {
            background: var(--border-color, #e2e8f0);
        }

        .teacher-logout {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }

        .teacher-logout:hover {
            background: #ef4444;
            color: #fff;
        }

        .teacher-sidebar-overlay {
            position: fixed;
            top: 64px;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 9999;
            background: rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(4px);
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition: 0.3s;
        }

        .teacher-sidebar-overlay.teacher-active {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
        }

        @media (max-width: 600px) {
            .teacher-menu-header {
                height: 60px;
                padding: 0 12px;
            }

            .teacher-menu-logo {
                font-size: 0.9rem;
            }

            .teacher-menu-toggle,
            .teacher-theme-toggle {
                width: 40px;
                height: 40px;
            }

            .teacher-sidebar {
                top: 60px;
                width: 270px;
                right: -280px;
            }

            .teacher-sidebar-overlay {
                top: 60px;
            }
        }

        .teacher_menu_active {
            background: #2563eb !important;
            color: #fff !important;
        }
    </style>

    <header class="teacher-menu-header">
        <button class="teacher-menu-toggle" id="teacherMenuToggle" type="button">
            <img src="../images/icons/menu.png" width="25" height="25" />
        </button>

        <div class="teacher-menu-logo">
            <img src="../images/icons/user.png" width="25" height="25" />
            <span>پنل مدیریتی معلم</span>
        </div>

        <button class="teacher-theme-toggle" id="teacherThemeToggle" type="button">
            <img src="../images/icons/theme.png" width="25" height="25" />
        </button>
    </header>

    <aside class="teacher-sidebar" id="teacherSidebar">
        <div class="teacher-sidebar-brand">
            <img src="../images/icons/user.png" width="20" height="20" />
            <span>پنل معلم سیستم</span>
        </div>

        <nav class="teacher-sidebar-nav">
            <ul>
                <li>
                    <a href="panel.php">
                        <img src="../images/icons/first.png" width="20" height="20" />
                        <span>خانه</span>
                    </a>
                </li>

                <li>
                    <a href="online_class/index.php">
                        <img src="../images/icons/playgray.png" width="20" height="20" />
                        <span>کلاس مجازی</span>
                    </a>
                </li>

                <li>
                    <a href="add_score_teacher.php">
                        <img src="../images/icons/uploadnote.png" width="20" height="20" />
                        <span>ثبت نمره</span>
                    </a>
                </li>

                <li>
                    <a href="upload_note.php">
                        <img src="../images/icons/managescore.png" width="20" height="20" />
                        <span>بارگذاری جزوه</span>
                    </a>
                </li>

                <li>
                    <a href="upload_assignment.php">
                        <img src="../images/icons/check.png" width="20" height="20" />
                        <span>بارگذاری تمرین</span>
                    </a>
                </li>

                <li>
                    <a href="class_avg.php" class="teacher_menu_active">
                        <img src="../images/icons/Chevron-left.png" width="20" height="20" />
                        <span>میانگین نمرات ترم</span>
                    </a>
                </li>

                <li>
                    <a href="../teacher_attendance_report.php">
                        <img src="../images/icons/Chevron-left.png" width="20" height="20" />
                        <span>لیست حضور و غیاب ها</span>
                    </a>
                </li>
            </ul>
        </nav>

        <div class="teacher-sidebar-footer">
            <a href="../index.php" class="teacher-back-home">
                <img src="../images/icons/back.png" width="20" height="20" />
                <span>بازگشت به صفحه اصلی</span>
            </a>

            <a href="../logout.php" class="teacher-logout">
                <img src="../images/icons/leave.png" width="20" height="20" />
                <span>خروج از حساب</span>
            </a>
        </div>
    </aside>

    <div class="teacher-sidebar-overlay" id="teacherSidebarOverlay"></div>

    <script>
        (function () {
            const menuToggle = document.getElementById("teacherMenuToggle");
            const sidebar = document.getElementById("teacherSidebar");
            const overlay = document.getElementById("teacherSidebarOverlay");
            const themeToggle = document.getElementById("teacherThemeToggle");

            if (!menuToggle || !sidebar || !overlay) return;

            function openMenu() {
                sidebar.classList.add("teacher-active");
                overlay.classList.add("teacher-active");
            }

            function closeMenu() {
                sidebar.classList.remove("teacher-active");
                overlay.classList.remove("teacher-active");
            }

            menuToggle.addEventListener("click", function () {
                if (sidebar.classList.contains("teacher-active")) {
                    closeMenu();
                } else {
                    openMenu();
                }
            });

            overlay.addEventListener("click", closeMenu);

            document.addEventListener("keydown", function (e) {
                if (e.key === "Escape") {
                    closeMenu();
                }
            });

            sidebar.querySelectorAll("a").forEach(function (link) {
                link.addEventListener("click", closeMenu);
            });

            if (themeToggle) {
                themeToggle.addEventListener("click", function () {
                    const html = document.documentElement;
                    const currentTheme = html.getAttribute("data-theme") || "light";
                    const newTheme = currentTheme === "dark" ? "light" : "dark";

                    html.setAttribute("data-theme", newTheme);
                    localStorage.setItem("theme", newTheme);
                });
            }
        })();
    </script>

    <br><br>
    <main>
        <h1>مشاهده میانگین نمرات دانش‌آموزان</h1>

        <form id="gradeAverageForm">
            <div>
                <label for="classSelect">انتخاب کلاس:</label>
                <select id="classSelect" name="class_id" required>
                    <?php if (empty($teacher_classes)): ?>
                        <option value="">کلاسی برای شما اختصاص داده نشده است</option>
                    <?php else: ?>
                        <option value="">-- کلاس را انتخاب کنید --</option>
                        <?php foreach ($teacher_classes as $class): ?>
                            <option value="<?= $class['C_ID'] ?>">
                                پایه <?= htmlspecialchars($class['C_grade']) ?> - <?= htmlspecialchars($class['C_major']) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div>
                <label for="courseSelect">انتخاب درس:</label>
                <select id="courseSelect" name="course_id" disabled required>
                    <option value="">-- ابتدا کلاس را انتخاب کنید --</option>
                </select>
            </div>

            <div>
                <label for="termSelect">انتخاب دوره / ترم / پودمان:</label>
                <select id="termSelect" name="term" disabled required>
                    <option value="">-- ابتدا درس را انتخاب کنید --</option>
                </select>
            </div>

            <button type="submit" id="btnCalculate" disabled>محاسبه میانگین</button>

        </form>
        <a href="../teacher_panel.php">
            <button type="submit" id="btnCalculate">بازگشت به پنل معلم</button>
        </a>
    </main>

    <script src="../js/theme.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const loader = document.getElementById("loader");
            if (loader) {
                setTimeout(() => {
                    loader.classList.add("loader-hide");
                    setTimeout(() => loader.remove(), 500);
                }, 300);
            }

            const classSelect = document.getElementById("classSelect");
            const courseSelect = document.getElementById("courseSelect");
            const termSelect = document.getElementById("termSelect");
            const btnCalculate = document.getElementById("btnCalculate");

            const generalTerms = [
                { id: 1, name: "مهر و آبان" },
                { id: 2, name: "آذر" },
                { id: 3, name: "نوبت اول" },
                { id: 4, name: "اسفند" },
                { id: 5, name: "فروردین و اردیبهشت" },
                { id: 6, name: "خرداد" }
            ];

            const podmaniTerms = [
                { id: 1, name: "پودمان یک (مهر و آبان)" },
                { id: 2, name: "پودمان دو (نوبت اول)" },
                { id: 3, name: "پودمان سه (اسفند)" },
                { id: 4, name: "پودمان چهار (فروردین و اردیبهشت)" },
                { id: 5, name: "پودمان پنج (نوبت دوم)" }
            ];

            classSelect.addEventListener("change", function () {
                const classId = this.value;

                courseSelect.innerHTML = '<option value="">-- در حال بارگذاری... --</option>';
                courseSelect.disabled = true;
                termSelect.innerHTML = '<option value="">-- ابتدا درس را انتخاب کنید --</option>';
                termSelect.disabled = true;
                btnCalculate.disabled = true;

                if (!classId) {
                    courseSelect.innerHTML = '<option value="">-- ابتدا کلاس را انتخاب کنید --</option>';
                    return;
                }

                const formData = new FormData();
                formData.append("action", "get_courses");
                formData.append("class_id", classId);

                fetch("", {
                    method: "POST",
                    body: formData
                })
                    .then(res => res.json())
                    .then(courses => {
                        courseSelect.innerHTML = '<option value="">-- درس را انتخاب کنید --</option>';
                        if (courses.length > 0) {
                            courses.forEach(course => {
                                const opt = document.createElement("option");
                                opt.value = course.Co_ID;
                                opt.textContent = course.Co_name;
                                opt.dataset.type = course.Co_type;
                                courseSelect.appendChild(opt);
                            });
                            courseSelect.disabled = false;
                        } else {
                            courseSelect.innerHTML = '<option value="">هیچ درسی برای شما در این کلاس ثبت نشده است</option>';
                        }
                    })
                    .catch(() => {
                        Swal.fire({
                            icon: 'error',
                            title: 'خطا',
                            text: 'مشکلی در دریافت لیست دروس به وجود آمد.'
                        });
                    });
            });

            courseSelect.addEventListener("change", function () {
                const selectedOption = this.options[this.selectedIndex];
                termSelect.innerHTML = '<option value="">-- دوره را انتخاب کنید --</option>';

                if (!this.value) {
                    termSelect.disabled = true;
                    btnCalculate.disabled = true;
                    return;
                }

                const courseType = selectedOption.dataset.type;
                const termsList = (courseType === "0" || courseType === 0) ? podmaniTerms : generalTerms;

                termsList.forEach(term => {
                    const opt = document.createElement("option");
                    opt.value = term.id;
                    opt.textContent = term.name;
                    termSelect.appendChild(opt);
                });

                termSelect.disabled = false;
                btnCalculate.disabled = false;
            });

            document.getElementById("gradeAverageForm").addEventListener("submit", function (e) {
                e.preventDefault();

                const courseId = courseSelect.value;
                const term = termSelect.value;

                if (!courseId || !term) return;

                const formData = new FormData();
                formData.append("action", "calculate_average");
                formData.append("course_id", courseId);
                formData.append("term", term);

                fetch("", {
                    method: "POST",
                    body: formData
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === "success") {
                            Swal.fire({
                                icon: 'info',
                                title: 'میانگین نمرات دانش‌آموزان',
                                html: `میانگین این درس در دوره انتخاب‌شده:<br><strong style="font-size:1.8rem; color:#2563eb; display:inline-block; margin-top:10px;">${data.average}</strong><br><small style="color:gray;">(از مجموع ${data.count} نمره ثبت شده)</small>`,
                                confirmButtonText: 'متوجه شدم'
                            });
                        } else if (data.status === "empty") {
                            Swal.fire({
                                icon: 'warning',
                                title: 'اطلاعاتی یافت نشد',
                                text: data.message,
                                confirmButtonText: 'تایید'
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'خطا',
                                text: data.message,
                                confirmButtonText: 'تایید'
                            });
                        }
                    })
                    .catch(() => {
                        Swal.fire({
                            icon: 'error',
                            title: 'خطا',
                            text: 'ارتباط با سرور برقرار نشد.',
                            confirmButtonText: 'تایید'
                        });
                    });
            });
        });
    </script>
</body>

</html>