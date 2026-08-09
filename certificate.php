<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$userType = intval($_SESSION['type'] ?? -1);
if ($userType < 0) {
    header("Location: login.php");
    exit;
}

require_once 'connect.php';
if (!isset($pdo) && isset($connect)) {
    $pdo = $connect;
}

require_once 'teacher/jdf.php';

$action = $_GET['action'] ?? '';
$certID = intval($_GET['id'] ?? 0);

// حالت مشاهده یا چاپ لوح
if ($action === 'view' || $action === 'print') {
    if ($certID <= 0) {
        die("شناسه لوح تقدیر نامعتبر است.");
    }

    try {
        $stmt = $pdo->prepare("
            SELECT c.*, s.Stu_fullName, cl.C_grade, cl.C_major 
            FROM certificates c
            JOIN students s ON c.student_id = s.Stu_ID
            LEFT JOIN classes cl ON s.Stu_classID = cl.C_ID
            WHERE c.id = ?
        ");
        $stmt->execute([$certID]);
        $cert = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$cert) {
            die("لوح تقدیر مورد نظر یافت نشد.");
        }

        // بررسی امنیت دسترسی دانش‌آموز (در جدول admin ستون Ad_type دارد اما نوع دانش‌آموز معمولاً در سیستم شما 0 یا مشابه تعریف شده است)
        if ($userType === 0) {
            $currentStudentID = $_SESSION['ID'] ?? $_SESSION['student_id'] ?? $_SESSION['user_id'] ?? 0;
            if (intval($cert['student_id']) !== intval($currentStudentID)) {
                die("<div style='text-align:center; margin-top:50px; font-family:Tahoma;'><h2>دسترسی غیرمجاز</h2><p>شما اجازه مشاهده این لوح را ندارید.</p></div>");
            }
        }

        $studentName = !empty($cert['Stu_fullName']) ? $cert['Stu_fullName'] : "هنرجو";
        $classTitle = trim(($cert['C_grade'] ?? '') . ' ' . ($cert['C_major'] ?? ''));
        if (empty($classTitle)) {
            $classTitle = "هنرستان راه دانش";
        }

        $certTitle = !empty($cert['title']) ? $cert['title'] : "لوح سپاس و تقدیر";
        $certDesc = !empty($cert['description']) ? $cert['description'] : "به پاس تلاش‌های ارزنده و پشتکار جنابعالی در امور آموزشی و فرهنگی، این لوح تقدیر تقدیم می‌گردد.";

        $timestamp = strtotime($cert['created_at']);
        $jalaliYear = intval(jdate('Y', $timestamp));
        $jalaliMonth = intval(jdate('m', $timestamp));
        $academicYear = ($jalaliMonth >= 7) ? $jalaliYear . '-' . ($jalaliYear + 1) : ($jalaliYear - 1) . '-' . $jalaliYear;

    } catch (Exception $e) {
        die("خطا در ارتباط با دیتابیس.");
    }
    ?>
    <!DOCTYPE html>
    <html lang="fa" dir="rtl">
    <head>
        <meta charset="UTF-8">
        <title><?php echo htmlspecialchars($certTitle, ENT_QUOTES, 'UTF-8'); ?></title>
        <link rel="stylesheet" href="font/font.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>
            @page {
                size: A4 portrait;
                margin: 0;
            }
            * {
                box-sizing: border-box;
                margin: 0;
                padding: 0;
            }
            body {
                font-family: 'Vazirmatn', Tahoma, sans-serif;
                background: #555;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                min-height: 100vh;
            }
            .no-print {
                margin: 15px 0;
                display: flex;
                gap: 10px;
            }
            .btn-action {
                background: #007bff;
                color: #fff;
                border: none;
                padding: 10px 20px;
                font-family: inherit;
                font-size: 14px;
                border-radius: 5px;
                cursor: pointer;
                text-decoration: none;
                display: inline-flex;
                align-items: center;
                gap: 5px;
            }
            .btn-action:hover {
                background: #0056b3;
            }
            .btn-back {
                background: #6c757d;
            }
            .btn-back:hover {
                background: #5a6268;
            }
            .certificate-sheet {
                width: 210mm;
                height: 297mm;
                background-image: url("images/certificates/vertical-bg.jpg");
                background-size: cover;
                background-position: center;
                background-repeat: no-repeat;
                position: relative;
                overflow: hidden;
                box-shadow: 0 0 15px rgba(0,0,0,0.3);
                background-color: #fff;
            }
            .cert-content-box {
                position: absolute;
                top: 25mm;
                bottom: 25mm;
                left: 20mm;
                right: 20mm;
                display: flex;
                flex-direction: column;
                justify-content: space-between;
                align-items: center;
                text-align: center;
            }
            .cert-header {
                display: flex;
                flex-direction: column;
                align-items: center;
                width: 100%;
                margin-top: 15mm;
            }
            .cert-logo {
                width: 80px;
                height: auto;
                margin-bottom: 15px;
            }
            .cert-title {
                font-size: 28px;
                font-weight: bold;
                color: #b8860b;
                margin-bottom: 5px;
            }
            .cert-subtitle {
                font-size: 16px;
                color: #444;
            }
            .cert-body {
                width: 85%;
                margin: 0 auto;
                text-align: center;
            }
            .student-name-box {
                font-size: 24px;
                font-weight: bold;
                color: #222;
                margin-bottom: 15px;
            }
            .student-class-box {
                font-size: 15px;
                color: #555;
                margin-bottom: 20px;
            }
            .cert-text {
                font-size: 16px;
                line-height: 2.2;
                color: #333;
                text-align: justify;
                text-align-last: center;
            }
            .cert-footer {
                width: 100%;
                display: flex;
                justify-content: space-between;
                align-items: flex-end;
                padding: 0 20px;
                margin-bottom: 10mm;
                font-size: 14px;
                color: #333;
            }
            .signature-box {
                text-align: center;
                line-height: 1.8;
            }
            @media print {
                body {
                    background: none;
                }
                .no-print {
                    display: none !important;
                }
                .certificate-sheet {
                    box-shadow: none;
                    margin: 0;
                    width: 210mm;
                    height: 297mm;
                }
            }
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        </style>
    </head>
    <body>

        <div class="no-print">
            <button onclick="window.print();" class="btn-action"><i class="fa fa-print"></i> چاپ / ذخیره PDF</button>
            <?php if ($userType === 0): ?>
                <a href="student/certificate.php" class="btn-action btn-back"><i class="fa fa-arrow-right"></i> بازگشت</a>
            <?php else: ?>
                <a href="certificate.php" class="btn-action btn-back"><i class="fa fa-arrow-right"></i> بازگشت به مدیریت</a>
            <?php endif; ?>
        </div>

        <div class="certificate-sheet">
            <div class="cert-content-box">
                <div class="cert-header">
                    <img src="images/certificates/logo.png" class="cert-logo" alt="لوگو هنرستان">
                    <div class="cert-title"><?php echo htmlspecialchars($certTitle, ENT_QUOTES, 'UTF-8'); ?></div>
                    <div class="cert-subtitle">هنرستان راه دانش</div>
                </div>

                <div class="cert-body">
                    <div class="student-name-box">سرکار خانم / جناب آقای <?php echo htmlspecialchars($studentName, ENT_QUOTES, 'UTF-8'); ?></div>
                    <div class="student-class-box">هنرجوی پایه / رشته: <?php echo htmlspecialchars($classTitle, ENT_QUOTES, 'UTF-8'); ?></div>
                    <div class="cert-text">
                        <?php echo nl2br(htmlspecialchars($certDesc, ENT_QUOTES, 'UTF-8')); ?>
                    </div>
                </div>

                <div class="cert-footer">
                    <div>سال تحصیلی: <?php echo htmlspecialchars($academicYear, ENT_QUOTES, 'UTF-8'); ?></div>
                    <div class="signature-box">
                        مدیریت هنرستان راه دانش<br>
                        (امضا و مهر)
                    </div>
                </div>
            </div>
        </div>

        <?php if ($action === 'print'): ?>
        <script>
            window.onload = function() {
                window.print();
            };
        </script>
        <?php endif; ?>
    </body>
    </html>
    <?php
    exit;
}

