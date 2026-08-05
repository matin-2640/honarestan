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

// فراخوانی دیتابیس (متغیر $connect)
include '../connect.php';

// ---------------------------------------------------------
// ۱. عملیات حذف تمرین (Delete)
// ---------------------------------------------------------
if (isset($_POST['action']) && $_POST['action'] === 'delete_assignment') {
    $assignment_id = intval($_POST['assignment_id']);

    try {
        $stmt = $connect->prepare("SELECT file_path FROM Assignments WHERE id = :id AND teacher_id = :teacher_id");
        $stmt->execute([':id' => $assignment_id, ':teacher_id' => $teacher_id]);
        $assignment = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($assignment) {
            if (!empty($assignment['file_path']) && $assignment['file_path'] !== 'none' && file_exists($assignment['file_path'])) {
                unlink($assignment['file_path']);
            }

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
// ۲. عملیات ویرایش تمرین (Update)
// ---------------------------------------------------------
if (isset($_POST['action']) && $_POST['action'] === 'edit_assignment') {
    $assignment_id = intval($_POST['assignment_id']);
    $new_title = isset($_POST['edit_title']) ? trim($_POST['edit_title']) : '';
    $new_expiration_date = isset($_POST['edit_expiration_date']) ? trim($_POST['edit_expiration_date']) : '';
    $new_description = isset($_POST['edit_description']) ? trim($_POST['edit_description']) : '';

    if (mb_strlen($new_description, 'UTF-8') > 500) {
        $new_description = mb_substr($new_description, 0, 500, 'UTF-8');
    }

    if (!empty($new_title) && !empty($new_expiration_date)) {
        try {
            $stmt = $connect->prepare("SELECT file_path FROM Assignments WHERE id = :id AND teacher_id = :teacher_id");
            $stmt->execute([':id' => $assignment_id, ':teacher_id' => $teacher_id]);
            $currentAssignment = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($currentAssignment) {
                $filePath = $currentAssignment['file_path'];
                $uploadOk = true;

                // بررسی انتخابی بودن فایل جدید
                if (isset($_FILES['edit_file']) && $_FILES['edit_file']['error'] !== UPLOAD_ERR_NO_FILE) {
                    if ($_FILES['edit_file']['error'] === UPLOAD_ERR_OK) {
                        $file = $_FILES['edit_file'];
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

                            $newFileDestination = $uploadDir . $newFileName;

                            if (move_uploaded_file($fileTmpName, $newFileDestination)) {
                                if (!empty($filePath) && $filePath !== 'none' && file_exists($filePath)) {
                                    unlink($filePath);
                                }
                                $filePath = $newFileDestination;
                            } else {
                                $uploadOk = false;
                                $message = "خطا در آپلود فایل جدید.";
                                $messageType = "error";
                            }
                        } else {
                            $uploadOk = false;
                            $message = "پسوند فایل جدید مجاز نیست!";
                            $messageType = "error";
                        }
                    } else {
                        $uploadOk = false;
                        $message = "خطا در بارگذاری فایل.";
                        $messageType = "error";
                    }
                }

                if ($uploadOk) {
                    // اگر مسیر خالی بود مقدار none ست می‌شود
                    if (empty($filePath)) {
                        $filePath = 'none';
                    }

                    $updateStmt = $connect->prepare("UPDATE Assignments SET title = :title, description = :description, expiration_date = :expiration_date, file_path = :file_path WHERE id = :id AND teacher_id = :teacher_id");
                    $updateStmt->execute([
                        ':title' => $new_title,
                        ':description' => $new_description,
                        ':expiration_date' => $new_expiration_date,
                        ':file_path' => $filePath,
                        ':id' => $assignment_id,
                        ':teacher_id' => $teacher_id
                    ]);
                    $message = "تمرین با موفقیت به‌روزرسانی شد.";
                    $messageType = "success";
                }
            } else {
                $message = "تمرین مورد نظر یافت نشد.";
                $messageType = "error";
            }
        } catch (PDOException $e) {
            $message = "خطا در به‌روزرسانی: " . $e->getMessage();
            $messageType = "error";
        }
    } else {
        $message = "لطفاً فیلدهای ضروری (عنوان و مهلت تحویل) را پر کنید.";
        $messageType = "error";
    }
}

// ---------------------------------------------------------
// ۳. عملیات بارگذاری تمرین جدید (Upload)
// ---------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_assignment'])) {
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $class_id = isset($_POST['class_id']) ? intval($_POST['class_id']) : 0;
    $expiration_date = isset($_POST['expiration_date']) ? trim($_POST['expiration_date']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';

    if (mb_strlen($description, 'UTF-8') > 500) {
        $description = mb_substr($description, 0, 500, 'UTF-8');
    }

    if (empty($title) || empty($class_id) || empty($expiration_date)) {
        $message = "لطفاً تمامی فیلدهای ضروری (عنوان، کلاس و مهلت تحویل) را پر کنید.";
        $messageType = "error";
    } else {
        // مقدار اولیه مسیر فایل برابر 'none' قرار می‌گیرد
        $fileDestination = 'none';
        $uploadOk = true;

        // بررسی انتخابی بودن فایل
        if (isset($_FILES['assignment_file']) && $_FILES['assignment_file']['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($_FILES['assignment_file']['error'] === UPLOAD_ERR_OK) {
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

                    $targetPath = $uploadDir . $newFileName;

                    if (move_uploaded_file($fileTmpName, $targetPath)) {
                        $fileDestination = $targetPath;
                    } else {
                        $uploadOk = false;
                        $message = "خطا در انتقال فایل به سرور.";
                        $messageType = "error";
                    }
                } else {
                    $uploadOk = false;
                    $message = "پسوند فایل انتخابی مجاز نیست!";
                    $messageType = "error";
                }
            } else {
                $uploadOk = false;
                $message = "خطا در بارگذاری فایل.";
                $messageType = "error";
            }
        }

        if ($uploadOk) {
            try {
                $stmt = $connect->prepare("INSERT INTO Assignments (title, file_path, class_id, teacher_id, expiration_date, description) VALUES (:title, :file_path, :class_id, :teacher_id, :expiration_date, :description)");
                $result = $stmt->execute([
                    ':title' => $title,
                    ':file_path' => $fileDestination,
                    ':class_id' => $class_id,
                    ':teacher_id' => $teacher_id,
                    ':expiration_date' => $expiration_date,
                    ':description' => $description
                ]);

                if ($result) {
                    $message = "تمرین با موفقیت ثبت شد.";
                    $messageType = "success";
                } else {
                    $message = "خطا در ثبت اطلاعات در دیتابیس.";
                    $messageType = "error";
                }
            } catch (PDOException $e) {
                $message = "خطا در دیتابیس: " . $e->getMessage();
                $messageType = "error";
            }
        }
    }
}

// ---------------------------------------------------------
// ۴. دریافت لیست کلاس‌ها و لیست تمرین‌ها (مرتب‌شده بر اساس پایه ۱۰، ۱۱، ۱۲)
// ---------------------------------------------------------
$classes = array();
$my_assignments = array();

if (isset($connect) && $connect) {
    try {
        $classes_query = "SELECT C_ID, C_Grade, C_Major FROM Classes ORDER BY C_Grade ASC, C_Major ASC";
        $stmt = $connect->query($classes_query);
        $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // مرتب‌سازی بر اساس پایه کلاس (۱۰ سپس ۱۱ سپس ۱۲)
        $assignments_query = "SELECT A.id, A.title, A.file_path, A.expiration_date, A.description, C.C_Grade, C.C_Major 
                            FROM Assignments A 
                            LEFT JOIN Classes C ON A.class_id = C.C_ID 
                            WHERE A.teacher_id = :teacher_id 
                            ORDER BY C.C_Grade ASC, C.C_Major ASC, A.id DESC";
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
    <title>ثبت تمرین جدید</title>

    <link rel="icon" href="../images/icons/rahdanesh.png">
    <link rel="stylesheet" href="../styles/font.css">
    <link rel="stylesheet" href="../styles/note.css">
    <link rel="stylesheet" href="../styles/style.css">
    <link rel="stylesheet" href="../js/sweetalert2.min.css">
    <link rel="stylesheet" href="../js/jalali-datepicker.min.css">

    <script src="../js/jquery-1.10.2.min.js"></script>
    <script src="../js/sweetalert2.min.js"></script>
    <script src="../js/theme.js"></script>
    <script src="../js/jalali-datepicker.min.js"></script>

    <style>
        textarea.form-control {
            width: 100%;
            padding: 12px 14px;
            background-color: var(--input-bg);
            border: 1px solid var(--input-border);
            border-radius: 8px;
            color: var(--text-primary);
            font-size: 0.95rem;
            outline: none;
            resize: vertical;
            min-height: 110px;
            font-family: inherit;
            line-height: 1.6;
        }

        textarea.form-control:focus {
            border-color: var(--input-focus);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        }

        jdp-container {
            z-index: 999999 !important;
        }

        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.6);
            z-index: 99999;
            align-items: center;
            justify-content: center;
            padding: 15px;
        }

        .modal-card {
            background: var(--bg-card, #ffffff);
            border-radius: 12px;
            max-width: 520px;
            width: 100%;
            padding: 24px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            color: var(--text-primary);
        }

        .modal-card h3 {
            margin-top: 0;
            margin-bottom: 18px;
            font-size: 1.2rem;
            border-bottom: 2px solid var(--input-border, #e5e7eb);
            padding-bottom: 10px;
        }

        .badge-class {
            display: inline-block;
            background-color: rgba(59, 130, 246, 0.12);
            color: #2563eb;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .note-description {
            margin-top: 8px;
            color: var(--text-secondary);
            font-size: 0.88rem;
            line-height: 1.5;
            text-align: justify;
            word-break: break-word;
            white-space: pre-line;
            background: rgba(0, 0, 0, 0.02);
            padding: 8px;
            border-radius: 6px;
        }
    </style>
</head>

<body>

    <div id="loader"></div>

    <div class="container">
        <header class="page-header">
            <a href="../teacher_panel.php" class="btn-back" title="بازگشت به پنل">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 12H5M12 19l-7-7 7-7" />
                </svg>
                بازگشت به پنل
            </a>

            <button id="themeToggle" class="theme-toggle-btn" aria-label="تغییر تم">
                <i class="fa-solid fa-moon"></i>
            </button>
        </header>

        <main class="form-card">
            <h2>ثبت تمرین جدید</h2>

            <form action="" method="POST" enctype="multipart/form-data" id="uploadForm">

                <div class="form-group">
                    <label for="title">عنوان تمرین:</label>
                    <input type="text" id="title" name="title" required placeholder="مثلاً: تمرین سری اول - پودمان دوم">
                </div>

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

                <div class="form-group">
                    <label for="expiration_date">مهلت تحویل (انتخاب از تقویم):</label>
                    <input type="text" id="expiration_date" name="expiration_date" data-jdp required
                        placeholder="جهت باز شدن تقویم کلیک کنید" autocomplete="off" readonly>
                </div>

                <div class="form-group">
                    <label for="description">توضیحات تمرین (اختیاری):</label>
                    <textarea id="description" name="description" class="form-control" maxlength="500" rows="4"
                        placeholder="توضیحات یا دستورالعمل تمرین را وارد کنید (حداکثر ۵۰۰ کاراکتر)..."></textarea>
                    <small class="help-text">حداکثر ۵۰۰ کاراکتر.</small>
                </div>

                <div class="form-group">
                    <label for="assignment_file">فایل ضمیمه تمرین (اختیاری):</label>
                    <input type="file" id="assignment_file" name="assignment_file"
                        accept=".jpg,.jpeg,.png,.pdf,.ppt,.pptx,.doc,.docx,.accdb,.mdb,.xls,.xlsx,.sql,.txt">
                    <small class="help-text">در صورت نیاز می‌توانید فایل مربوطه را پیوست کنید.</small>
                </div>

                <div class="form-actions">
                    <button type="submit" name="submit_assignment" class="btn-submit">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                            <polyline points="17 8 12 3 7 8" />
                            <line x1="12" y1="3" x2="12" y2="15" />
                        </svg>
                        ثبت تمرین
                    </button>
                </div>

            </form>
        </main>

        <section class="notes-section">
            <h3>تمرین‌های ثبت شده شما (مرتب‌شده بر اساس پایه)</h3>

            <div class="notes-grid">
                <?php if (!empty($my_assignments)): ?>
                    <?php foreach ($my_assignments as $assignment): ?>
                        <div class="note-box">

                            <div style="text-align: center;">
                                <span class="badge-class">
                                    کلاس:
                                    <?php echo htmlspecialchars(($assignment['C_Grade'] ?? '') . ' ' . ($assignment['C_Major'] ?? 'عمومی')); ?>
                                </span>
                            </div>

                            <!-- بررسی اینکه آیا فایل وجود دارد و مقدار آن none نیست -->
                            <?php if (!empty($assignment['file_path']) && $assignment['file_path'] !== 'none'): ?>
                                <a href="<?php echo htmlspecialchars($assignment['file_path']); ?>" download
                                    class="file-download-link" title="دانلود فایل تمرین">
                                    <div class="file-icon">
                                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2">
                                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 2 2h12a2 2 0 0 2 2-2V8z"></path>
                                            <polyline points="14 2 14 8 20 8"></polyline>
                                            <line x1="12" y1="18" x2="12" y2="12"></line>
                                            <polyline points="9 15 12 18 15 15"></polyline>
                                        </svg>
                                    </div>
                                    <span class="note-title"><?php echo htmlspecialchars($assignment['title']); ?></span>
                                </a>
                            <?php else: ?>
                                <div class="file-download-link" style="cursor: default;">
                                    <div class="file-icon" style="color: var(--text-secondary);">
                                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2">
                                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 2 2h12a2 2 0 0 2 2-2V8z"></path>
                                            <polyline points="14 2 14 8 20 8"></polyline>
                                            <line x1="16" y1="13" x2="8" y2="13"></line>
                                            <line x1="16" y1="17" x2="8" y2="17"></line>
                                        </svg>
                                    </div>
                                    <span class="note-title"><?php echo htmlspecialchars($assignment['title']); ?></span>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($assignment['expiration_date'])): ?>
                                <span class="note-class"
                                    style="margin-top: 6px; color: #ef4444; font-weight: bold; text-align: center;">
                                    مهلت تحویل: <?php echo htmlspecialchars($assignment['expiration_date']); ?>
                                </span>
                            <?php endif; ?>

                            <?php if (!empty($assignment['description'])): ?>
                                <div class="note-description">
                                    <?php echo htmlspecialchars($assignment['description']); ?>
                                </div>
                            <?php endif; ?>

                            <div class="note-actions" style="margin-top: 12px;">
                                <button type="button" class="btn-edit" onclick="openEditModal(
                                    <?php echo $assignment['id']; ?>, 
                                    '<?php echo addslashes(htmlspecialchars($assignment['title'])); ?>', 
                                    '<?php echo addslashes(htmlspecialchars($assignment['expiration_date'])); ?>', 
                                    '<?php echo addslashes(htmlspecialchars($assignment['description'] ?? '')); ?>'
                                )">
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
                    <div class="no-notes">هنوز هیچ تمرینی ثبت نکرده‌اید.</div>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <!-- مدال ویرایش تمرین -->
    <div id="editModal" class="modal-overlay">
        <div class="modal-card">
            <h3>ویرایش تمرین</h3>
            <form action="" method="POST" enctype="multipart/form-data" id="fullEditForm">
                <input type="hidden" name="action" value="edit_assignment">
                <input type="hidden" name="assignment_id" id="modal_assignment_id">

                <div class="form-group">
                    <label for="modal_title">عنوان تمرین:</label>
                    <input type="text" id="modal_title" name="edit_title" required>
                </div>

                <div class="form-group">
                    <label for="modal_expiration_date">مهلت تحویل (تقویم):</label>
                    <input type="text" id="modal_expiration_date" name="edit_expiration_date" data-jdp required
                        autocomplete="off" readonly>
                </div>

                <div class="form-group">
                    <label for="modal_description">توضیحات تمرین (اختیاری):</label>
                    <textarea id="modal_description" name="edit_description" class="form-control" maxlength="500"
                        rows="4"></textarea>
                    <small class="help-text">حداکثر ۵۰۰ کاراکتر.</small>
                </div>

                <div class="form-group">
                    <label for="modal_file">فایل جدید (در صورت نیاز به تغییر):</label>
                    <input type="file" id="modal_file" name="edit_file"
                        accept=".jpg,.jpeg,.png,.pdf,.ppt,.pptx,.doc,.docx,.accdb,.mdb,.xls,.xlsx,.sql,.txt">
                    <small class="help-text">اگر فایلی انتخاب نکنید، فایل قبلی تغییری نمی‌کند.</small>
                </div>

                <div class="form-actions" style="margin-top: 20px; gap: 10px; display: flex;">
                    <button type="submit" class="btn-submit" style="flex: 1;">ذخیره تغییرات</button>
                    <button type="button" class="btn-back" onclick="closeEditModal()"
                        style="background: var(--input-border); color: var(--text-primary);">انصراف</button>
                </div>
            </form>
        </div>
    </div>

    <!-- فرم مخفی حذف -->
    <form id="deleteForm" action="" method="POST" style="display: none;">
        <input type="hidden" name="action" value="delete_assignment">
        <input type="hidden" name="assignment_id" id="delete_assignment_id">
    </form>

    <script>
        $(document).ready(function () {

            jalaliDatepicker.startWatch({
                hideAfterChange: true
            });

            <?php if (!empty($message)): ?>
                Swal.fire({
                    title: '<?php echo $messageType === "success" ? "موفقیت" : "خطا"; ?>',
                    text: '<?php echo $message; ?>',
                    icon: '<?php echo $messageType; ?>',
                    confirmButtonText: 'تأیید'
                });
            <?php endif; ?>

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

            $('#fullEditForm').on('submit', function (e) {
                var fileInput = $('#modal_file')[0];
                if (fileInput.files && fileInput.files.length > 0) {
                    var fileName = fileInput.files[0].name;
                    var ext = fileName.split('.').pop().toLowerCase();
                    var allowedExts = ['jpg', 'jpeg', 'png', 'pdf', 'ppt', 'pptx', 'doc', 'docx', 'accdb', 'mdb', 'xls', 'xlsx', 'sql', 'txt'];

                    if ($.inArray(ext, allowedExts) === -1) {
                        e.preventDefault();
                        Swal.fire({
                            title: 'خطای پسوند فایل',
                            text: 'فرمت فایل جدید مجاز نیست.',
                            icon: 'warning',
                            confirmButtonText: 'تأیید'
                        });
                    }
                }
            });

        });

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

        function openEditModal(id, title, expirationDate, description) {
            $('#modal_assignment_id').val(id);
            $('#modal_title').val(title);
            $('#modal_expiration_date').val(expirationDate);
            $('#modal_description').val(description);
            $('#modal_file').val('');
            $('#editModal').css('display', 'flex');
        }

        function closeEditModal() {
            $('#editModal').hide();
        }
    </script>

</body>

</html>