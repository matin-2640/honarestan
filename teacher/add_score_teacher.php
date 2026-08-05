<?php
session_start();
if (!(isset($_SESSION["state_login"]) && $_SESSION["type"] <= 2)) {
    header("location:../login.php");
    exit();
}

include("../connect.php");

// چک کردن دقیق تمام کلیدهای احتمالی سشن برای آیدی معلم
$teacher_id = 0;
if (isset($_SESSION["user_id"])) {
    $teacher_id = intval($_SESSION["user_id"]);
} elseif (isset($_SESSION["teacher_id"])) {
    $teacher_id = intval($_SESSION["teacher_id"]);
} elseif (isset($_SESSION["T_ID"])) {
    $teacher_id = intval($_SESSION["T_ID"]);
}

try {
    // کوئری برای استخراج کلاس‌های معلم با آیدی مشخص شده
    $stmt_classes = $connect->prepare("
        SELECT DISTINCT c.C_ID, c.C_grade, c.C_major 
        FROM courses co
        JOIN classes c ON co.Co_classID = c.C_ID
        WHERE co.Co_teacherID = :tid
        ORDER BY c.C_grade ASC
    ");
    $stmt_classes->execute([':tid' => $teacher_id]);
    $classList = $stmt_classes->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $classList = [];
}
?>
<!doctype html>
<html lang="fa" dir="rtl" data-theme="light">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ثبت نمره | پنل معلم</title>
    <link rel="stylesheet" href="../styles/font.css">
    <link rel="icon" href="../images/icons/rahdanesh.png">
    <link rel="stylesheet" href="../styles/score_style.css">
    
    <script src="../js/jquery-1.10.2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <header class="panel-header">
        <div class="panel-container header-wrapper">
            <div class="user-profile-brief">
                <div class="user-avatar-mini">
                    <svg viewBox="0 0 24 24" class="inline-svg">
                        <path
                            d="M18 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zM6 4h5v8l-2.5-1.5L6 12V4z" />
                    </svg>
                </div>
                <div class="user-info-text">
                    <span>پنل معلم هنرستان</span>
                    <small>ثبت نمرات دانش‌آموزان</small>
                </div>
            </div>

            <nav class="panel-nav" id="panelNav">
                <a href="teacher_panel.php">صفحه نخست</a>
                <a href="#" class="active">ثبت نمره</a>
            </nav>

            <div class="header-actions">
                <button class="theme-toggle" id="themeToggle" title="تغییر حالت شب و روز">
                    <svg viewBox="0 0 24 24" class="inline-svg" id="themeSvgIcon">
                        <path d="M12.3 2a10 10 0 0 0-1.9 19.8 10 10 0 0 1 11.8-11.8A10 10 0 0 1 12.3 2z" />
                    </svg>
                </button>
            </div>
        </div>
    </header>

    <main class="panel-container profile-layout">
        <form action="../Add_score_back.php" method="POST" id="scoreForm" class="register-form">

            <section class="profile-card">
                <div class="profile-card-header">
                    <h2 class="profile-student-name">
                        <svg viewBox="0 0 24 24" class="inline-svg">
                            <path
                                d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z" />
                        </svg>
                        ثبت نمرات
                    </h2>
                    <p class="profile-student-sub">مشخصات کلاس، درس و دوره را جهت ثبت نمرات انتخاب کنید.</p>
                </div>

                <div class="profile-info-grid">
                    <div class="info-item">
                        <label for="C_ID">انتخاب کلاس<span class="required-star">*</span></label>
                        <div class="select-wrapper input-with-icon">
                            <select id="C_ID" name="C_ID" class="info-value-box input-field select-field" required>
                                <option value="" disabled selected hidden>انتخاب کلاس...</option>
                                <?php if (!empty($classList)): ?>
                                    <?php foreach ($classList as $cls): ?>
                                        <option value="<?php echo $cls['C_ID']; ?>">
                                            <?php echo "پایه " . $cls['C_grade'] . " - " . $cls['C_major']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="" disabled>کلاسی برای شما ثبت نشده است</option>
                                <?php endif; ?>
                            </select>
                            <span class="field-icon-box">
                                <svg viewBox="0 0 24 24" class="inline-svg">
                                    <path
                                        d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z" />
                                </svg>
                            </span>
                        </div>
                    </div>

                    <div class="info-item">
                        <label for="G_courseID">انتخاب درس<span class="required-star">*</span></label>
                        <div class="select-wrapper input-with-icon">
                            <select id="G_courseID" name="G_courseID" class="info-value-box input-field select-field"
                                required disabled>
                                <option value="" disabled selected hidden>ابتدا کلاس را انتخاب کنید...</option>
                            </select>
                            <span class="field-icon-box">
                                <svg viewBox="0 0 24 24" class="inline-svg">
                                    <path
                                        d="M18 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zM6 4h5v8l-2.5-1.5L6 12V4z" />
                                </svg>
                            </span>
                        </div>
                    </div>

                    <div class="info-item">
                        <label for="G_term">انتخاب دوره<span class="required-star">*</span></label>
                        <div class="select-wrapper input-with-icon">
                            <select id="G_term" name="G_term" class="info-value-box input-field select-field" required>
                                <option value="" disabled selected hidden>انتخاب دوره...</option>
                                <option value="1">مهر و آبان</option>
                                <option value="2">آذر</option>
                                <option value="3">نوبت اول</option>
                                <option value="4">اسفند</option>
                                <option value="5">فروردین و اردیبهشت</option>
                                <option value="6">نوبت دوم</option>
                            </select>
                            <span class="field-icon-box">
                                <svg viewBox="0 0 24 24" class="inline-svg">
                                    <path
                                        d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11z" />
                                </svg>
                            </span>
                        </div>
                    </div>
                </div>
            </section>

            <section class="profile-card margin-top-card">
                <div class="list-header-flex">
                    <h2 class="list-main-title">لیست دانش‌آموزان</h2>
                    <div class="student-count-badge">
                        تعداد: <span id="student_count_num">0</span> نفر
                    </div>
                </div>

                <div id="students_container" class="students-table-wrapper">
                    <p class="empty-msg">لطفاً کلاس، درس و دوره را انتخاب کنید.</p>
                </div>

                <div class="profile-actions-footer register-actions">
                    <button type="submit" class="btn-back-home btn-submit-register">
                        ثبت نهایی نمرات
                    </button>
                </div>
            </section>
        </form>
    </main>

    <script>
        $(document).ready(function () {
            
            <?php if (isset($_SESSION['add_score'])): ?>
                Swal.fire({
                    icon: 'success',
                    title: 'موفقیت‌آمیز',
                    text: 'نمرات دانش‌آموزان با موفقیت ثبت شد',
                    confirmButtonText: 'باشه'
                });
                <?php unset($_SESSION['add_score']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['score_error'])): ?>
                Swal.fire({
                    icon: 'warning',
                    title: 'خطای نمره',
                    text: 'نمره باید بین 0 تا 20 وارد شود',
                    confirmButtonText: 'متوجه شدم'
                });
                <?php unset($_SESSION['score_error']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['send_error'])): ?>
                Swal.fire({
                    icon: 'error',
                    title: 'خطا',
                    text: 'خطا در ثبت اطلاعات. لطفا دوباره امتحان کنید',
                    confirmButtonText: 'تلاش مجدد'
                });
                <?php unset($_SESSION['send_error']); ?>
            <?php endif; ?>

            function loadStudents() {
                var classID = $('#C_ID').val();
                var courseID = $('#G_courseID').val();
                var term = $('#G_term').val();

                if (classID && courseID && term) {
                    $.ajax({
                        url: '../get_grade_data.php',
                        type: 'POST',
                        data: { 
                            action: 'get_students', 
                            class_id: classID,
                            course_id: courseID,
                            term: term
                        },
                        dataType: 'html',
                        success: function (htmlResponse) {
                            $('#students_container').html(htmlResponse);
                            var totalStudents = $('#students_container .student-row').length;
                            $('#student_count_num').text(totalStudents);
                        }
                    });
                }
            }

            $('#C_ID').on('change', function () {
                var classID = $(this).val();

                if (classID) {
                    $.ajax({
                        url: '../get_grade_data.php',
                        type: 'POST',
                        data: { action: 'get_teacher_courses', class_id: classID },
                        dataType: 'json',
                        success: function (courses) {
                            var courseSelect = $('#G_courseID');
                            courseSelect.empty();
                            courseSelect.append('<option value="" disabled selected hidden>انتخاب درس...</option>');

                            if (courses && courses.length > 0) {
                                $.each(courses, function (index, course) {
                                    courseSelect.append('<option value="' + course.Co_ID + '">' + course.Co_name + '</option>');
                                });
                                courseSelect.prop('disabled', false);
                            } else {
                                courseSelect.append('<option value="" disabled>درسی برای شما در این کلاس ثبت نشده است</option>');
                                courseSelect.prop('disabled', true);
                            }
                            $('#students_container').html('<p class="empty-msg">لطفاً درس و دوره را انتخاب کنید.</p>');
                            $('#student_count_num').text(0);
                        }
                    });
                }
            });

            $('#G_courseID, #G_term').on('change', function () {
                loadStudents();
            });
        });
    </script>
    <script src="../js/theme.js"></script>
</body>
</html>
