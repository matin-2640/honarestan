<?php
session_start();
require_once '../connect.php';

// بررسی لاگین بودن معلم (استفاده از کلید سشن درست)
if (!(isset($_SESSION["state_login"]) && $_SESSION["type"] == 1)) {
    header("location:../login.php");
    exit();
}

$teacher_id = $_SESSION['ID'];

// پردازش درخواست‌های AJAX
if (isset($_POST['action'])) {
    header('Content-Type: application/json; charset=utf-8');

    // 1. دریافت دروس یک کلاس مشخص که معلم در آن تدریس می‌کند
    if ($_POST['action'] === 'get_courses') {
        $class_id = intval($_POST['class_id']);

        $stmt = $connect->prepare("SELECT Co_ID, Co_name, Co_type FROM courses WHERE Co_teacherID = ? AND Co_classID = ?");
        $stmt->execute([$teacher_id, $class_id]);
        $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($courses);
        exit();
    }

    // 2. محاسبه میانگین نمرات دانش‌آموزان
    if ($_POST['action'] === 'calculate_average') {
        $course_id = intval($_POST['course_id']);
        $term = intval($_POST['term']);

        // پاکسازی نقاط اضافی و کست کردن نمرات به عدد برای محاسبه دقیق میانگین
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

// دریافت لیست کلاس‌هایی که معلم در آن‌ها حداقل یک درس دارد
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
    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- FontAwesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../styles/font.css" />
    <link rel="stylesheet" href="../styles/class_avg.css" />
    <link rel="icon" href="../images/icons/rahdanesh.png" />
</head>

<body>

    <div id="loader">
        <p>در حال بارگذاری...</p>
    </div>

    <header>
        <a href="../teacher_panel.php">بازگشت به پنل معلم</a>
        <button id="themeToggle" type="button">
            <img src="../images/icons/theme.png" width="25px" height="25px" />
        </button>

    </header>

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
    </main>

    <!-- اسکریپت تم و قالب عمومی -->
    <script src="../js/theme.js"></script>

    <!-- منطق کنترل فرم و تعاملات AJAX -->
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            // مخفی کردن لودر در صورت نیاز
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

            // دوره‌های دروس عمومی (Co_type = 1 یا غیر صفر)
            const generalTerms = [
                { id: 1, name: "مهر و آبان" },
                { id: 2, name: "آذر" },
                { id: 3, name: "نوبت اول" },
                { id: 4, name: "اسفند" },
                { id: 5, name: "فروردین و اردیبهشت" },
                { id: 6, name: "خرداد" }
            ];

            // دوره‌های دروس پودمانی (Co_type = 0)
            const podmaniTerms = [
                { id: 1, name: "پودمان یک (مهر و آبان)" },
                { id: 2, name: "پودمان دو (نوبت اول)" },
                { id: 3, name: "پودمان سه (اسفند)" },
                { id: 4, name: "پودمان چهار (فروردین و اردیبهشت)" },
                { id: 5, name: "پودمان پنج (نوبت دوم)" }
            ];

            // ۱. انتخاب کلاس و بارگذاری دروس
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

            // ۲. انتخاب درس و تغییر پویای گزینه‌های دوره/پودمان
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

            // ۳. ارسال اطلاعات و نمایش میانگین با SweetAlert2
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