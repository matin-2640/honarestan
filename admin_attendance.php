<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include("connect.php");

if (!(isset($_SESSION["state_login"]) && $_SESSION["type"] <= 2)) {
    header("location:login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ثبت حضور و غیاب</title>
    <link rel="stylesheet" href="js/sweetalert2.min.css">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/vazirmatn-font-face.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="https://unpkg.com/persian-datepicker@1.2.0/dist/css/persian-datepicker.min.css" />
    <link rel="stylesheet" href="styles/attendance_style.css">
</head>

<body>

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
