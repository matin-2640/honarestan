<?php
session_start();

// بررسی لاگین بودن معلم
if (!isset($_SESSION['ID'])) {
    header("Location: ../login.php");
    exit();
}

$teacher_id = $_SESSION['ID'];
$message = "";
$messageType = "";

// فراخوانی دیتابیس
include '../connect.php';

// ---------------------------------------------------------
// ۱. عملیات حذف تمرین (Delete)
// ---------------------------------------------------------
if (isset($_POST['action']) && $_POST['action'] === 'delete_assignment') {
    $assignment_id = intval($_POST['assignment_id']);

    try {
        // دریافت آدرس فایل برای حذف فیزیکی از سرور
        $stmt = $connect->prepare("SELECT file_path FROM Assignments WHERE id = :id AND teacher_id = :teacher_id");
        $stmt->execute([':id' => $assignment_id, ':teacher_id' => $teacher_id]);
        $assignment = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($assignment) {
            if (file_exists($assignment['file_path'])) {
                unlink($assignment['file_path']); // حذف فایل از پوشه
            }

            // حذف از جدول Assignments
            $delStmt = $connect->prepare("DELETE FROM Assignments WHERE id = :id AND teacher_id = :teacher_id");
            $delStmt->execute([':id' => $assignment_id, ':teacher_id' => $teacher_id]);

            $message = "تمرین با موفقیت حذف شد.";
            $messageType = "success";
        } else {
            $message = "تمرین مورد نظر یافت نشد یا دسترسی حذف آن را ندارید.";
            $messageType = "error";
        }
    } catch (PDOException $e) {
        $message = "خطا در حذف تمرین: " . $e->getMessage();
        $messageType = "error";
    }
}

// ---------------------------------------------------------
// ۲. عملیات ویرایش عنوان تمرین (Update)
// ---------------------------------------------------------
if (isset($_POST['action']) && $_POST['action'] === 'edit_assignment') {
    $assignment_id = intval($_POST['assignment_id']);
    $new_title = trim($_POST['new_title']);

    if (!empty($new_title)) {
        try {
            $stmt = $connect->prepare("UPDATE Assignments SET title = :title WHERE id = :id AND teacher_id = :teacher_id");
            $stmt->execute([
                ':title' => $new_title,
                ':id' => $assignment_id,
                ':teacher_id' => $teacher_id
            ]);
            $message = "عنوان تمرین با موفقیت به‌روزرسانی شد.";
            $messageType = "success";
        } catch (PDOException $e) {
            $message = "خطا در به‌روزرسانی: " . $e->getMessage();
            $messageType = "error";
        }
    } else {
        $message = "عنوان تمرین نمی‌تواند خالی باشد.";
        $messageType = "error";
    }
}

// ---------------------------------------------------------
// ۳. عملیات بارگذاری تمرین جدید (Upload)
// ---------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_assignment'])) {
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $class_id = isset($_POST['class_id']) ? intval($_POST['class_id']) : 0;

    if (empty($title) || empty($class_id) || !isset($_FILES['assignment_file']) || $_FILES['assignment_file']['error'] !== 0) {
        $message = "لطفاً تمامی فیلدها را به درستی پر کنید و فایل را انتخاب نمایید.";
        $messageType = "error";
    } else {
        $file = $_FILES['assignment_file'];
        $fileName = $file['name'];
        $fileTmpName = $file['tmp_name'];

        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowed = array('jpg', 'jpeg', 'png', 'pdf', 'ppt', 'pptx', 'doc', 'docx', 'accdb', 'mdb', 'xls', 'xlsx', 'sql', 'txt');

        if (in_array($fileExt, $allowed)) {
            $newFileName = time() . '_' . rand(1000, 9999) . '.' . $fileExt;
            $uploadDir = "../images/tamrin/";

            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileDestination = $uploadDir . $newFileName;

            if (move_uploaded_file($fileTmpName, $fileDestination)) {
                try {
                    $stmt = $connect->prepare("INSERT INTO Assignments (title, file_path, class_id, teacher_id) VALUES (:title, :file_path, :class_id, :teacher_id)");
                    $result = $stmt->execute([
                        ':title' => $title,
                        ':file_path' => $fileDestination,
                        ':class_id' => $class_id,
                        ':teacher_id' => $teacher_id
                    ]);

                    if ($result) {
                        $message = "تمرین با موفقیت بارگذاری شد.";
                        $messageType = "success";
                    } else {
                        $message = "خطا در ثبت اطلاعات در دیتابیس.";
                        $messageType = "error";
                    }
                } catch (PDOException $e) {
                    $message = "خطا در ارتباط با دیتابیس: " . $e->getMessage();
                    $messageType = "error";
                }
            } else {
                $message = "خطا در انتقال فایل به سرور.";
                $messageType = "error";
            }
        } else {
            $message = "پسوند فایل انتخابی مجاز نیست!";
            $messageType = "error";
        }
    }
}

