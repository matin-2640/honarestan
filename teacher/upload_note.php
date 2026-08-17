<?php
session_start();

if (!(isset($_SESSION["state_login"]) && $_SESSION["type"] == 1)) {
    header("location:../login.php");
    exit();
}

$teacher_id = $_SESSION['ID'];
$message = "";
$messageType = "";

include '../connect.php';

if (isset($_POST['action']) && $_POST['action'] === 'delete_note') {
    $note_id = intval($_POST['note_id']);

    try {
        $stmt = $connect->prepare("SELECT file_path FROM Notes WHERE id = :id AND teacher_id = :teacher_id");
        $stmt->execute([':id' => $note_id, ':teacher_id' => $teacher_id]);
        $note = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($note) {
            if (file_exists($note['file_path'])) {
                unlink($note['file_path']);
            }

            $delStmt = $connect->prepare("DELETE FROM Notes WHERE id = :id AND teacher_id = :teacher_id");
            $delStmt->execute([':id' => $note_id, ':teacher_id' => $teacher_id]);

            $message = "جزوه با موفقیت حذف شد.";
            $messageType = "success";
        } else {
            $message = "جزوه مورد نظر یافت نشد یا دسترسی حذف آن را ندارید.";
            $messageType = "error";
        }
    } catch (PDOException $e) {
        $message = "خطا در حذف جزوه: " . $e->getMessage();
        $messageType = "error";
    }
}

