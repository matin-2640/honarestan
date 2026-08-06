<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include("connect.php");

if (!(isset($_SESSION["state_login"]) && $_SESSION["type"] == 2 || $_SESSION["type"] == 3) || $_SESSION["type"] == 4) {
    header("location:login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تغییر رمز عبور| پورتال هنرستان</title>
    <link rel="stylesheet" href="js/sweetalert2.min.css">

    <link rel="stylesheet" href="styles/panel_style.css" />
    <link rel="stylesheet" href="styles/profile_style.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/vazirmatn-font-face.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="https://unpkg.com/persian-datepicker@1.2.0/dist/css/persian-datepicker.min.css" />
    <link rel="stylesheet" href="styles/attendance_style.css">
    <link rel="stylesheet" href="styles/font.css">
    <link rel="icon" href="images/icons/rahdanesh.png">
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
                    <small>مدیریت حضور و غیاب</small>
                </div>
            </div>

            <nav class="panel-nav" id="panelNav">
                <a href="admin_panel.php">
                    <svg viewBox="0 0 24 24" class="nav-svg-icon">
                        <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z" />
                    </svg>
                    صفحه نخست
                </a>
                <a href="#" class="active">
                    <svg viewBox="0 0 24 24" class="nav-svg-icon">
                        <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z" />
                    </svg>
                    حضور و غیاب
                </a>
                <a href="admin_panel.php" class="back-link-btn">
                    <svg viewBox="0 0 24 24" class="nav-svg-icon">
                        <path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z" />
                    </svg>
                    بازگشت
                </a>
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

    <div class="main-container">

        <div class="filter-card">
            <div style="font-size: 18px; font-weight: bold; margin-bottom: 5px;">
                <i class="fa-solid fa-clipboard-user" style="color: var(--primary-blue);"></i> مدیریت حضور و غیاب
            </div>

            <form id="filterForm" style="display: flex; flex-direction: column; gap: 12px;">

                <div class="filter-group">
                    <label><i class="fa-solid fa-users"></i> انتخاب کلاس:</label>
                    <select id="class_id" class="form-input" required onchange="loadCourses(this.value)">
                        <option value="">-- انتخاب کنید --</option>
                        <?php
                        $stmt = $connect->query("SELECT C_ID, C_Grade, C_Major FROM Classes ORDER BY C_Grade ASC");
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            $className = "پایه " . $row['C_Grade'] . " " . $row['C_Major'];
                            echo "<option value='{$row['C_ID']}'>{$className}</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label><i class="fa-solid fa-book-open"></i> انتخاب درس:</label>
                    <select id="course_id" class="form-input" required>
                        <option value="">ابتدا کلاس را انتخاب کنید</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label><i class="fa-solid fa-calendar-days"></i> انتخاب تاریخ:</label>
                    <input type="text" id="pdate" class="form-input pdate-input" placeholder="انتخاب تاریخ..." required
                        autocomplete="off">
                </div>

                <button type="button" class="btn-submit-main" style="margin-top: 5px;" onclick="fetchStudents()">
                    <i class="fa-solid fa-magnifying-glass"></i> نمایش لیست دانش‌آموزان
                </button>

            </form>
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
            <a href="admin_panel.php" id="smsParentBtn" class="btn-view-link">
                بازگشت به پنل مدیریت
            </a>
        </div>

        <div id="studentsCard" style="display: none; flex-direction: column; gap: 12px;">
            <form id="attendanceForm" action="save_attendance.php" method="POST">
                <input type="hidden" name="course_id" id="h_course_id">
                <input type="hidden" name="a_date" id="h_a_date">

                <div class="section-header">
                    <div class="section-title">لیست دانش‌آموزان</div>

                    <div class="summary-mini-card">
                        <span class="summary-item total">کل: <span id="studentCount">0</span></span>
                        <span>|</span>
                        <span class="summary-item present"><i class="fa-solid fa-check"></i> حاضر: <span
                                id="presentCount">0</span></span>
                        <span>|</span>
                        <span class="summary-item absent"><i class="fa-solid fa-xmark"></i> غایب: <span
                                id="absentCount">0</span></span>
                    </div>
                </div>

                <div class="students-list" id="studentsList"></div>

                <button type="submit" class="btn-submit-main"
                    style="background-color: var(--primary-blue); position: sticky; bottom: 15px;">
                    <i class="fa-solid fa-floppy-disk"></i> ذخیره حضور و غیاب
                </button>
            </form>
        </div>

    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/sweetalert2.min.js"></script>
    <script src="https://unpkg.com/persian-date@1.1.0/dist/persian-date.min.js"></script>
    <script src="https://unpkg.com/persian-datepicker@1.2.0/dist/js/persian-datepicker.min.js"></script>
    <script src="https://unpkg.com/lenis@1.3.11/dist/lenis.min.js"></script>
    <script type="text/javascript" src="js/theme.js"></script>

    <script>
        $(document).ready(function () {
            // تقویم شمسی
            $('.pdate-input').pDatepicker({
                format: 'YYYY/MM/DD',
                autoClose: true,
                initialValue: true,
                calendar: {
                    persian: { locale: 'fa' }
                }
            });

            // به‌روزرسانی آمار زنده هنگام تغییر رادیوباتن‌ها
            $(document).on('change', '.opt-btn', function () {
                updateLiveStats();
            });
        });

        // دریافت لیست دروس با تغییر کلاس
        function loadCourses(classId) {
            if (!classId) {
                $('#course_id').html('<option value="">ابتدا کلاس را انتخاب کنید</option>');
                return;
            }

            $('#course_id').html('<option value="">در حال دریافت دروس...</option>');

            $.post('get_courses_ajax.php', { class_id: classId }, function (data) {
                $('#course_id').html(data);
            });
        }

        // دریافت لیست دانش‌آموزان
        function fetchStudents() {
            var classId = $('#class_id').val();
            var courseId = $('#course_id').val();
            var date = $('#pdate').val();

            // اصلاح عملگرهای منطقی || 
            // بررسی کامل بودن اطلاعات
            if (!classId || !courseId || !date) {

                Swal.fire({
                    icon: 'warning',
                    title: 'تکمیل اطلاعات',
                    text: 'لطفاً کلاس، درس و تاریخ را به طور کامل انتخاب کنید.',
                    confirmButtonText: 'متوجه شدم',
                    confirmButtonColor: '#2563eb',
                    customClass: {
                        popup: 'my-custom-popup',
                        title: 'my-custom-title',
                        htmlContainer: 'my-custom-html',
                        confirmButton: 'my-custom-confirm-btn'
                    }
                });

                return; // ادامه کد اجرا نشود
            }

            $('#h_course_id').val(courseId);
            $('#h_a_date').val(date);

            $.post('get_students_ajax.php', { class_id: classId, course_id: courseId, date: date }, function (response) {
                var res = JSON.parse(response);
                $('#studentsList').html(res.html);
                $('#studentCount').text(res.count);
                $('#studentsCard').css('display', 'flex');

                updateLiveStats();
            });
        }

        // آمار زنده حاضرین و غایبین
        function updateLiveStats() {
            var present = $('.opt-btn[value="1"]:checked').length;
            var absent = $('.opt-btn[value="0"]:checked').length;

            $('#presentCount').text(present);
            $('#absentCount').text(absent);
        }
    </script>

</body>

</html>