<?php
session_start();

// بررسی لاگین بودن معلم
if (!(isset($_SESSION["state_login"]) && $_SESSION["type"] == 1)) {
    header("location:../login.php");
    exit();
}

// تنظیم منطقه زمانی و فراخوانی فایل‌های ضروری
date_default_timezone_set('Asia/Tehran');
require_once '../connect.php';
include_once 'jdf.php';

$teacher_id = $_SESSION['ID'];
$today_jalali = jdate('Y/m/d');

// -----------------------------------------------------------------------------
// هندلر پردازش‌های Ajax (افزودن، ویرایش، حذف، دریافت اطلاعات تعاملی)
// -----------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json; charset=utf-8');

    // 1. دریافت لیست دروس یک کلاس برای معلم جاری
    if ($_POST['action'] === 'get_courses') {
        $class_id = intval($_POST['class_id'] ?? 0);
        $stmt = $connect->prepare("SELECT Co_ID, Co_name FROM courses WHERE Co_classID = :class_id AND Co_teacherID = :teacher_id");
        $stmt->execute([':class_id' => $class_id, ':teacher_id' => $teacher_id]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit();
    }

    // 2. دریافت لیست هنرجویان یک کلاس
    if ($_POST['action'] === 'get_students') {
        $class_id = intval($_POST['class_id'] ?? 0);
        $stmt = $connect->prepare("
            SELECT DISTINCT s.Stu_ID, s.Stu_fullName 
            FROM students s 
            INNER JOIN courses co ON s.Stu_classID = co.Co_classID 
            WHERE s.Stu_classID = :class_id AND co.Co_teacherID = :teacher_id 
            ORDER BY s.Stu_fullName ASC
        ");
        $stmt->execute([':class_id' => $class_id, ':teacher_id' => $teacher_id]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit();
    }

    // 3. ثبت رویداد انضباطی جدید در جدول Teacher_disciplinary
    if ($_POST['action'] === 'add_disciplinary') {
        $student_id = intval($_POST['student_id'] ?? 0);
        $course_id = intval($_POST['course_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $incident_date = trim($_POST['incident_date'] ?? '');
        $incident_time = trim($_POST['incident_time'] ?? '');
        $description = mb_substr(trim($_POST['description'] ?? ''), 0, 200, 'UTF-8');

        if (empty($student_id) || empty($course_id) || empty($title) || empty($incident_date) || empty($incident_time)) {
            echo json_encode(['status' => 'error', 'message' => 'لطفاً تمام فیلدهای ضروری را تکمیل کنید.']);
            exit();
        }

        try {
            $stmt = $connect->prepare("
                INSERT INTO Teacher_disciplinary 
                (student_id, title, incident_date, incident_time, description, course_id, teacher_id, created_at) 
                VALUES (:student_id, :title, :incident_date, :incident_time, :description, :course_id, :teacher_id, NOW())
            ");
            $stmt->execute([
                ':student_id' => $student_id,
                ':title' => $title,
                ':incident_date' => $incident_date,
                ':incident_time' => $incident_time,
                ':description' => $description,
                ':course_id' => $course_id,
                ':teacher_id' => $teacher_id
            ]);
            echo json_encode(['status' => 'success', 'message' => 'رویداد انضباطی با موفقیت ثبت شد.']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'خطا در ثبت اطلاعات: ' . $e->getMessage()]);
        }
        exit();
    }

    // 4. ویرایش رویداد انضباطی
    if ($_POST['action'] === 'edit_disciplinary') {
        $record_id = intval($_POST['record_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $incident_date = trim($_POST['incident_date'] ?? '');
        $incident_time = trim($_POST['incident_time'] ?? '');
        $description = mb_substr(trim($_POST['description'] ?? ''), 0, 200, 'UTF-8');

        if (empty($record_id) || empty($title) || empty($incident_date) || empty($incident_time)) {
            echo json_encode(['status' => 'error', 'message' => 'ورودی‌ها معتبر نمی‌باشند.']);
            exit();
        }

        try {
            $stmt = $connect->prepare("
                UPDATE Teacher_disciplinary 
                SET title = :title, incident_date = :incident_date, incident_time = :incident_time, description = :description 
                WHERE id = :id AND teacher_id = :teacher_id
            ");
            $stmt->execute([
                ':title' => $title,
                ':incident_date' => $incident_date,
                ':incident_time' => $incident_time,
                ':description' => $description,
                ':id' => $record_id,
                ':teacher_id' => $teacher_id
            ]);
            echo json_encode(['status' => 'success', 'message' => 'بروزرسانی با موفقیت انجام شد.']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'خطا در ویرایش: ' . $e->getMessage()]);
        }
        exit();
    }

    // 5. حذف رویداد انضباطی
    if ($_POST['action'] === 'delete_disciplinary') {
        $record_id = intval($_POST['record_id'] ?? 0);

        $stmt = $connect->prepare("DELETE FROM Teacher_disciplinary WHERE id = :id AND teacher_id = :teacher_id");
        $stmt->execute([':id' => $record_id, ':teacher_id' => $teacher_id]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['status' => 'success', 'message' => 'مورد انضباطی با موفقیت حذف شد.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'شناسه نامعتبر است یا شما مجاز به حذف این مورد نیستید.']);
        }
        exit();
    }
}

// -----------------------------------------------------------------------------
// استخراج اولیه اطلاعات جهت رندر HTML
// -----------------------------------------------------------------------------

// دریافت کلاس‌های مربوط به معلم جاری
$stmt_classes = $connect->prepare("
    SELECT DISTINCT c.C_ID, c.C_grade, c.C_major 
    FROM classes c 
    INNER JOIN courses co ON c.C_ID = co.Co_classID 
    WHERE co.Co_teacherID = :teacher_id
    ORDER BY c.C_grade ASC
");
$stmt_classes->execute([':teacher_id' => $teacher_id]);
$teacher_classes = $stmt_classes->fetchAll(PDO::FETCH_ASSOC);

// دریافت لیست رویدادهای انضباطی از Teacher_disciplinary همراه با نام معلم و نام درس
$stmt_records = $connect->prepare("
    SELECT 
        td.id, td.title, td.incident_date, td.incident_time, td.description, td.created_at,
        s.Stu_fullName, s.Stu_classID,
        c.C_grade, c.C_major,
        co.Co_name AS course_name,
        t.T_fullName AS teacher_name
    FROM Teacher_disciplinary td
    INNER JOIN students s ON td.student_id = s.Stu_ID
    INNER JOIN classes c ON s.Stu_classID = c.C_ID
    INNER JOIN courses co ON td.course_id = co.Co_ID
    INNER JOIN teachers t ON td.teacher_id = t.T_ID
    WHERE td.teacher_id = :teacher_id
    ORDER BY td.id DESC
");
$stmt_records->execute([':teacher_id' => $teacher_id]);
$records = $stmt_records->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ثبت و مدیریت رویداد انضباطی</title>

    <link rel="stylesheet" href="../styles/font.css">
    <link rel="stylesheet" href="../styles/teacher_disciplinary.css">
    <link rel="stylesheet" href="../js/sweetalert2.min.css">
    <link rel="stylesheet" href="../js/jalali-datepicker.min.css">
    <link rel="icon" href="images/icons/rahdanesh.png">
</head>

<body>

    <div class="main-container">

        <!-- کارت فرم ثبت رویداد انضباطی -->
        <div class="card-box">
            <div class="card-header">
                <h2>ثبت رویداد انضباطی جدید</h2>
            </div>
            <div class="card-body">
                <form id="addDisciplinaryForm">
                    <input type="hidden" name="action" value="add_disciplinary">

                    <div class="form-row">
                        <!-- انتخاب کلاس -->
                        <div class="form-group col-md-4">
                            <label for="class_select">انتخاب کلاس <span class="required">*</span></label>
                            <select id="class_select" name="class_id" class="form-control" required>
                                <option value="">-- انتخاب کنید --</option>
                                <?php foreach ($teacher_classes as $cls): ?>
                                    <option value="<?= $cls['C_ID']; ?>">
                                        کلاس <?= htmlspecialchars($cls['C_grade'] . ' - ' . $cls['C_major']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- انتخاب درس -->
                        <div class="form-group col-md-4">
                            <label for="course_select">انتخاب درس <span class="required">*</span></label>
                            <select id="course_select" name="course_id" class="form-control" required disabled>
                                <option value="">-- ابتدا کلاس را انتخاب کنید --</option>
                            </select>
                        </div>

                        <!-- انتخاب هنرجو -->
                        <div class="form-group col-md-4">
                            <label for="student_select">انتخاب هنرجو <span class="required">*</span></label>
                            <select id="student_select" name="student_id" class="form-control" required disabled>
                                <option value="">-- ابتدا کلاس را انتخاب کنید --</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <!-- عنوان رویداد -->
                        <div class="form-group col-md-6">
                            <label for="title">عنوان رویداد <span class="required">*</span></label>
                            <input type="text" id="title" name="title" class="form-control"
                                placeholder="مثال: تاخیر ورود به کلاس" required>
                        </div>

                        <!-- تاریخ رویداد -->
                        <div class="form-group col-md-3">
                            <label for="incident_date">تاریخ وقوع <span class="required">*</span></label>
                            <input type="text" id="incident_date" name="incident_date" class="form-control" data-jdp
                                placeholder="YYYY/MM/DD" value="<?= $today_jalali; ?>" autocomplete="off" required>
                        </div>

                        <!-- ساعت رویداد -->
                        <div class="form-group col-md-3">
                            <label for="incident_time">ساعت وقوع <span class="required">*</span></label>
                            <input type="time" id="incident_time" name="incident_time" class="form-control"
                                value="<?= date('H:i'); ?>" required>
                        </div>
                    </div>

                    <!-- توضیحات -->
                    <div class="form-group">
                        <label for="description">توضیحات و شرح رویداد</label>
                        <textarea id="description" name="description" class="form-control" rows="4" maxlength="200"
                            placeholder="توضیحات تکمیلی را در صورت نیاز وارد کنید..."></textarea>
                        <small class="form-text text-muted">حداکثر ۲۰۰ کاراکتر</small>
                    </div>

                    <div class="form-actions">
                        <button type="submit" id="btnSubmit" class="btn btn-primary">
                            <i class="icon-save"></i> ثبت رویداد انضباطی
                        </button>
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
                            margin-top: 10px;
                            max-width: 160px;
                        }
                    </style>
                    <a href="../teacher_panel.php" id="smsParentBtn" class="btn-view-link">
                        بازگشت به پنل معلم
                    </a>
                </form>
            </div>
        </div>

        <!-- بخش فیلتر و لیست کارت‌های ثبت‌شده -->
        <div class="records-section">
            <div class="section-header">
                <h3>رویدادهای انضباطی ثبت‌شده</h3>
                <div class="filter-box">
                    <label for="filter_class">فیلتر بر اساس کلاس:</label>
                    <select id="filter_class" class="form-control-sm">
                        <option value="all">همه کلاس‌ها</option>
                        <?php foreach ($teacher_classes as $cls): ?>
                            <option value="<?= $cls['C_ID']; ?>">
                                کلاس <?= htmlspecialchars($cls['C_grade'] . ' - ' . $cls['C_major']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="cards-grid" id="recordsContainer">
                <?php if (empty($records)): ?>
                    <div class="no-data">هیچ رویداد انضباطی ثبت نشده است.</div>
                <?php else: ?>
                    <?php foreach ($records as $rec): ?>
                        <div class="note-box" data-class-id="<?= $rec['Stu_classID']; ?>">
                            <div class="note-header">
                                <span class="student-name"><?= htmlspecialchars($rec['Stu_fullName']); ?></span>
                                <span
                                    class="badge-class"><?= htmlspecialchars($rec['C_grade'] . ' - ' . $rec['C_major']); ?></span>
                            </div>
                            <div class="note-title"><?= htmlspecialchars($rec['title']); ?></div>

                            <!-- نمایش نام معلم و نام درس مربوطه -->
                            <div class="note-info-tags" style="margin-bottom: 8px; font-size: 0.85rem; color: var(--primary);">
                                <span>📌 درس: <strong><?= htmlspecialchars($rec['course_name']); ?></strong></span> |
                                <span>👨‍🏫 ثبت‌کننده: <strong><?= htmlspecialchars($rec['teacher_name']); ?></strong></span>
                            </div>

                            <div class="note-meta">
                                <span>📅 <?= htmlspecialchars($rec['incident_date']); ?></span>
                                <span>⏰ <?= htmlspecialchars($rec['incident_time']); ?></span>
                            </div>
                            <div class="note-body">
                                <?= nl2br(htmlspecialchars($rec['description'])); ?>
                            </div>
                            <div class="note-actions">
                                <button type="button" class="btn-sm btn-edit btn-trigger-edit" data-id="<?= $rec['id']; ?>"
                                    data-title="<?= htmlspecialchars($rec['title']); ?>"
                                    data-date="<?= htmlspecialchars($rec['incident_date']); ?>"
                                    data-time="<?= htmlspecialchars($rec['incident_time']); ?>"
                                    data-desc="<?= htmlspecialchars($rec['description']); ?>">
                                    ویرایش
                                </button>
                                <button type="button" class="btn-sm btn-delete btn-trigger-delete" data-id="<?= $rec['id']; ?>">
                                    حذف
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <!-- مدال اختصاصی جهت ویرایش -->
    <div class="modal-overlay" id="editModal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h4>ویرایش رویداد انضباطی</h4>
                <span class="modal-close" id="btnCloseModal">&times;</span>
            </div>
            <form id="editDisciplinaryForm">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit_disciplinary">
                    <input type="hidden" name="record_id" id="edit_record_id">

                    <div class="form-group">
                        <label for="edit_title">عنوان رویداد <span class="required">*</span></label>
                        <input type="text" id="edit_title" name="title" class="form-control" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="edit_incident_date">تاریخ وقوع <span class="required">*</span></label>
                            <input type="text" id="edit_incident_date" name="incident_date" class="form-control"
                                data-jdp autocomplete="off" required>
                        </div>

                        <div class="form-group col-md-6">
                            <label for="edit_incident_time">ساعت وقوع <span class="required">*</span></label>
                            <input type="time" id="edit_incident_time" name="incident_time" class="form-control"
                                required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="edit_description">توضیحات</label>
                        <textarea id="edit_description" name="description" class="form-control" rows="4"
                            maxlength="200"></textarea>
                        <small class="form-text text-muted">حداکثر ۲۰۰ کاراکتر</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="btnCancelModal">انصراف</button>
                    <button type="submit" class="btn btn-success">ذخیره تغییرات</button>
                </div>
            </form>

        </div>
    </div>

    <script src="../js/jquery-1.10.2.min.js"></script>
    <script src="../js/sweetalert2.min.js"></script>
    <script src="../js/theme.js"></script>
    <script src="../js/jalali-datepicker.min.js"></script>

    <script>
        $(document).ready(function () {

            jalaliDatepicker.startWatch({
                minDate: "attr",
                maxDate: "attr"
            });

            $('#class_select').on('change', function () {
                var classId = $(this).val();
                var $courseSelect = $('#course_select');
                var $studentSelect = $('#student_select');

                $courseSelect.html('<option value="">در حال دریافت...</option>').prop('disabled', true);
                $studentSelect.html('<option value="">در حال دریافت...</option>').prop('disabled', true);

                if (!classId) {
                    $courseSelect.html('<option value="">-- ابتدا کلاس را انتخاب کنید --</option>');
                    $studentSelect.html('<option value="">-- ابتدا کلاس را انتخاب کنید --</option>');
                    return;
                }

                $.ajax({
                    url: 'add_disciplinary.php',
                    type: 'POST',
                    data: { action: 'get_courses', class_id: classId },
                    dataType: 'json',
                    success: function (response) {
                        var options = '<option value="">-- انتخاب درس --</option>';
                        if (response.length > 0) {
                            $.each(response, function (i, item) {
                                options += '<option value="' + item.Co_ID + '">' + item.Co_name + '</option>';
                            });
                            $courseSelect.html(options).prop('disabled', false);
                        } else {
                            $courseSelect.html('<option value="">درسی برای این کلاس یافت نشد</option>');
                        }
                    }
                });

                $.ajax({
                    url: 'add_disciplinary.php',
                    type: 'POST',
                    data: { action: 'get_students', class_id: classId },
                    dataType: 'json',
                    success: function (response) {
                        var options = '<option value="">-- انتخاب هنرجو --</option>';
                        if (response.length > 0) {
                            $.each(response, function (i, item) {
                                options += '<option value="' + item.Stu_ID + '">' + item.Stu_fullName + '</option>';
                            });
                            $studentSelect.html(options).prop('disabled', false);
                        } else {
                            $studentSelect.html('<option value="">هنرجویی در این کلاس یافت نشد</option>');
                        }
                    }
                });
            });

            $('#addDisciplinaryForm').on('submit', function (e) {
                e.preventDefault();
                var $btn = $('#btnSubmit');
                $btn.prop('disabled', true).text('در حال ثبت...');

                $.ajax({
                    url: 'add_disciplinary.php',
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function (res) {
                        $btn.prop('disabled', false).html('<i class="icon-save"></i> ثبت رویداد انضباطی');
                        if (res.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'موفقیت‌آمیز',
                                text: res.message,
                                confirmButtonText: 'باشه'
                            }).then(function () {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'خطا',
                                text: res.message,
                                confirmButtonText: 'متوجه شدم'
                            });
                        }
                    },
                    error: function () {
                        $btn.prop('disabled', false).html('<i class="icon-save"></i> ثبت رویداد انضباطی');
                        Swal.fire('خطا', 'برقراری ارتباط با سرور با خطا مواجه شد.', 'error');
                    }
                });
            });

            $('#filter_class').on('change', function () {
                var selectedClass = $(this).val();
                if (selectedClass === 'all') {
                    $('.note-box').show();
                } else {
                    $('.note-box').hide();
                    $('.note-box[data-class-id="' + selectedClass + '"]').show();
                }
            });

            $(document).on('click', '.btn-trigger-edit', function () {
                var id = $(this).data('id');
                var title = $(this).data('title');
                var date = $(this).data('date');
                var time = $(this).data('time');
                var desc = $(this).data('desc');

                $('#edit_record_id').val(id);
                $('#edit_title').val(title);
                $('#edit_incident_date').val(date);
                $('#edit_incident_time').val(time);
                $('#edit_description').val(desc);

                $('#editModal').fadeIn(200);
            });

            $('#btnCloseModal, #btnCancelModal').on('click', function () {
                $('#editModal').fadeOut(200);
            });

            $('#editDisciplinaryForm').on('submit', function (e) {
                e.preventDefault();
                $.ajax({
                    url: 'add_disciplinary.php',
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function (res) {
                        if (res.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'ویرایش شد',
                                text: res.message,
                                confirmButtonText: 'باشه'
                            }).then(function () {
                                location.reload();
                            });
                        } else {
                            Swal.fire('خطا', res.message, 'error');
                        }
                    }
                });
            });

            $(document).on('click', '.btn-trigger-delete', function () {
                var recordId = $(this).data('id');

                Swal.fire({
                    title: 'آیا از حذف این رویداد اطمینان دارید؟',
                    text: 'این عملیات قابل بازگشت نخواهد بود!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'بله، حذف شود',
                    cancelButtonText: 'انصراف'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: 'add_disciplinary.php',
                            type: 'POST',
                            data: { action: 'delete_disciplinary', record_id: recordId },
                            dataType: 'json',
                            success: function (res) {
                                if (res.status === 'success') {
                                    Swal.fire('حذف شد!', res.message, 'success').then(function () {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire('خطا', res.message, 'error');
                                }
                            }
                        });
                    }
                });
            });

        });
    </script>

</body>

</html>