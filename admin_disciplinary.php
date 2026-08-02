<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!(isset($_SESSION["state_login"]) && $_SESSION["type"] <= 2)) {
    header("location:login.php");
    exit();
}
include("connect.php");

$error_msg = null;
$success_msg = null;

// ۱. دریافت لیست کامل کلاس‌ها بر اساس ساختار دیتابیس (C_ID, C_grade, C_major)
$classes = [];
try {
    $stmtClasses = $connect->query("SELECT C_ID, C_grade, C_major FROM classes ORDER BY C_grade ASC, C_major ASC");
    while ($row = $stmtClasses->fetch(PDO::FETCH_ASSOC)) {
        $classes[] = [
            'Class_ID' => $row['C_ID'],
            'Class_name' => "پایه " . $row['C_grade'] . " - " . $row['C_major']
        ];
    }
} catch (PDOException $e) {
    $error_msg = "خطا در دریافت لیست کلاس‌ها: " . $e->getMessage();
}

// ۲. پردازش فرم ثبت پرونده انضباطی
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_disciplinary'])) {
    $student_id = intval($_POST['student_id']);
    $title = trim($_POST['title']);
    $incident_date = trim($_POST['incident_date']);
    $incident_time = trim($_POST['incident_time']);
    $description = trim($_POST['description']);

    $desc_length = mb_strlen($description, 'UTF-8');

    if ($desc_length > 400) {
        $error_msg = "شرح انضباطی نباید بیشتر از ۴۰۰ کاراکتر باشد. (تعداد کاراکتر فعلی: " . $desc_length . ")";
    } elseif ($desc_length == 0) {
        $error_msg = "شرح توضیحات حادثه نمی‌تواند خالی باشد.";
    } elseif ($student_id <= 0 || empty($title) || empty($incident_date) || empty($incident_time)) {
        $error_msg = "لطفاً تمامی فیلدها را به درستی پر کنید.";
    } else {
        $sql = "INSERT INTO disciplinary_records (student_id, title, incident_date, incident_time, description) VALUES (?, ?, ?, ?, ?)";
        $stmtInsert = $connect->prepare($sql);
        $result = $stmtInsert->execute([$student_id, $title, $incident_date, $incident_time, $description]);

        if ($result) {
            $success_msg = "پرونده انضباطی دانش‌آموز با موفقیت ثبت شد.";
        } else {
            $error_msg = "خطایی در ثبت اطلاعات رخ داد.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ثبت پرونده انضباطی</title>
    <link rel="stylesheet" href="styles/disciplinary.css">
    <link rel="stylesheet" href="styles/font.css">
    <link rel="icon" href="images/icons/rahdanesh.png">
    <link rel="stylesheet" href="https://unpkg.com/persian-datepicker@1.2.0/dist/css/persian-datepicker.min.css">
</head>

<body>

    <div class="container">

        <?php if ($success_msg): ?>
            <div class="alert-box alert-success"><?php echo $success_msg; ?></div>
        <?php endif; ?>

        <?php if ($error_msg): ?>
            <div class="alert-box alert-danger"><?php echo $error_msg; ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-title">
                <svg viewBox="0 0 24 24">
                    <path d="M12 2L1 21h22L12 2zm1 14h-2v-2h2v2zm0-4h-2v-4h2v4z" />
                </svg>
                ثبت پرونده انضباطی جدید
            </div>

            <form method="POST" action="">
                <div class="form-grid">

                    <div class="form-group">
                        <label>۱. انتخاب کلاس:</label>
                        <select name="class_id" id="classSelect" onchange="loadStudents(this.value)" required>
                            <option value="">-- انتخاب کلاس --</option>
                            <?php foreach ($classes as $c): ?>
                                <option value="<?php echo $c['Class_ID']; ?>">
                                    <?php echo htmlspecialchars($c['Class_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>۲. انتخاب دانش‌آموز:</label>
                        <select name="student_id" id="studentSelect" required disabled>
                            <option value="">-- ابتدا کلاس را انتخاب کنید --</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>۳. عنوان مورد انضباطی:</label>
                        <input type="text" name="title" placeholder="مثلاً: تاخیر در ورود" required>
                    </div>

                    <div class="form-group">
                        <label>۴. تاریخ حادثه:</label>
                        <input type="text" name="incident_date" id="incidentDate" placeholder="انتخاب تاریخ..."
                            autocomplete="off" required>
                    </div>

                    <div class="form-group">
                        <label>۵. ساعت حادثه:</label>
                        <input type="time" name="incident_time" value="<?php echo date('H:i'); ?>" required>
                    </div>

                    <div class="form-group full-width">
                        <label>شرح کامل وقوع حادثه:</label>
                        <textarea name="description" id="descInput" rows="4" maxlength="400"
                            placeholder="توضیحات دقیق حادثه..." oninput="updateCharCount()" required></textarea>
                        <small id="charCounter" style="color: #64748b; margin-top: 5px; font-weight: bold;">
                            تعداد کاراکترها: 0 / 400
                        </small>
                    </div>

                </div>

                <div class="actions-footer">
                    <button type="submit" name="save_disciplinary" class="btn-submit">
                        <svg viewBox="0 0 24 24">
                            <path
                                d="M17 3H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V7l-4-4zm-5 16c-1.66 0-3-1.34-3-3s1.34-3 3-3 3 1.34 3 3-1.34 3-3 3zm3-10H5V5h10v4z" />
                        </svg>
                        ثبت پرونده انضباطی
                    </button>

                    <a href="view_disciplinary.php" class="btn-view-link">
                        <svg viewBox="0 0 24 24">
                            <path
                                d="M19 19H5V5h7V3H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2v-7h-2v7zM14 3v2h3.59l-9.83 9.83 1.41 1.41L19 6.41V10h2V3h-7z" />
                        </svg>
                        مشاهده پرونده‌های انضباطی
                    </a>
                </div>
            </form>
        </div>

    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://unpkg.com/persian-date@1.1.0/dist/persian-date.min.js"></script>
    <script src="https://unpkg.com/persian-datepicker@1.2.0/dist/js/persian-datepicker.min.js"></script>

    <script>
        // ۱. فعال‌سازی تقویم شمسی روی ورودی تاریخ
        $(document).ready(function () {
            $('#incidentDate').persianDatepicker({
                format: 'YYYY/MM/DD',
                autoClose: true,
                initialValue: true,
                calendar: {
                    persian: {
                        locale: 'fa'
                    }
                }
            });
        });

        // ۲. بارگذاری دانش‌آموزان کلاس انتخاب‌شده
        function loadStudents(classId) {
            let studentSelect = document.getElementById('studentSelect');
            studentSelect.innerHTML = '<option value="">در حال دریافت...</option>';

            if (!classId) {
                studentSelect.innerHTML = '<option value="">-- ابتدا کلاس را انتخاب کنید --</option>';
                studentSelect.disabled = true;
                return;
            }

            fetch('get_students.php?class_id=' + classId)
                .then(response => response.json())
                .then(data => {
                    studentSelect.innerHTML = '<option value="">-- انتخاب دانش‌آموز --</option>';
                    if (data && data.length > 0) {
                        data.forEach(stu => {
                            let option = document.createElement('option');
                            option.value = stu.Stu_ID;
                            option.textContent = stu.Stu_fullName;
                            studentSelect.appendChild(option);
                        });
                        studentSelect.disabled = false;
                    } else {
                        studentSelect.innerHTML = '<option value="">هیچ دانش‌آموزی در این کلاس یافت نشد</option>';
                        studentSelect.disabled = true;
                    }
                })
                .catch(err => {
                    console.error('Error:', err);
                    studentSelect.innerHTML = '<option value="">خطا در دریافت اطلاعات</option>';
                    studentSelect.disabled = true;
                });
        }

        // ۳. کنترل شمارش کاراکترها
        function updateCharCount() {
            let textarea = document.getElementById('descInput');
            let counter = document.getElementById('charCounter');
            let len = textarea.value.length;

            counter.innerText = 'تعداد کاراکترها: ' + len + ' / 400';
            counter.style.color = (len >= 400) ? '#dc2626' : '#64748b';
        }
    </script>

</body>

</html>