$successMsg = "";
$errorMsg = "";

// عملیات حذف
if (isset($_GET['delete_id'])) {
    $delID = intval($_GET['delete_id']);
    try {
        $delStmt = $pdo->prepare("DELETE FROM certificates WHERE id = ?");
        $delStmt->execute([$delID]);
        header("Location: certificate.php");
        exit;
    } catch (Exception $e) {
        $errorMsg = "خطا در حذف لوح تقدیر.";
    }
}

// عملیات ویرایش / ثبت
$editMode = false;
$editData = ['id' => '', 'student_id' => '', 'title' => '', 'description' => '', 'C_ID' => ''];

if (isset($_GET['edit_id'])) {
    $editID = intval($_GET['edit_id']);
    $editStmt = $pdo->prepare("
        SELECT c.*, s.Stu_classID 
        FROM certificates c 
        JOIN students s ON c.student_id = s.Stu_ID 
        WHERE c.id = ?
    ");
    $editStmt->execute([$editID]);
    $fetchedEdit = $editStmt->fetch(PDO::FETCH_ASSOC);
    if ($fetchedEdit) {
        $editMode = true;
        $editData = [
            'id' => $fetchedEdit['id'],
            'student_id' => $fetchedEdit['student_id'],
            'title' => $fetchedEdit['title'],
            'description' => $fetchedEdit['description'],
            'C_ID' => $fetchedEdit['Stu_classID']
        ];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postID = intval($_POST['id'] ?? 0);
    $student_id = intval($_POST['student_id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($student_id <= 0 || empty($description)) {
        $errorMsg = "لطفاً دانش‌آموز و متن لوح را تکمیل کنید.";
    } else {
        if ($postID > 0) {
            $updateStmt = $pdo->prepare("UPDATE certificates SET student_id = ?, title = ?, description = ? WHERE id = ?");
            if ($updateStmt->execute([$student_id, $title, $description, $postID])) {
                header("Location: certificate.php");
                exit;
            } else {
                $errorMsg = "خطا در ویرایش اطلاعات.";
            }
        } else {
            $insertStmt = $pdo->prepare("INSERT INTO certificates (student_id, title, description) VALUES (?, ?, ?)");
            if ($insertStmt->execute([$student_id, $title, $description])) {
                header("Location: certificate.php");
                exit;
            } else {
                $errorMsg = "خطا در ثبت لوح تقدیر جدید.";
            }
        }
    }
}

$classesStmt = $pdo->query("SELECT * FROM classes ORDER BY C_grade, C_major");
$classesList = $classesStmt->fetchAll(PDO::FETCH_ASSOC);

$certificatesListStmt = $pdo->query("
    SELECT c.*, s.Stu_fullName, cl.C_grade, cl.C_major 
    FROM certificates c
    JOIN students s ON c.student_id = s.Stu_ID
    LEFT JOIN classes cl ON s.Stu_classID = cl.C_ID
    ORDER BY c.id DESC
");
$certificatesList = $certificatesListStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>مدیریت لوح‌های تقدیر - هنرستان راه دانش</title>
    <link rel="stylesheet" href="font/font.css">
    <link rel="stylesheet" href="styles/admin_panel.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            font-family: 'Vazirmatn', Tahoma, sans-serif;
            background-color: #f4f6f9;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        h2 {
            margin-bottom: 20px;
            color: #333;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #444;
        }
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-family: inherit;
            font-size: 14px;
        }
        textarea.form-control {
            resize: vertical;
            height: 120px;
        }
        .btn {
            background: #28a745;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-family: inherit;
            font-size: 14px;
        }
        .btn:hover {
            background: #218838;
        }
        .btn-secondary {
            background: #6c757d;
        }
        .btn-secondary:hover {
            background: #5a6268;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
        }
        th, td {
            border: 1px solid #dee2e6;
            padding: 12px;
            text-align: center;
            font-size: 14px;
        }
        th {
            background-color: #007bff;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .action-links a {
            padding: 6px 10px;
            margin: 0 2px;
            border-radius: 4px;
            color: #fff;
            text-decoration: none;
            display: inline-block;
            font-size: 13px;
        }
        .btn-view { background: #17a2b8; }
        .btn-print { background: #ffc107; color: #000 !important; }
        .btn-edit { background: #fd7e14; }
        .btn-del { background: #dc3545; }
        .top-nav {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="top-nav">
            <a href="panel.php" class="btn btn-secondary"><i class="fa fa-arrow-right"></i> بازگشت به پنل مدیریت</a>
        </div>

        <h2>
            <span><i class="fa fa-award"></i> مدیریت لوح‌های تقدیر هنرستان</span>
        </h2>

        <?php if (!empty($errorMsg)): ?>
            <div class="alert-error"><?php echo htmlspecialchars($errorMsg, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <form method="POST" action="certificate.php">
            <input type="hidden" name="id" value="<?php echo $editData['id']; ?>">
            
            <div style="display: flex; gap: 20px;">
                <div class="form-group" style="flex: 1;">
                    <label>انتخاب کلاس:</label>
                    <select id="class_selector" class="form-control" onchange="loadStudents(this.value)">
                        <option value="">-- انتخاب کلاس --</option>
                        <?php foreach ($classesList as $cl): ?>
                            <option value="<?php echo $cl['C_ID']; ?>" <?php echo ($editData['C_ID'] == $cl['C_ID']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cl['C_grade'] . ' - ' . $cl['C_major'], ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group" style="flex: 1;">
                    <label>انتخاب دانش‌آموز:</label>
                    <select name="student_id" id="student_selector" class="form-control" required>
                        <option value="">-- ابتدا کلاس را انتخاب کنید --</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>عنوان لوح (اختیاری - پیش‌فرض: لوح سپاس و تقدیر):</label>
                <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($editData['title'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="مثلاً: لوح تقدیر علمی و پژوهشی">
            </div>

            <div class="form-group">
                <label>متن لوح تقدیر:</label>
                <textarea name="description" class="form-control" required placeholder="متن تقدیرنامه را اینجا بنویسید..."><?php echo htmlspecialchars($editData['description'], ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>

            <button type="submit" class="btn"><i class="fa fa-save"></i> <?php echo $editMode ? 'ویرایش لوح تقدیر' : 'ثبت و صدور لوح تقدیر'; ?></button>
            <?php if ($editMode): ?>
                <a href="certificate.php" class="btn btn-secondary" style="text-decoration:none; display:inline-block; padding:10px 20px;">انصراف</a>
            <?php endif; ?>
        </form>

        <hr style="margin: 30px 0; border:0; border-top:1px solid #ddd;">

        <h3>لیست لوح‌های صادر شده</h3>
        <table>
            <thead>
                <tr>
                    <th>ردیف</th>
                    <th>نام دانش‌آموز</th>
                    <th>کلاس</th>
                    <th>عنوان لوح</th>
                    <th>تاریخ ثبت</th>
                    <th>عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($certificatesList) > 0): ?>
                    <?php $rowNum = 1; foreach ($certificatesList as $item): 
                        $stuName = !empty($item['Stu_fullName']) ? $item['Stu_fullName'] : 'هنرجو';
                        $classInfo = trim(($item['C_grade'] ?? '') . ' ' . ($item['C_major'] ?? ''));
                    ?>
                        <tr>
                            <td><?php echo $rowNum++; ?></td>
                            <td><?php echo htmlspecialchars($stuName, ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($classInfo, ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars(!empty($item['title']) ? $item['title'] : 'لوح سپاس و تقدیر', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars(jdate('Y/m/d', strtotime($item['created_at'])), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td class="action-links">
                                <a href="certificate.php?id=<?php echo $item['id']; ?>&action=view" class="btn-view" title="مشاهده"><i class="fa fa-eye"></i></a>
                                <a href="certificate.php?id=<?php echo $item['id']; ?>&action=print" class="btn-print" title="چاپ"><i class="fa fa-print"></i></a>
                                <a href="certificate.php?edit_id=<?php echo $item['id']; ?>" class="btn-edit" title="ویرایش"><i class="fa fa-edit"></i></a>
                                <a href="#" onclick="confirmDelete(<?php echo $item['id']; ?>)" class="btn-del" title="حذف"><i class="fa fa-trash"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="padding: 20px; color: #777;">هیچ لوح تقدیری تاکنون ثبت نشده است.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script>
        function loadStudents(classID, selectedStuID = null) {
            const studentSelect = document.getElementById('student_selector');
            studentSelect.innerHTML = '<option value="">در حال بارگذاری...</option>';

            if (!classID) {
                studentSelect.innerHTML = '<option value="">-- ابتدا کلاس را انتخاب کنید --</option>';
                return;
            }

            fetch('get_students_by_class.php?class_id=' + classID)
                .then(response => response.json())
                .then(data => {
                    studentSelect.innerHTML = '<option value="">-- انتخاب دانش‌آموز --</option>';
                    data.forEach(stu => {
                        let opt = document.createElement('option');
                        opt.value = stu.id;
                        opt.textContent = stu.name;
                        if (selectedStuID && stu.id == selectedStuID) {
                            opt.selected = true;
                        }
                        studentSelect.appendChild(opt);
                    });
                })
                .catch(error => {
                    studentSelect.innerHTML = '<option value="">خطا در دریافت لیست دانش‌آموزان</option>';
                });
        }

        function confirmDelete(id) {
            Swal.fire({
                title: 'آیا از حذف این لوح اطمینان دارید؟',
                text: "این عملیات غیرقابل بازگشت است!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'بله، حذف شود',
                cancelButtonText: 'انصراف'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'certificate.php?delete_id=' + id;
                }
            });
        }

        <?php if ($editMode && !empty($editData['C_ID'])): ?>
            window.onload = function() {
                loadStudents(<?php echo $editData['C_ID']; ?>, <?php echo $editData['student_id']; ?>);
            };
        <?php endif; ?>
    </script>
</body>
</html>
