<?php
session_start();

if (!(isset($_SESSION["state_login"]) && $_SESSION["type"] == 1)) {
    header("location:../login.php");
    exit();
}

date_default_timezone_set('Asia/Tehran');

if (file_exists('jdf.php')) {
    include_once 'jdf.php';
} else {
    include_once '../jdf.php';
}

$teacher_id = $_SESSION['ID'];
$message = "";
$messageType = "";

include '../connect.php';

function convertNumbersToEnglish($string)
{
    if (empty($string))
        return '';
    $persian = array('۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹');
    $arabic = array('٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩');
    $num = range(0, 9);
    $string = str_replace($persian, $num, $string);
    $string = str_replace($arabic, $num, $string);
    return str_replace('-', '/', trim($string));
}
function normalizeJalaliDate($dateString)
{
    $cleanDate = convertNumbersToEnglish($dateString);
    $parts = explode('/', $cleanDate);
    if (count($parts) === 3) {
        $year = $parts[0];
        $month = str_pad($parts[1], 2, '0', STR_PAD_LEFT);
        $day = str_pad($parts[2], 2, '0', STR_PAD_LEFT);
        return "$year/$month/$day";
    }
    return $cleanDate;
}

function getTodayJalaliDate()
{
    if (function_exists('jdate')) {
        return jdate('Y/m/d', '', '', '', 'en');
    }
    return date('Y/m/d');
}

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
        $fileDestination = 'none';
        $uploadOk = true;

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

$classes = array();
$my_assignments = array();

if (isset($connect) && $connect) {
    try {
        $classes_query = "SELECT C_ID, C_Grade, C_Major FROM Classes ORDER BY C_Grade ASC, C_Major ASC";
        $stmt = $connect->query($classes_query);
        $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $assignments_query = "SELECT A.id, A.title, A.file_path, A.class_id, A.expiration_date, A.description, C.C_Grade, C.C_Major 
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
    <link rel="stylesheet" href="../styles/note.css">
    <link rel="stylesheet" href="../styles/font.css">
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

        .expired-badge {
            display: inline-block;
            background-color: #ef4444;
            color: #ffffff;
            font-size: 0.75rem;
            font-weight: bold;
            padding: 2px 8px;
            border-radius: 4px;
            margin-right: 6px;
        }

        .notes-header-filter {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 18px;
        }

        .notes-header-filter h3 {
            margin: 0;
        }

        .filter-select {
            padding: 8px 14px;
            border-radius: 8px;
            border: 1px solid var(--input-border);
            background-color: var(--input-bg);
            color: var(--text-primary);
            font-family: inherit;
            font-size: 0.9rem;
            outline: none;
            cursor: pointer;
        }

        .filter-select:focus {
            border-color: var(--input-focus);
        }

        .no-notes-filter {
            display: none;
            width: 100%;
            text-align: center;
            padding: 30px;
            color: var(--text-secondary);
            background: var(--bg-card);
            border-radius: 8px;
            font-weight: 500;
        }
    </style>
</head>

<body>
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
  .teacher_menu_active{
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
        <a href="upload_note.php">
          <img src="../images/icons/managescore.png" width="20" height="20" />
          <span>بارگذاری جزوه</span>
        </a>
      </li>

      <li>
        <a href="upload_assignment.php" class="teacher_menu_active">
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
                <img src="../images/icons/theme.png" width="25px" height="25px" />
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
            <div class="notes-header-filter">
                <h3>تمرین‌های ثبت شده شما</h3>
                <div>
                    <label for="classFilter" style="font-size:0.9rem; margin-left: 6px;">نمایش کلاس:</label>
                    <select id="classFilter" class="filter-select">
                        <option value="all">همه کلاس‌ها</option>
                        <?php
                        if (!empty($classes)) {
                            foreach ($classes as $class) {
                                echo '<option value="' . htmlspecialchars($class['C_ID']) . '">' . htmlspecialchars($class['C_Grade']) . ' ' . htmlspecialchars($class['C_Major']) . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div class="notes-grid">
                <?php if (!empty($my_assignments)): ?>
                    <?php
                    $todayJalali = normalizeJalaliDate(getTodayJalaliDate());
                    ?>

                    <?php foreach ($my_assignments as $assignment): ?>
                        <?php
                        $isExpired = false;
                        if (!empty($assignment['expiration_date'])) {
                            $expClean = normalizeJalaliDate($assignment['expiration_date']);
                            if ($expClean < $todayJalali) {
                                $isExpired = true;
                            }
                        }
                        ?>

                        <div class="note-box" data-class-id="<?php echo htmlspecialchars($assignment['class_id']); ?>">

                            <div style="text-align: center;">
                                <span class="badge-class">
                                    کلاس:
                                    <?php echo htmlspecialchars(($assignment['C_Grade'] ?? '') . ' ' . ($assignment['C_Major'] ?? 'عمومی')); ?>
                                </span>
                            </div>

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
                                    style="margin-top: 6px; color: <?php echo $isExpired ? '#ef4444' : '#6b7280'; ?>; font-weight: bold; text-align: center;">
                                    <?php if ($isExpired): ?>
                                        <span class="expired-badge">منقضی شده</span>
                                    <?php endif; ?>
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

                    <div id="noAssignmentsFilter" class="no-notes-filter">برای این کلاس تمرینی ثبت نشده است.</div>

                <?php else: ?>
                    <div class="no-notes">هنوز هیچ تمرینی ثبت نکرده‌اید.</div>
                <?php endif; ?>
            </div>
        </section>
    </div>

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

            $('#classFilter').on('change', function () {
                var selectedClass = $(this).val();
                var visibleCount = 0;

                if (selectedClass === 'all') {
                    $('.note-box').show();
                    visibleCount = $('.note-box').length;
                } else {
                    $('.note-box').each(function () {
                        if ($(this).attr('data-class-id') === selectedClass) {
                            $(this).show();
                            visibleCount++;
                        } else {
                            $(this).hide();
                        }
                    });
                }

                if (visibleCount === 0) {
                    $('#noAssignmentsFilter').show();
                } else {
                    $('#noAssignmentsFilter').hide();
                }
            });

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