// ---------------------------------------------------------
// ۴. دریافت لیست کلاس‌ها و لیست تمرین‌های این معلم
// ---------------------------------------------------------
$classes = array();
$my_assignments = array();

if (isset($connect) && $connect) {
    try {
        // لیست کلاس‌ها
        $classes_query = "SELECT C_ID, C_Grade, C_Major FROM Classes ORDER BY C_Grade ASC";
        $stmt = $connect->query($classes_query);
        $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // لیست تمرین‌های ثبت‌شده همین معلم همراه با نام کلاس
        $assignments_query = "SELECT A.id, A.title, A.file_path, C.C_Grade, C.C_Major 
                            FROM Assignments A 
                            LEFT JOIN Classes C ON A.class_id = C.C_ID 
                            WHERE A.teacher_id = :teacher_id 
                            ORDER BY A.id DESC";
        $stmtAssignments = $connect->prepare($assignments_query);
        $stmtAssignments->execute([':teacher_id' => $teacher_id]);
        $my_assignments = $stmtAssignments->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $classes = array();
        $my_assignments = array();
    }
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>بارگذاری تمرین جدید</title>

    <!-- فایل‌های پروژه -->
    <link rel="icon" href="../images/icons/rahdanesh.png">
    <link rel="stylesheet" href="../styles/font.css">
    <link rel="stylesheet" href="../styles/style.css">
    <link rel="stylesheet" href="../js/sweetalert2.min.css">
    <link rel="stylesheet" href="../styles/note.css">

    <script src="../js/jquery-1.10.2.min.js"></script>
    <script src="../js/sweetalert2.min.js"></script>
    <script src="../js/theme.js"></script>
</head>

<body>

    <!-- لودر صفحه -->
    <div id="loader"></div>

    <div class="container">
        <!-- هدر صفحه -->
        <header class="page-header">
            <a href="../teacher_panel.php" class="btn-back" title="بازگشت به پنل">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 12H5M12 19l-7-7 7-7" />
                </svg>
                بازگشت به پنل
            </a>

            <!-- دکمه تم منطبق با theme.js -->
            <button id="themeToggle" class="theme-toggle-btn" aria-label="تغییر تم">
                <i class="fa-solid fa-moon"></i>
            </button>
        </header>

        <!-- کارت فرم اصلی -->
        <main class="form-card">
            <h2>بارگذاری تمرین جدید</h2>

            <form action="" method="POST" enctype="multipart/form-data" id="uploadForm">

                <!-- عنوان تمرین -->
                <div class="form-group">
                    <label for="title">عنوان تمرین:</label>
                    <input type="text" id="title" name="title" required placeholder="مثلاً: تمرین سری اول - پودمان دوم">
                </div>

                <!-- انتخاب کلاس -->
                <div class="form-group">
                    <label for="class_id">کلاس مربوطه:</label>
                    <select id="class_id" name="class_id" required>
                        <option value="">-- انتخاب کلاس --</option>
                        <?php
                        if (!empty($classes)) {
                            foreach ($classes as $class) {
                                echo '<option value="' . htmlspecialchars($class['C_ID']) . '">' . htmlspecialchars($class['C_Grade']) . ' ' . htmlspecialchars($class['C_Major']) . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>

                <!-- آپلود فایل -->
                <div class="form-group">
                    <label for="assignment_file">فایل تمرین:</label>
                    <input type="file" id="assignment_file" name="assignment_file" required
                        accept=".jpg,.jpeg,.png,.pdf,.ppt,.pptx,.doc,.docx,.accdb,.mdb,.xls,.xlsx,.sql,.txt">
                    <small class="help-text">پسوندهای مجاز: عکس، PDF، ورد، پاورپوینت، اکسس، اکسل، SQL، TXT</small>
                </div>

                <!-- دکمه ارسال -->
                <div class="form-actions">
                    <button type="submit" name="submit_assignment" class="btn-submit">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                            <polyline points="17 8 12 3 7 8" />
                            <line x1="12" y1="3" x2="12" y2="15" />
                        </svg>
                        ثبت و بارگذاری تمرین
                    </button>
                </div>

            </form>
        </main>

        <!-- بخش باکس‌های تمرین‌های آپلود شده -->
        <section class="notes-section">
            <h3>تمرین‌های آپلود شده شما</h3>

            <div class="notes-grid">
                <?php if (!empty($my_assignments)): ?>
                    <?php foreach ($my_assignments as $assignment): ?>
                        <div class="note-box">
                            <!-- آیکون فایل و دانلود -->
                            <a href="<?php echo htmlspecialchars($assignment['file_path']); ?>" download
                                class="file-download-link" title="دانلود فایل تمرین">
                                <div class="file-icon">
                                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                        <polyline points="14 2 14 8 20 8"></polyline>
                                        <line x1="12" y1="18" x2="12" y2="12"></line>
                                        <polyline points="9 15 12 18 15 15"></polyline>
                                    </svg>
                                </div>
                                <span class="note-title"><?php echo htmlspecialchars($assignment['title']); ?></span>
                                <span
                                    class="note-class"><?php echo htmlspecialchars($assignment['C_Grade'] . ' ' . $assignment['C_Major']); ?></span>
                            </a>

                            <!-- دکمه‌های عملیاتی (ویرایش و حذف) -->
                            <div class="note-actions">
                                <button type="button" class="btn-edit"
                                    onclick="editAssignment(<?php echo $assignment['id']; ?>, '<?php echo addslashes(htmlspecialchars($assignment['title'])); ?>')">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                    </svg>
                                    ویرایش
                                </button>

                                <button type="button" class="btn-delete"
                                    onclick="deleteAssignment(<?php echo $assignment['id']; ?>)">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2">
                                        <polyline points="3 6 5 6 21 6"></polyline>
                                        <path
                                            d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2">
                                        </path>
                                    </svg>
                                    حذف
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-notes">هنوز هیچ تمرینی آپلود نکرده‌اید.</div>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <!-- فرم‌های مخفی برای ارسال درخواست ویرایش و حذف -->
    <form id="deleteForm" action="" method="POST" style="display: none;">
        <input type="hidden" name="action" value="delete_assignment">
        <input type="hidden" name="assignment_id" id="delete_assignment_id">
    </form>

    <form id="editForm" action="" method="POST" style="display: none;">
        <input type="hidden" name="action" value="edit_assignment">
        <input type="hidden" name="assignment_id" id="edit_assignment_id">
        <input type="hidden" name="new_title" id="edit_new_title">
    </form>

    <!-- اسکریپت SweetAlert2 و توابع کلاینت -->
    <script>
        $(document).ready(function () {

            // نمایش پیام‌های سرور با SweetAlert2
            <?php if (!empty($message)): ?>
                Swal.fire({
                    title: '<?php echo $messageType === "success" ? "موفقیت" : "خطا"; ?>',
                    text: '<?php echo $message; ?>',
                    icon: '<?php echo $messageType; ?>',
                    confirmButtonText: 'تأیید'
                });
            <?php endif; ?>

            // اعتبارسنجی پسوند فایل قبل از ارسال
            $('#uploadForm').on('submit', function (e) {
                var fileInput = $('#assignment_file')[0];
                if (fileInput.files && fileInput.files.length > 0) {
                    var fileName = fileInput.files[0].name;
                    var ext = fileName.split('.').pop().toLowerCase();
                    var allowedExts = ['jpg', 'jpeg', 'png', 'pdf', 'ppt', 'pptx', 'doc', 'docx', 'accdb', 'mdb', 'xls', 'xlsx', 'sql', 'txt'];

                    if ($.inArray(ext, allowedExts) === -1) {
                        e.preventDefault();
                        Swal.fire({
                            title: 'خطای پسوند فایل',
                            text: 'فرمت فایل انتخابی مجاز نیست.',
                            icon: 'warning',
                            confirmButtonText: 'تأیید'
                        });
                    }
                }
            });

        });

        // تابع حذف تمرین با SweetAlert2
        function deleteAssignment(assignmentId) {
            Swal.fire({
                title: 'آیا از حذف این تمرین اطمینان دارید؟',
                text: "این عملیات غیرقابل بازگشت است!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'بله، حذف شود',
                cancelButtonText: 'انصراف'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#delete_assignment_id').val(assignmentId);
                    $('#deleteForm').submit();
                }
            });
        }

        // تابع ویرایش عنوان تمرین با SweetAlert2
        function editAssignment(assignmentId, currentTitle) {
            Swal.fire({
                title: 'ویرایش عنوان تمرین',
                input: 'text',
                inputValue: currentTitle,
                showCancelButton: true,
                confirmButtonText: 'ذخیره تغییرات',
                cancelButtonText: 'انصراف',
                inputValidator: (value) => {
                    if (!value.trim()) {
                        return 'لطفاً یک عنوان وارد کنید!';
                    }
                }
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    $('#edit_assignment_id').val(assignmentId);
                    $('#edit_new_title').val(result.value.trim());
                    $('#editForm').submit();
                }
            });
        }
    </script>

</body>

</html>