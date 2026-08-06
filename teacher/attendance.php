<?php
session_start();
if (!(isset($_SESSION["state_login"]) && $_SESSION["type"] == 1)) {
    header("location:login.php");
    exit();
}

include("../connect.php");

$teacher_id = $_SESSION["ID"] ?? 0;

try {
    // دریافت کلاس‌هایی که این معلم در آن‌ها درس دارد
    $stmt_classes = $connect->prepare("
        SELECT DISTINCT c.C_ID, c.C_grade, c.C_major 
        FROM courses co
        JOIN classes c ON co.Co_classID = c.C_ID
        WHERE co.Co_teacherID = ?
        ORDER BY c.C_grade ASC
    ");
    $stmt_classes->execute([$teacher_id]);
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
    <title>حضور و غیاب | پنل معلم</title>
    <link rel="stylesheet" href="../styles/font.css">
    <link rel="icon" href="../images/icons/rahdanesh.png">
    <link rel="stylesheet" href="../styles/score_style.css">
    <link rel="stylesheet" href="https://unpkg.com/persian-datepicker@1.2.0/dist/css/persian-datepicker.min.css">

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
                    <small>سیستم حضور و غیاب دانش‌آموزان</small>
                </div>
            </div>

            <nav class="panel-nav" id="panelNav">
                <a href="../teacher_panel.php">صفحه نخست</a>
                <a href="#" class="active">حضور و غیاب</a>
            </nav>
        </div>
    </header>

    <main class="panel-container profile-layout">
        <form action="save_teacher_attendance.php" method="POST" id="attendanceForm" class="register-form">
            <section class="profile-card">
                <div class="profile-card-header">
                    <h2>ثبت حضور و غیاب</h2>
                    <p>کلاس، درس، تاریخ و زمان را انتخاب کنید تا لیست دانش‌آموزان نمایش داده شود.</p>
                </div>

                <div class="profile-info-grid">
                    <div class="info-item">
                        <label for="select_class_id">انتخاب کلاس<span class="required-star">*</span></label>
                        <div class="select-wrapper input-with-icon">
                            <select id="select_class_id" class="info-value-box input-field select-field" required>
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
                        </div>
                    </div>

                    <div class="info-item">
                        <label for="G_courseID">انتخاب درس<span class="required-star">*</span></label>
                        <div class="select-wrapper input-with-icon">
                            <select id="G_courseID" name="G_courseID" class="info-value-box input-field select-field"
                                required disabled>
                                <option value="" disabled selected hidden>ابتدا کلاس را انتخاب کنید...</option>
                            </select>
                        </div>
                    </div>

                    <div class="info-item">
                        <label for="attendance_date">تاریخ حضور و غیاب (شمسی)<span
                                class="required-star">*</span></label>
                        <div class="input-with-icon">
                            <input type="text" id="attendance_date" name="date" class="info-value-box input-field"
                                placeholder="1405/01/01" autocomplete="off" required />
                        </div>
                    </div>

                    <div class="info-item" id="time_type_container" style="display: none;">
                        <label for="A_type">زمان حضور و غیاب<span class="required-star">*</span></label>
                        <div class="select-wrapper input-with-icon">
                            <select id="A_type" name="A_type" class="info-value-box input-field select-field">
                                <option value="1">اول زنگ</option>
                                <option value="2">آخر زنگ</option>
                            </select>
                        </div>
                    </div>
                </div>
            </section>

            <section class="profile-card margin-top-card">
                <div class="list-header-flex">
                    <h2>لیست دانش‌آموزان</h2>
                    <div class="student-count-badge">تعداد غایبین انتخاب شده: <span id="absent_count_num">0</span> نفر
                    </div>
                </div>

                <div id="students_container" class="students-table-wrapper">
                    <p class="empty-msg">لطفاً کلاس، درس و تاریخ را انتخاب کنید.</p>
                </div>

                <div class="profile-actions-footer register-actions">
                    <button type="submit" class="btn-back-home btn-submit-register">ذخیره حضور و غیاب</button>
                </div>
            </section>
        </form>
    </main>

    <script>
        var courseTypes = {};

        $(document).ready(function () {

            $('#attendance_date').persianDatepicker({ format: 'YYYY/MM/DD', persianNumbers: true });

            <?php if (isset($_SESSION['attendance_success'])): ?>
                Swal.fire({ icon: 'success', title: 'موفقیت‌آمیز', text: 'حضور و غیاب با موفقیت ثبت شد', confirmButtonText: 'باشه' });
                <?php unset($_SESSION['attendance_success']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['attendance_error'])): ?>
                Swal.fire({ icon: 'error', title: 'خطا', text: 'خطا در ذخیره اطلاعات، لطفا دوباره تلاش کنید', confirmButtonText: 'متوجه شدم' });
                <?php unset($_SESSION['attendance_error']); ?>
            <?php endif; ?>

            function loadStudents() {
                var classID = $('#select_class_id').val();
                var courseID = $('#G_courseID').val();
                var date = $('#attendance_date').val();
                var type = $('#A_type').val() || 1;

                if (classID && courseID && date) {
                    $.ajax({
                        url: 'get_teacher_students.php',
                        type: 'POST',
                        data: {
                            class_id: classID,
                            course_id: courseID,
                            date: date,
                            type: type
                        },
                        dataType: 'html',
                        success: function (htmlResponse) {
                            $('#students_container').html(htmlResponse);
                            updateAbsentCount();
                        }
                    });
                }
            }

            $(document).on('change', 'input[name="absent_students[]"]', function () {
                updateAbsentCount();
            });

            function updateAbsentCount() {
                var totalAbsent = $('input[name="absent_students[]"]:checked').length;
                $('#absent_count_num').text(totalAbsent);
            }

            $('#select_class_id').on('change', function () {
                var classID = $(this).val();

                if (classID) {
                    $.ajax({
                        url: 'get_teacher_courses.php',
                        type: 'POST',
                        data: { class_id: classID },
                        dataType: 'json',
                        success: function (courses) {
                            var courseSelect = $('#G_courseID');
                            courseSelect.empty();
                            courseSelect.append('<option value="" disabled selected hidden>انتخاب درس...</option>');
                            courseTypes = {};

                            if (courses && courses.length > 0) {
                                $.each(courses, function (index, course) {
                                    courseSelect.append('<option value="' + course.Co_ID + '">' + course.Co_name + '</option>');
                                    courseTypes[course.Co_ID] = course.Co_type;
                                });
                                courseSelect.prop('disabled', false);
                            } else {
                                courseSelect.append('<option value="" disabled>درسی برای شما در این کلاس ثبت نشده است</option>');
                                courseSelect.prop('disabled', true);
                            }
                            $('#students_container').html('<p class="empty-msg">لطفاً درس و تاریخ را انتخاب کنید.</p>');
                            $('#time_type_container').hide();
                        }
                    });
                }
            });

            $('#G_courseID').on('change', function () {
                var courseID = $(this).val();
                var cType = courseTypes[courseID];

                if (cType == "0") {
                    // پودمانی: نمایش زمان (اول زنگ / آخر زنگ)
                    $('#time_type_container').show();
                    $('#A_type').val("1");
                } else {
                    // غیر پودمانی: مخفی کردن زمان
                    $('#time_type_container').hide();
                    $('#A_type').val("1");
                }

                loadStudents();
            });

            $('#attendance_date, #A_type').on('change', function () {
                loadStudents();
            });
        });
    </script>
    <script src="https://unpkg.com/persian-date@1.1.0/dist/persian-date.min.js"></script>
    <script src="https://unpkg.com/persian-datepicker@1.2.0/dist/js/persian-datepicker.min.js"></script>
    <script src="../js/theme.js"></script>
</body>

</html>