<?php
session_start();
if (!(isset($_SESSION["state_login"]) && $_SESSION["type"] == 1)) {
  header("location:../login.php");
  exit();
}

include("../connect.php");

$teacher_id = 0;

if (isset($_SESSION["ID"])) {
  $teacher_id = intval($_SESSION["ID"]);
}


try {
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
  <title>ثبت نمره | پنل معلم</title>
  <link rel="stylesheet" href="../styles/font.css">
  <link rel="icon" href="../images/icons/rahdanesh.png">
  <link rel="stylesheet" href="../styles/score_style.css">

  <script src="../js/jquery-1.10.2.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
          <a href="add_score_teacher.php" class="teacher_menu_active">
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


  <main class="panel-container profile-layout">
    <form action="add_score_back.php" method="POST" id="scoreForm" class="register-form">

      <section class="profile-card">
        <div class="profile-card-header">
          <h2 class="profile-student-name">
            <svg viewBox="0 0 24 24" class="inline-svg">
              <path
                d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z" />
            </svg>
            ثبت نمرات
          </h2>
          <p class="profile-student-sub">مشخصات کلاس، درس و دوره را جهت ثبت نمرات انتخاب کنید.</p>
        </div>

        <div class="profile-info-grid">
          <div class="info-item">
            <label for="C_ID">انتخاب کلاس<span class="required-star">*</span></label>
            <div class="select-wrapper input-with-icon">
              <select id="C_ID" name="C_ID" class="info-value-box input-field select-field" required>
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
              <span class="field-icon-box">
                <svg viewBox="0 0 24 24" class="inline-svg">
                  <path
                    d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z" />
                </svg>
              </span>
            </div>
          </div>

          <div class="info-item">
            <label for="G_courseID">انتخاب درس<span class="required-star">*</span></label>
            <div class="select-wrapper input-with-icon">
              <select id="G_courseID" name="G_courseID" class="info-value-box input-field select-field" required
                disabled>
                <option value="" disabled selected hidden>ابتدا کلاس را انتخاب کنید...</option>
              </select>
              <span class="field-icon-box">
                <svg viewBox="0 0 24 24" class="inline-svg">
                  <path
                    d="M18 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zM6 4h5v8l-2.5-1.5L6 12V4z" />
                </svg>
              </span>
            </div>
          </div>

          <div class="info-item">
            <label for="G_term">انتخاب دوره<span class="required-star">*</span></label>
            <div class="select-wrapper input-with-icon">
              <select id="G_term" name="G_term" class="info-value-box input-field select-field" required>
                <option value="" disabled selected hidden>انتخاب دوره...</option>
                <option value="1">مهر و آبان</option>
                <option value="2">آذر</option>
                <option value="3">نوبت اول</option>
                <option value="4">اسفند</option>
                <option value="5">فروردین و اردیبهشت</option>
                <option value="6">نوبت دوم</option>
              </select>
              <span class="field-icon-box">
                <svg viewBox="0 0 24 24" class="inline-svg">
                  <path
                    d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11z" />
                </svg>
              </span>
            </div>
          </div>
        </div>
      </section>

      <section class="profile-card margin-top-card">
        <div class="list-header-flex">
          <h2 class="list-main-title">لیست دانش‌آموزان</h2>
          <div class="student-count-badge">
            تعداد: <span id="student_count_num">0</span> نفر
          </div>
        </div>

        <div id="students_container" class="students-table-wrapper">
          <p class="empty-msg">لطفاً کلاس، درس و دوره را انتخاب کنید.</p>
        </div>

        <div class="profile-actions-footer register-actions">
          <button type="submit" class="btn-back-home btn-submit-register">
            ثبت نهایی نمرات
          </button>
        </div>
      </section>
    </form>
  </main>

  <script>
    $(document).ready(function () {

      <?php if (isset($_SESSION['add_score'])): ?>
        Swal.fire({
          icon: 'success',
          title: 'موفقیت‌آمیز',
          text: 'نمرات دانش‌آموزان با موفقیت ثبت شد',
          confirmButtonText: 'باشه'
        });
        <?php unset($_SESSION['add_score']); ?>
      <?php endif; ?>

      <?php if (isset($_SESSION['score_error'])): ?>
        Swal.fire({
          icon: 'warning',
          title: 'خطای نمره',
          text: 'نمره باید بین 0 تا 20 وارد شود',
          confirmButtonText: 'متوجه شدم'
        });
        <?php unset($_SESSION['score_error']); ?>
      <?php endif; ?>

      <?php if (isset($_SESSION['send_error'])): ?>
        Swal.fire({
          icon: 'error',
          title: 'خطا',
          text: 'خطا در ثبت اطلاعات. لطفا دوباره امتحان کنید',
          confirmButtonText: 'تلاش مجدد'
        });
        <?php unset($_SESSION['send_error']); ?>
      <?php endif; ?>

      function loadStudents() {
        var classID = $('#C_ID').val();
        var courseID = $('#G_courseID').val();
        var term = $('#G_term').val();

        if (classID && courseID && term) {
          $.ajax({
            url: 'get_grade_data.php',
            type: 'POST',
            data: {
              action: 'get_students',
              class_id: classID,
              course_id: courseID,
              term: term
            },
            dataType: 'html',
            success: function (htmlResponse) {
              $('#students_container').html(htmlResponse);
              var totalStudents = $('#students_container .student-row').length;
              $('#student_count_num').text(totalStudents);
            }
          });
        }
      }
      $('#C_ID').on('change', function () {
        var classID = $(this).val();

        if (classID) {
          $.ajax({
            url: 'get_grade_data.php',
            type: 'POST',
            data: { action: 'get_teacher_courses', class_id: classID },
            dataType: 'json',
            success: function (courses) {
              var courseSelect = $('#G_courseID');
              courseSelect.empty();
              courseSelect.append('<option value="" disabled selected hidden>انتخاب درس...</option>');

              if (courses && courses.length > 0) {
                $.each(courses, function (index, course) {
                  courseSelect.append('<option value="' + course.Co_ID + '">' + course.Co_name + '</option>');
                });
                courseSelect.prop('disabled', false);
              } else {
                courseSelect.append('<option value="" disabled>درسی برای شما در این کلاس ثبت نشده است</option>');
                courseSelect.prop('disabled', true);
              }
              $('#students_container').html('<p class="empty-msg">لطفاً درس و دوره را انتخاب کنید.</p>');
              $('#student_count_num').text(0);
            }
          });
        }
      });

      $('#G_courseID, #G_term').on('change', function () {
        loadStudents();
      });
    });
  </script>
  <script src="../js/theme.js"></script>
</body>

</html>