<?php
session_start();
if (!(isset($_SESSION["state_login"]) && $_SESSION["type"] <= 2)) {
    header("location:login.php");
    exit();
}

include("connect.php");

try {
    $stmt_classes = $connect->prepare("SELECT C_ID, C_Grade, C_Major FROM Classes ORDER BY C_Grade ASC");
    $stmt_classes->execute();
    $classList = $stmt_classes->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $classList = [];
}
?>
<!doctype html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ثبت نمره | پورتال هنرستان</title>
    <link rel="stylesheet" href="styles/panel_style.css" />
    <link rel="stylesheet" href="styles/profile_style.css" />
    <link rel="stylesheet" href="styles/add_student.css" />
    <link rel="stylesheet" href="styles/font.css">
    <link rel="stylesheet" href="styles/students_list_style.css" />

    <link rel="icon" href="images/icons/rahdanesh.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/vazirmatn@33.0.3/Vazirmatn-font-face.css" />

    <!-- لینک CDN معتبر معلق نماند -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
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
                    <small>ثبت نمره</small>
                </div>
            </div>
            <nav class="panel-nav" id="panelNav">
                <a href="admin_panel.php">صفحه نخست</a>
                <a href="#" class="active">ثبت نمره</a>
                <a href="admin_panel.php" class="back-link-btn">بازگشت</a>
            </nav>

            <div class="header-actions">
                <button class="theme-toggle" id="themeToggle" title="تغییر حالت شب و روز">
                    <svg viewBox="0 0 24 24" class="theme-svg-icon" id="themeIcon">
                        <path class="moon-path"
                            d="M12.3 2a10 10 0 0 0-1.9 19.8 10 10 0 0 0 11.8-11.8A10 10 0 0 1 12.3 2z" />
                    </svg>
                </button>
            </div>
        </div>
    </header>

    <main class="panel-container profile-layout">
        <section class="profile-card">
            <div class="profile-card-header">
                <h2 class="profile-student-name">فرم ثبت نمره</h2>
                <p class="profile-student-sub">مشخصات زیر را با دقت وارد نموده و سپس دکمه ثبت نهایی را بزنید.</p>
                <span class="cell-label" style="color:red;   font-weight: 900; font-size:12px;">در صورت وارد نکردن نمره
                    پیشفرض 0 درنظر
                    گرفته میشود</span>

            </div>

            <form action="Add_score_back.php" method="POST" class="register-form">
                <div class="profile-info-grid">

                    <!-- انتخاب دوره -->
                    <div class="info-item">
                        <label for="G_term">انتخاب دوره<span class="required-star">*</span></label>
                        <div class="select-wrapper">
                            <select id="G_term" name="G_term" class="info-value-box input-field select-field" required>
                                <option value="" disabled selected hidden>انتخاب دوره...</option>
                                <option value="1">مهر و آبان</option>
                                <option value="2">آذر</option>
                                <option value="3">نوبت اول</option>
                                <option value="4">اسفند</option>
                                <option value="5">فروردین و اردیبهشت</option>
                                <option value="6">نوبت دوم</option>
                            </select>
                        </div>
                    </div>

                    <!-- انتخاب کلاس -->
                    <div class="info-item">
                        <label for="C_ID">کلاس درس<span class="required-star">*</span></label>
                        <div class="select-wrapper">
                            <select id="C_ID" name="C_ID" class="info-value-box input-field select-field" required>
                                <option value="" disabled selected hidden>انتخاب کلاس...</option>
                                <?php foreach ($classList as $cls): ?>
                                    <option value="<?php echo $cls['C_ID']; ?>">
                                        <?php echo "پایه " . $cls['C_Grade'] . " - " . $cls['C_Major']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- انتخاب درس -->
                    <div class="info-item">
                        <label for="G_courseID">انتخاب درس<span class="required-star">*</span></label>
                        <div class="select-wrapper">
                            <select id="G_courseID" name="G_courseID" class="info-value-box input-field select-field"
                                required disabled>
                                <option value="" disabled selected hidden>ابتدا کلاس را انتخاب کنید...</option>
                            </select>
                        </div>
                    </div>

                </div>

                <h2 class="list-main-title" style="margin-top: 25px;">لیست هنرجویان</h2>

                <div id="students_container" class="students-linear-list">
                    <p style="text-align: center; color: #888; padding: 15px;">لطفاً ابتدا کلاس درس را انتخاب کنید.</p>
                </div>

                <div class="profile-actions-footer register-actions">
                    <button type="submit" class="btn-back-home btn-submit-register">
                        <svg viewBox="0 0 24 24" class="btn-svg-icon">
                            <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z" />
                        </svg>
                        ثبت و ذخیره درس
                    </button>
                </div>
            </form>
        </section>
    </main>

    <script>
        $(document).ready(function () {
            console.log("jQuery loaded successfully.");

            $('#C_ID').on('change', function () {
                var classID = $(this).val();
                console.log("Selected Class ID:", classID);

                if (classID) {
                    // ۱. دریافت دروس
                    $.ajax({
                        url: 'get_grade_data.php',
                        type: 'POST',
                        data: { action: 'get_courses', class_id: classID },
                        dataType: 'json',
                        success: function (courses) {
                            console.log("Courses received:", courses);
                            var courseSelect = $('#G_courseID');
                            courseSelect.empty();
                            courseSelect.append('<option value="" disabled selected hidden>انتخاب درس...</option>');

                            if (courses && courses.length > 0) {
                                $.each(courses, function (index, course) {
                                    courseSelect.append('<option value="' + course.Co_ID + '">' + course.Co_Name + '</option>');
                                });
                                courseSelect.prop('disabled', false);
                            } else {
                                courseSelect.append('<option value="" disabled>درسی برای این کلاس یافت نشد</option>');
                                courseSelect.prop('disabled', true);
                            }
                        },
                        error: function (xhr, status, error) {
                            console.error("Courses AJAX Error:", error, xhr.responseText);
                        }
                    });

                    // ۲. دریافت دانش‌آموزان
                    $.ajax({
                        url: 'get_grade_data.php',
                        type: 'POST',
                        data: { action: 'get_students', class_id: classID },
                        dataType: 'html',
                        success: function (htmlResponse) {
                            console.log("Students HTML received.");
                            $('#students_container').html(htmlResponse);
                        },
                        error: function (xhr, status, error) {
                            console.error("Students AJAX Error:", error, xhr.responseText);
                        }
                    });

                }
            });
        });
    </script>

    <script src="https://unpkg.com/lenis@1.3.11/dist/lenis.min.js"></script>
    <script type="text/javascript" src="js/theme.js"></script>

</body>

</html>