if (isset($_POST['action']) && $_POST['action'] === 'edit_note') {
    $note_id = intval($_POST['note_id']);
    $new_title = trim($_POST['new_title']);

    if (!empty($new_title)) {
        try {
            $stmt = $connect->prepare("UPDATE Notes SET title = :title WHERE id = :id AND teacher_id = :teacher_id");
            $stmt->execute([
                ':title' => $new_title,
                ':id' => $note_id,
                ':teacher_id' => $teacher_id
            ]);
            $message = "عنوان جزوه با موفقیت به‌روزرسانی شد.";
            $messageType = "success";
        } catch (PDOException $e) {
            $message = "خطا در به‌روزرسانی: " . $e->getMessage();
            $messageType = "error";
        }
    } else {
        $message = "عنوان جزوه نمی‌تواند خالی باشد.";
        $messageType = "error";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_note'])) {
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $class_id = isset($_POST['class_id']) ? intval($_POST['class_id']) : 0;

    if (empty($title) || empty($class_id) || !isset($_FILES['note_file']) || $_FILES['note_file']['error'] !== 0) {
        $message = "لطفاً تمامی فیلدها را به درستی پر کنید و فایل را انتخاب نمایید.";
        $messageType = "error";
    } else {
        $file = $_FILES['note_file'];
        $fileName = $file['name'];
        $fileTmpName = $file['tmp_name'];

        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowed = array('jpg', 'jpeg', 'png', 'pdf', 'ppt', 'pptx', 'doc', 'docx', 'accdb', 'mdb', 'xls', 'xlsx', 'sql', 'txt');

        if (in_array($fileExt, $allowed)) {
            $newFileName = time() . '_' . rand(1000, 9999) . '.' . $fileExt;
            $uploadDir = "../images/notes/";

            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileDestination = $uploadDir . $newFileName;

            if (move_uploaded_file($fileTmpName, $fileDestination)) {
                try {
                    $stmt = $connect->prepare("INSERT INTO Notes (title, file_path, class_id, teacher_id) VALUES (:title, :file_path, :class_id, :teacher_id)");
                    $result = $stmt->execute([
                        ':title' => $title,
                        ':file_path' => $fileDestination,
                        ':class_id' => $class_id,
                        ':teacher_id' => $teacher_id
                    ]);

                    if ($result) {
                        $message = "جزوه با موفقیت بارگذاری شد.";
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

$classes = array();
$my_notes = array();

if (isset($connect) && $connect) {
    try {
        $classes_query = "SELECT C_ID, C_Grade, C_Major FROM Classes ORDER BY C_Grade ASC";
        $stmt = $connect->query($classes_query);
        $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $notes_query = "SELECT N.id, N.title, N.file_path, C.C_Grade, C.C_Major 
                       FROM Notes N 
                       LEFT JOIN Classes C ON N.class_id = C.C_ID 
                       WHERE N.teacher_id = :teacher_id 
                       ORDER BY N.id DESC";
        $stmtNotes = $connect->prepare($notes_query);
        $stmtNotes->execute([':teacher_id' => $teacher_id]);
        $my_notes = $stmtNotes->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $classes = array();
        $my_notes = array();
    }
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>بارگذاری جزوه جدید</title>

    <link rel="icon" href="../images/icons/rahdanesh.png">
    <link rel="stylesheet" href="../styles/font.css">
    <link rel="stylesheet" href="../styles/note.css">
    <link rel="stylesheet" href="../js/sweetalert2.min.css">

    <script src="../js/jquery-1.10.2.min.js"></script>
    <script src="../js/sweetalert2.min.js"></script>
    <script src="../js/theme.js"></script>
</head>

<body>

    <div id="loader"></div>

    <div class="container">
        <style>
            .teacher-menu-header,
            .teacher-menu-header *,
            .teacher-sidebar,
            .teacher-sidebar *,
            .teacher-sidebar-overlay {
                box-sizing: border-box;
            }

            .teacher-menu-header {
                width: 100%;
                height: 64px;
                position: fixed;
                top: 0;
                right: 0;
                left: 0;
                z-index: 10000;
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 0 20px;
                background: var(--bg-card, #fff);
                border-bottom: 1px solid var(--border-color, #e2e8f0);
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
                direction: rtl;
            }

            .teacher-menu-toggle,
            .teacher-theme-toggle {
                width: 44px;
                height: 44px;
                padding: 0;
                border: 0;
                background: transparent;
                border-radius: 8px;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
            }

            .teacher-menu-toggle:hover,
            .teacher-theme-toggle:hover {
                background: var(--bg-main, #f8fafc);
            }

            .teacher-menu-logo {
                display: flex;
                align-items: center;
                gap: 8px;
                color: var(--text-main, #0f172a);
                font-weight: 700;
                font-size: 1.1rem;
            }

            .teacher-sidebar {
                width: 270px;
                position: fixed;
                top: 64px;
                right: -280px;
                bottom: 0;
                z-index: 10001;
                display: flex;
                flex-direction: column;
                background: var(--bg-card, #fff);
                border-left: 1px solid var(--border-color, #e2e8f0);
                box-shadow: -4px 0 20px rgba(0, 0, 0, 0.08);
                transition: right 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                direction: rtl;
            }

            .teacher-sidebar.teacher-active {
                right: 0;
            }

            .teacher-sidebar-brand {
                padding: 20px;
                display: flex;
                align-items: center;
                gap: 12px;
                color: var(--color-primary, #2563eb);
                font-size: 1.1rem;
                font-weight: 800;
                border-bottom: 1px solid var(--border-color, #e2e8f0);
            }

            .teacher-sidebar-nav {
                flex: 1;
                padding: 16px 12px;
                overflow-y: auto;
            }

            .teacher-sidebar-nav ul {
                list-style: none;
                padding: 0;
                margin: 0;
                display: flex;
                flex-direction: column;
                gap: 6px;
            }

            .teacher-sidebar-nav li {
                margin: 0;
                padding: 0;
            }

            .teacher-sidebar-nav a {
                display: flex;
                align-items: center;
                gap: 12px;
                padding: 12px 16px;
                border-radius: 8px;
                color: var(--text-muted, #64748b);
                text-decoration: none;
                font-size: 0.9rem;
                font-weight: 500;
                transition: 0.2s;
            }

            .teacher-sidebar-nav a:hover,
            .teacher-sidebar-nav a.teacher-current {
                background: var(--color-primary, #2563eb);
                color: #fff;
            }

            .teacher-sidebar-nav img,
            .teacher-sidebar-brand img,
            .teacher-sidebar-footer img {
                flex-shrink: 0;
            }

            .teacher-sidebar-footer {
                padding: 16px;
                border-top: 1px solid var(--border-color, #e2e8f0);
                display: flex;
                flex-direction: column;
                gap: 8px;
            }

            .teacher-sidebar-footer a {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
                padding: 12px;
                border-radius: 8px;
                text-decoration: none;
                font-size: 0.85rem;
                font-weight: 600;
            }

            .teacher-back-home {
                background: var(--bg-main, #f8fafc);
                color: var(--text-main, #0f172a);
            }

            .teacher-back-home:hover {
                background: var(--border-color, #e2e8f0);
            }

            .teacher-logout {
                background: rgba(239, 68, 68, 0.1);
                color: #ef4444;
            }

            .teacher-logout:hover {
                background: #ef4444;
                color: #fff;
            }

            .teacher-sidebar-overlay {
                position: fixed;
                top: 64px;
                left: 0;
                right: 0;
                bottom: 0;
                z-index: 9999;
                background: rgba(0, 0, 0, 0.4);
                backdrop-filter: blur(4px);
                opacity: 0;
                visibility: hidden;
                pointer-events: none;
                transition: 0.3s;
            }

            .teacher-sidebar-overlay.teacher-active {
                opacity: 1;
                visibility: visible;
                pointer-events: auto;
            }

            @media (max-width: 600px) {
                .teacher-menu-header {
                    height: 60px;
                    padding: 0 12px;
                }

                .teacher-menu-logo {
                    font-size: 0.9rem;
                }

                .teacher-menu-toggle,
                .teacher-theme-toggle {
                    width: 40px;
                    height: 40px;
                }

                .teacher-sidebar {
                    top: 60px;
                    width: 270px;
                    right: -280px;
                }

                .teacher-sidebar-overlay {
                    top: 60px;
                }
            }

            .teacher_menu_active {
                background: #2563eb !important;
                color: #fff !important;
            }
        </style>

        <header class="teacher-menu-header">
            <button class="teacher-menu-toggle" id="teacherMenuToggle" type="button">
                <img src="../images/icons/menu.png" width="25" height="25" />
            </button>

            <div class="teacher-menu-logo">
                <img src="../images/icons/user.png" width="25" height="25" />
                <span>پنل مدیریتی معلم</span>
            </div>

            <button class="teacher-theme-toggle" id="teacherThemeToggle" type="button">
                <img src="../images/icons/theme.png" width="25" height="25" />
            </button>
        </header>

        <aside class="teacher-sidebar" id="teacherSidebar">
            <div class="teacher-sidebar-brand">
                <img src="../images/icons/user.png" width="20" height="20" />
                <span>پنل معلم سیستم</span>
            </div>

            <nav class="teacher-sidebar-nav">
                <ul>
                    <li>
                        <a href="panel.php">
                            <img src="../images/icons/first.png" width="20" height="20" />
                            <span>خانه</span>
                        </a>
                    </li>

                    <li>
                        <a href="online_class/index.php">
                            <img src="../images/icons/playgray.png" width="20" height="20" />
                            <span>کلاس مجازی</span>
                        </a>
                    </li>

                    <li>
                        <a href="add_score_teacher.php">
                            <img src="../images/icons/uploadnote.png" width="20" height="20" />
                            <span>ثبت نمره</span>
                        </a>
                    </li>

                    <li>
                        <a href="upload_note.php" class="teacher_menu_active">
                            <img src="../images/icons/managescore.png" width="20" height="20" />
                            <span>بارگذاری جزوه</span>
                        </a>
                    </li>

                    <li>
                        <a href="upload_assignment.php">
                            <img src="../images/icons/check.png" width="20" height="20" />
                            <span>بارگذاری تمرین</span>
                        </a>
                    </li>

                    <li>
                        <a href="class_avg.php">
                            <img src="../images/icons/Chevron-left.png" width="20" height="20" />
                            <span>میانگین نمرات ترم</span>
                        </a>
                    </li>

      <li>
        <a href="../teacher_attendance_report.php">
          <img src="../images/icons/Chevron-left.png" width="20" height="20" />
          <span>لیست حضور و غیاب ها</span>
        </a>
      </li>
                </ul>
            </nav>

            <div class="teacher-sidebar-footer">
                <a href="../index.php" class="teacher-back-home">
                    <img src="../images/icons/back.png" width="20" height="20" />
                    <span>بازگشت به صفحه اصلی</span>
                </a>

                <a href="../logout.php" class="teacher-logout">
                    <img src="../images/icons/leave.png" width="20" height="20" />
                    <span>خروج از حساب</span>
                </a>
            </div>
        </aside>

        <div class="teacher-sidebar-overlay" id="teacherSidebarOverlay"></div>

        <script>
            (function () {
                const menuToggle = document.getElementById("teacherMenuToggle");
                const sidebar = document.getElementById("teacherSidebar");
                const overlay = document.getElementById("teacherSidebarOverlay");
                const themeToggle = document.getElementById("teacherThemeToggle");

                if (!menuToggle || !sidebar || !overlay) return;

                function openMenu() {
                    sidebar.classList.add("teacher-active");
                    overlay.classList.add("teacher-active");
                }

                function closeMenu() {
                    sidebar.classList.remove("teacher-active");
                    overlay.classList.remove("teacher-active");
                }

                menuToggle.addEventListener("click", function () {
                    if (sidebar.classList.contains("teacher-active")) {
                        closeMenu();
                    } else {
                        openMenu();
                    }
                });

                overlay.addEventListener("click", closeMenu);

                document.addEventListener("keydown", function (e) {
                    if (e.key === "Escape") {
                        closeMenu();
                    }
                });

                sidebar.querySelectorAll("a").forEach(function (link) {
                    link.addEventListener("click", closeMenu);
                });

                if (themeToggle) {
                    themeToggle.addEventListener("click", function () {
                        const html = document.documentElement;
                        const currentTheme = html.getAttribute("data-theme") || "light";
                        const newTheme = currentTheme === "dark" ? "light" : "dark";

                        html.setAttribute("data-theme", newTheme);
                        localStorage.setItem("theme", newTheme);
                    });
                }
            })();
        </script>
<br><br><br>

        <main class="form-card">
            <h2>بارگذاری جزوه جدید</h2>

            <form action="" method="POST" enctype="multipart/form-data" id="uploadForm">

                <div class="form-group">
                    <label for="title">عنوان جزوه:</label>
                    <input type="text" id="title" name="title" required placeholder="مثلاً: ۱۰ شبکه - فصل اول">
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
                    <label for="note_file">فایل جزوه:</label>
                    <input type="file" id="note_file" name="note_file" required
                        accept=".jpg,.jpeg,.png,.pdf,.ppt,.pptx,.doc,.docx,.accdb,.mdb,.xls,.xlsx,.sql,.txt">
                    <small class="help-text">پسوندهای مجاز: عکس، PDF، ورد، پاورپوینت، اکسس، اکسل، SQL، TXT</small>
                </div>

                <div class="form-actions">
                    <button type="submit" name="submit_note" class="btn-submit">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                            <polyline points="17 8 12 3 7 8" />
                            <line x1="12" y1="3" x2="12" y2="15" />
                        </svg>
                        ثبت و بارگذاری جزوه
                    </button>
                </div>

            </form>
        </main>

        <section class="notes-section">
            <h3>جزوات آپلود شده شما</h3>

            <div class="notes-grid">
                <?php if (!empty($my_notes)): ?>
                    <?php foreach ($my_notes as $note): ?>
                        <div class="note-box">
                            <!-- آیکون فایل و دانلود -->
                            <a href="<?php echo htmlspecialchars($note['file_path']); ?>" download class="file-download-link"
                                title="دانلود فایل جزوه">
                                <div class="file-icon">
                                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                        <polyline points="14 2 14 8 20 8"></polyline>
                                        <line x1="12" y1="18" x2="12" y2="12"></line>
                                        <polyline points="9 15 12 18 15 15"></polyline>
                                    </svg>
                                </div>
                                <span class="note-title"><?php echo htmlspecialchars($note['title']); ?></span>
                                <span
                                    class="note-class"><?php echo htmlspecialchars($note['C_Grade'] . ' ' . $note['C_Major']); ?></span>
                            </a>

                            <div class="note-actions">
                                <button type="button" class="btn-edit"
                                    onclick="editNote(<?php echo $note['id']; ?>, '<?php echo addslashes(htmlspecialchars($note['title'])); ?>')">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                    </svg>
                                    ویرایش
                                </button>

                                <button type="button" class="btn-delete" onclick="deleteNote(<?php echo $note['id']; ?>)">
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
                    <div class="no-notes">هنوز هیچ جزوه‌ای آپلود نکرده‌اید.</div>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <form id="deleteForm" action="" method="POST" style="display: none;">
        <input type="hidden" name="action" value="delete_note">
        <input type="hidden" name="note_id" id="delete_note_id">
    </form>

    <form id="editForm" action="" method="POST" style="display: none;">
        <input type="hidden" name="action" value="edit_note">
        <input type="hidden" name="note_id" id="edit_note_id">
        <input type="hidden" name="new_title" id="edit_new_title">
    </form>

    <script>
        $(document).ready(function () {

            <?php if (!empty($message)): ?>
                Swal.fire({
                    title: '<?php echo $messageType === "success" ? "موفقیت" : "خطا"; ?>',
                    text: '<?php echo $message; ?>',
                    icon: '<?php echo $messageType; ?>',
                    confirmButtonText: 'تأیید'
                });
            <?php endif; ?>

            $('#uploadForm').on('submit', function (e) {
                var fileInput = $('#note_file')[0];
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

        function deleteNote(noteId) {
            Swal.fire({
                title: 'آیا از حذف این جزوه اطمینان دارید؟',
                text: "این عملیات غیرقابل بازگشت است!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'بله، حذف شود',
                cancelButtonText: 'انصراف'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#delete_note_id').val(noteId);
                    $('#deleteForm').submit();
                }
            });
        }

        function editNote(noteId, currentTitle) {
            Swal.fire({
                title: 'ویرایش عنوان جزوه',
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
                    $('#edit_note_id').val(noteId);
                    $('#edit_new_title').val(result.value.trim());
                    $('#editForm').submit();
                }
            });
        }
    </script>

</body>

</html>