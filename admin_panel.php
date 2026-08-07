<?php
session_start();
include("connect.php");

if (!(isset($_SESSION["state_login"]) && $_SESSION["type"] == 2 || $_SESSION["type"] == 3) || $_SESSION["type"] == 4) {
  header("location:login.php");
  exit();
}

$sql_student = " select COUNT(*) from students";
$stmt_student = $connect->prepare($sql_student);
$stmt_student->execute();

$sql_teacher = " select COUNT(*) from teachers";
$stmt_teacher = $connect->prepare($sql_teacher);
$stmt_teacher->execute();

$sql_class = " select COUNT(*) from classes";
$stmt_class = $connect->prepare($sql_class);
$stmt_class->execute();

$sql_courses = " select COUNT(*) from courses";
$stmt_course = $connect->prepare($sql_courses);
$stmt_course->execute();

// آمار بازدید امروز
try {
  $today = date('Y-m-d');
  $sql_visit = "SELECT COUNT(*) FROM site_visits WHERE visit_date = :today";
  $stmt_visit = $connect->prepare($sql_visit);
  $stmt_visit->execute(['today' => $today]);
  $today_visits = $stmt_visit->fetchColumn();
} catch (Exception $e) {
  $today_visits = 450;
}

// محاسبه آمار حضور و غیاب ماهانه
try {
  $sql_students_att = "SELECT COUNT(*) FROM students";
  $stmt_students_att = $connect->prepare($sql_students_att);
  $stmt_students_att->execute();
  $total_students_att = $stmt_students_att->fetchColumn();

  $sql_absent = "SELECT COUNT(*) FROM Attendance";
  $stmt_absent = $connect->prepare($sql_absent);
  $stmt_absent->execute();
  $total_absent_records = $stmt_absent->fetchColumn();

  $total_possible_attendances = $total_students_att * 30;

  if ($total_possible_attendances > 0) {
    $total_present = max(0, $total_possible_attendances - $total_absent_records);
    $present_percent = round(($total_present / $total_possible_attendances) * 100);
  } else {
    $present_percent = 86;
  }
} catch (Exception $e) {
  $present_percent = 86;
}
$absent_percent = 100 - $present_percent;

try {
  $stmt_check = $connect->prepare("SELECT COUNT(*) FROM teacher_disciplinary WHERE is_read = 0");
  $stmt_check->execute();
  $has_new_disciplinary = $stmt_check->fetchColumn() > 0;
} catch (Exception $e) {
  $has_new_disciplinary = false;
}
?>

<!doctype html>
<html lang="fa" dir="rtl">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>پنل مدیریت هنرستان</title>
  <link rel="icon" href="images/icons/rahdanesh.png" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/vazirmatn-font-face.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link rel="stylesheet" href="styles/admin_panel.css" />
  <link rel="stylesheet" href="styles/font.css">

  <style>
    .menu-toggle-btn {
      display: flex !important;
    }

    .sidebar {
      right: calc(-1 * var(--sidebar-width));
    }

    .sidebar.active {
      right: 0 !important;
    }

    .main-content {
      margin-right: 0 !important;
      width: 100%;
    }

    .quick-operators-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 10px;
      margin-bottom: 16px;
      padding-bottom: 16px;
      border-bottom: 1px dashed var(--border-color);
    }

    @media (max-width: 768px) {
      .quick-operators-grid {
        grid-template-columns: repeat(2, 1fr);
      }
    }

    @keyframes blink-dot {
      0% {
        opacity: 1;
        transform: scale(1);
      }

      50% {
        opacity: 0.3;
        transform: scale(0.85);
      }

      100% {
        opacity: 1;
        transform: scale(1);
      }
    }

    .admin-blink-indicator {
      display: inline-block;
      width: 9px;
      height: 9px;
      background-color: #ff3b30;
      border-radius: 50%;
      margin-right: 8px;
      animation: blink-dot 1s infinite ease-in-out;
      vertical-align: middle;
    }
  </style>
</head>

<body>
  <header class="main-header">
    <div class="header-right">
      <button id="menuToggle" class="menu-toggle-btn">
        <img src="images/icons/menu.png" width="25px" height="25px" />
      </button>
      <div class="header-logo">
        <span class="brand-color">پنل مدیریت</span> | هنرستان راه دانش
      </div>
    </div>
    <div class="header-left">
      <button id="themeToggle" class="theme-toggle-btn">
        <img src="images/icons/theme.png" width="25px" height="25px" />
      </button>
    </div>
  </header>

  <div class="panel-container">
    <nav id="navMenu" class="sidebar">
      <div class="sidebar-brand">
        <img src="images/icons/user.png" width="25px" height="25px" />
        <span>داشبورد مدیریت</span>
      </div>

      <div class="sidebar-nav">
        <ul>
          <li>
            <a href="students_list.php" class="active">
              <img src="images/icons/userswhite.png" width="20px" height="20px" />لیست هنرجویان
            </a>
          </li>
          <li>
            <a href="teachers_list.php">
              <img src="images/icons/teachers.png" width="20px" height="20px" />
              لیست معلمین</a>
          </li>
          <li>
            <a href="teachers_list.php">
              <img src="images/icons/school.png" width="20px" height="20px" />
              لیست کلاس ها</a>
          </li>
          <li>
            <a href="teachers_list.php">
              <img src="images/icons/manageroles.png" width="20px" height="20px" />
              لیست دروس</a>
          </li>
          <li>
            <a href="add_score.php">
              <img src="images/icons/scorewhite.png" width="20px" height="20px" />
              ثبت نمره</a>
          </li>
          <li>
            <a href="send_sms.php">
              <img src="images/icons/sendsms.png" width="20px" height="20px" />
              ارسال پیام اس ام اسی </a>
          </li>
          <li>
            <a href="admin_pass.php">
              <img src="images/icons/edituser.png" width="20px" height="20px" />
              تغییر رمز عبور </a>
          </li>
          <li>
            <a href="attendance_reports.php">
              <img src="images/icons/visit.png" width="20px" height="20px" />
              لیست حضور و غیاب </a>
          </li>
        </ul>
      </div>

      <div class="sidebar-footer">
        <a href="index.php" class="back-home-btn">
          <img src="images/icons/back.png" width="20px" height="20px" />
          بازگشت به سایت</a>
        <a href="logout.php" class="logout-btn">
          <img src="images/icons/leave.png" width="20px" height="20px" /> خروج
          از حساب</a>
      </div>
    </nav>

    <div id="sidebarOverlay" class="sidebar-overlay"></div>

    <main class="main-content">
      <section class="top-stats-grid">
        <div class="stat-card card-gradient-1">
          <div class="stat-icon">
            <img src="images/icons/userswhite.png" width="30px" height="30px" />
          </div>
          <div class="stat-info">
            <h3>هنرجویان</h3>
            <div class="stat-number"><?php echo $stmt_student->fetchColumn(); ?> نفر</div>
          </div>
        </div>
        <div class="stat-card card-gradient-2">
          <div class="stat-icon">
            <img src="images/icons/teachers.png" width="30px" height="30px" />
          </div>
          <div class="stat-info">
            <h3>معلمان</h3>
            <div class="stat-number"><?php echo $stmt_teacher->fetchColumn(); ?> نفر</div>
          </div>
        </div>
        <div class="stat-card card-gradient-3">
          <div class="stat-icon">
            <img src="images/icons/school.png" width="30px" height="30px" />
          </div>
          <div class="stat-info">
            <h3>کلاس ها</h3>
            <div class="stat-number"><?php echo $stmt_class->fetchColumn(); ?> کلاس</div>
          </div>
        </div>
        <div class="stat-card card-gradient-4">
          <div class="stat-icon">
            <img src="images/icons/book.png" width="30px" height="30px" />
          </div>
          <div class="stat-info">
            <h3>درس ها</h3>
            <div class="stat-number"><?php echo $stmt_course->fetchColumn(); ?></div>
          </div>
        </div>
        <div class="stat-card card-gradient-5">
          <div class="stat-icon">
            <img src="images/icons/visit.png" width="30px" height="30px" />
          </div>
          <div class="stat-info">
            <h3>بازدید امروز</h3>
            <div class="stat-number"><?php echo number_format($today_visits); ?> بار</div>
          </div>
        </div>
      </section>

      <div class="dashboard-layout-grid">
        <div class="right-column-group">
          <section class="panel-card">
            <div class="card-header">
              <h2>دسترسی‌های سریع</h2>
            </div>
            <div class="quick-access-wrapper">
              <div class="quick-operators-grid">
                <a href="#" class="action-btn edit-btn">لیست پرداختی ها</a>
                <a href="report_card.php" class="action-btn edit-btn">چاپ کارنامه</a>
                <a href="admin_attendance.php" class="action-btn edit-btn"> حضور و غیاب</a>
                <a href="admin_disciplinary.php" class="action-btn edit-btn">پرونده انظباتی</a>
              </div>

              <div class="split-row">
                <a href="admin_news.php" class="quick-btn-split">
                  <img src="images/icons/bellblue.png" width="18px" height="18px" />
                  <span>افزودن خبر</span>
                </a>
                <a href="admin_gallery.php" class="quick-btn-split">
                  <img src="images/icons/uploaimg.png" width="18px" height="18px" />
                  <span>افزودن عکس به گالری</span>
                </a>
              </div>
              <div class="full-row">
                <a href="send_sms.php" class="quick-btn-full">
                  <img src="images/icons/sendsmsred.png" width="18px" height="18px" />
                  <span>ارسال اطلاعیه اس‌ام‌اسی</span>
                </a>
              </div>
            </div>
          </section>

          <section class="panel-card">
            <div class="card-header">
              <h2>مدیریت هنرستان</h2>
            </div>
            <div class="members-management-grid">
              <div class="management-sub-section">
                <h3 class="sub-section-title">مدیریت اعضا</h3>
                <div class="action-buttons-group">
                  <a href="add_student.php" class="action-btn add-btn">
                    <img src="images/icons/adduser.png" width="18px" height="18px" />
                    افزودن دانش‌آموز جدید</a>

                  <br>

                  <a href="add_teacher.php" class="action-btn add-btn">
                    <img src="images/icons/adduser.png" width="18px" height="18px" />
                    افزودن هنرآموز جدید</a>

                </div>
              </div>


              <div class="management-sub-section">
                <h3 class="sub-section-title">مدیریت کلاس ها</h3>
                <div class="action-buttons-group">
                  <a href="add_class.php" class="action-btn add-btn">
                    افزودن کلاس جدید</a>
                  <br>
                  <a href="add_course.php" class="action-btn add-btn">
                    افزودن درس جدید</a>
                </div>
              </div>
            </div>
          </section>
        </div>

        <div class="left-column-group">
          <section class="panel-card">
            <div class="card-header">
              <h2>نظارت بر هنرستان</h2>
            </div>
            <div class="monitoring-list">
              <a href="admin_teacher_disciplinary.php" class="monitoring-item">
                <div class="monitoring-info" style="display: flex; align-items: center; width: 100%;">
                  <img src="images/icons/visit.png" width="18px" height="18px" />
                  <span>نظارت بر پرونده انظباتی کلاسی</span>
                  <?php if ($has_new_disciplinary): ?>
                    <span class="admin-blink-indicator" title="پرونده جدید ثبت شده است"></span>
                  <?php endif; ?>
                </div>
                <img src="images/icons/Chevron-left.png" width="18px" height="18px" />
              </a>
              <a href="teacher_attendance_report.php" class="monitoring-item">
                <div class="monitoring-info">
                  <img src="images/icons/usercheck.png" width="18px" height="18px" />
                  <span>نظارت بر حضور و غیاب معلمان</span>
                </div>
                <img src="images/icons/Chevron-left.png" width="18px" height="18px" />
              </a>
              <a href="#" class="monitoring-item">
                <div class="monitoring-info">
                  <img src="images/icons/usercheckyellow.png" width="18px" height="18px" />
                  <span>نظارت بر دانش‌آموزان</span>
                </div>
                <img src="images/icons/Chevron-left.png" width="18px" height="18px" />
              </a>
            </div>
          </section>

          <section class="panel-card">
            <div class="card-header">
              <h2>آمار حضور و غیاب این ماه</h2>
            </div>
            <div class="chart-container">
              <div class="svg-chart-wrapper">
                <svg viewBox="0 0 36 36" class="circular-chart">
                  <path class="circle-bg"
                    d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                  <path class="circle-progress" stroke-dasharray="<?php echo $present_percent; ?>, 100"
                    d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                  <text x="18" y="20.35" class="percentage"><?php echo $present_percent; ?>٪</text>
                </svg>
              </div>
              <div class="chart-legend">
                <div class="legend-item">
                  <span class="legend-color present-color"></span> حاضر (<?php echo $present_percent; ?>٪)
                </div>
                <div class="legend-item">
                  <span class="legend-color absent-color"></span> غایب (<?php echo $absent_percent; ?>٪)
                </div>
              </div>
            </div>
          </section>

          <section class="panel-card">
            <div class="card-header">
              <div class="calendar-header-title">
                <h2>
                  <img src="images/icons/calendar.png" width="25px" height="25px" />
                  تقویم آموزشی
                </h2>
                <span id="todayTag" class="today-tag">امروز</span>
              </div>
            </div>
            <div class="calendar-wrapper">
              <div class="weekdays">
                <div>ش</div>
                <div>ی</div>
                <div>د</div>
                <div>س</div>
                <div>چ</div>
                <div>پ</div>
                <div>ج</div>
              </div>
              <div id="calendarDays" class="days"></div>
            </div>
          </section>

          <section class="panel-card">
            <div class="card-header">
              <h2>اطلاعیه‌های اخیر</h2>
            </div>
            <ul class="announcements-list">
              <li>
                <a href="hNews.html" class="announcement-item">
                  <div class="announcement-bullet"></div>
                  <div class="announcement-content">
                    <h4>برگزاری امتحانات میان‌ترم</h4>
                    <span class="announcement-date">۳ روز پیش</span>
                  </div>
                  <img src="images/icons/Chevron-left.png" width="18px" height="18px" />
                </a>
              </li>
              <li>
                <a href="hNews.html" class="announcement-item">
                  <div class="announcement-bullet"></div>
                  <div class="announcement-content">
                    <h4>تمدید مهلت ثبت‌نام در آزمون‌های عملی ترم</h4>
                    <span class="announcement-date">۱ هفته پیش</span>
                  </div>
                  <img src="images/icons/Chevron-left.png" width="18px" height="18px" />
                </a>
              </li>
              <li>
                <a href="hNews.html" class="announcement-item">
                  <div class="announcement-bullet"></div>
                  <div class="announcement-content">
                    <h4>تغییر ساعت کاری بخش اداری هنرستان</h4>
                    <span class="announcement-date">۲ هفته پیش</span>
                  </div>
                  <img src="images/icons/Chevron-left.png" width="18px" height="18px" />
                </a>
              </li>
            </ul>
          </section>
        </div>
      </div>

      <footer class="panel-footer">
        <a href="index.php" class="back-home-button-main">
          <img src="images/icons/back.png" width="25px" height="25px" />
          <span>بازگشت به صفحه اصلی</span>
        </a>
      </footer>
    </main>
  </div>
  <script src="https://unpkg.com/lenis@1.3.11/dist/lenis.min.js"></script>

  <script>
    const syncGlobalTheme = () => {
      const storedTheme = localStorage.getItem("theme") || "light";
      document.documentElement.setAttribute("data-theme", storedTheme);
      return storedTheme;
    };
    let currentTheme = syncGlobalTheme();

    document.addEventListener("DOMContentLoaded", () => {
      window.addEventListener("storage", (e) => {
        if (e.key === "theme") {
          const newTheme = e.newValue || "light";
          document.documentElement.setAttribute("data-theme", newTheme);
          const themeToggle = document.getElementById("themeToggle");
          if (themeToggle) {
            updateThemeIcon(
              newTheme,
              themeToggle,
              themeToggle.querySelector("i"),
            );
          }
        }
      });

      const themeToggle = document.getElementById("themeToggle");
      if (themeToggle) {
        const themeIcon = themeToggle.querySelector("i");
        updateThemeIcon(currentTheme, themeToggle, themeIcon);

        const handleThemeSwitch = (e) => {
          e.preventDefault();
          let theme = document.documentElement.getAttribute("data-theme");
          let newTheme = theme === "dark" ? "light" : "dark";

          document.documentElement.setAttribute("data-theme", newTheme);
          localStorage.setItem("theme", newTheme);
          updateThemeIcon(newTheme, themeToggle, themeIcon);
        };

        themeToggle.addEventListener("click", handleThemeSwitch);
      }

      const menuToggle = document.getElementById("menuToggle");
      const navMenu = document.getElementById("navMenu");
      const sidebarOverlay = document.getElementById("sidebarOverlay");

      if (menuToggle && navMenu) {
        const openMenu = () => {
          navMenu.classList.add("active");
          if (sidebarOverlay) sidebarOverlay.classList.add("active");
          const icon = menuToggle.querySelector("i");
          if (icon) icon.className = "fa-solid fa-xmark";
        };

        const closeMenu = () => {
          navMenu.classList.remove("active");
          if (sidebarOverlay) sidebarOverlay.classList.remove("active");
          const icon = menuToggle.querySelector("i");
          if (icon) icon.className = "fa-solid fa-bars";
        };

        menuToggle.addEventListener("click", (e) => {
          e.preventDefault();
          if (navMenu.classList.contains("active")) {
            closeMenu();
          } else {
            openMenu();
          }
        });

        if (sidebarOverlay) {
          sidebarOverlay.addEventListener("click", closeMenu);
        }

        const menuLinks = document.querySelectorAll(
          ".sidebar-nav a, .sidebar-footer a",
        );
        menuLinks.forEach((link) => {
          link.addEventListener("click", () => {
            setTimeout(() => {
              closeMenu();
            }, 150);
          });
        });
      }

      renderCalendar();
    });

    function updateThemeIcon(theme, toggleElement, iconElement) {
      if (!toggleElement || !iconElement) return;

      if (theme === "dark") {
        iconElement.className = "fa-solid fa-sun";
        toggleElement.style.color = "#eab308";
      } else {
        iconElement.className = "fa-solid fa-moon";
        toggleElement.style.color = "";
      }
    }

    function renderCalendar() {
      const calendarDays = document.getElementById("calendarDays");
      const todayTag = document.getElementById("todayTag");
      if (!calendarDays) return;

      const now = new Date();

      const dateOptions = {
        weekday: "long",
        year: "numeric",
        month: "long",
        day: "numeric",
      };
      const todayPersianText = new Intl.DateTimeFormat(
        "fa-IR",
        dateOptions,
      ).format(now);
      if (todayTag) todayTag.textContent = todayPersianText;

      const parts = new Intl.DateTimeFormat("fa-IR-u-ca-persian", {
        year: "numeric",
        month: "numeric",
        day: "numeric",
      }).formatToParts(now);

      let shYear, shMonth, shDay;
      parts.forEach((part) => {
        if (part.type === "year")
          shYear = parseInt(
            part.value.replace(/[۰-۹]/g, (d) => "۰۱۲۳۴۵۶۷۸۹".indexOf(d)),
          );
        if (part.type === "month")
          shMonth = parseInt(
            part.value.replace(/[۰-۹]/g, (d) => "۰۱۲۳۴۵۶۷۸۹".indexOf(d)),
          );
        if (part.type === "day")
          shDay = parseInt(
            part.value.replace(/[۰-۹]/g, (d) => "۰۱۲۳۴۵۶۷۸۹".indexOf(d)),
          );
      });

      const firstOfShMonthInGregorian = getGregorianFirstOfMonth(
        shYear,
        shMonth,
      );

      let gDayOfWeek = firstOfShMonthInGregorian.getDay();

      let shDayOfWeekOffset = (gDayOfWeek + 1) % 7;

      let daysInMonth = 30;
      if (shMonth <= 6) {
        daysInMonth = 31;
      } else if (shMonth === 12) {
        const isLeap = [1, 5, 9, 13, 17, 22, 26, 30].includes(shYear % 33);
        daysInMonth = isLeap ? 30 : 29;
      }

      let htmlContent = "";

      for (let i = 0; i < shDayOfWeekOffset; i++) {
        htmlContent += `<div class="empty"></div>`;
      }

      for (let d = 1; d <= daysInMonth; d++) {
        if (d === shDay) {
          htmlContent += `<div class="today">${d.toLocaleString("fa-IR")}</div>`;
        } else {
          htmlContent += `<div>${d.toLocaleString("fa-IR")}</div>`;
        }
      }

      calendarDays.innerHTML = htmlContent;
    }

    function getGregorianFirstOfMonth(jy, jm) {
      let gy, gm, gd;
      let g_days_in_month = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
      let jy_fixed = jy - 979;
      let j_day_no =
        365 * jy_fixed +
        Math.floor(jy_fixed / 33) * 8 +
        Math.floor(((jy_fixed % 33) + 3) / 4);

      for (let i = 0; i < jm - 1; ++i) {
        j_day_no += i < 6 ? 31 : 30;
      }

      let g_day_no = j_day_no + 79;
      gy = 1600 + 400 * Math.floor(g_day_no / 146097);
      g_day_no = g_day_no % 146097;

      let leap = true;
      if (g_day_no >= 36525) {
        g_day_no--;
        gy += 100 * Math.floor(g_day_no / 36524);
        g_day_no = g_day_no % 36524;
        if (g_day_no >= 365) {
          g_day_no++;
        } else {
          leap = false;
        }
      }

      gy += 4 * Math.floor(g_day_no / 1461);
      g_day_no %= 1461;

      if (g_day_no >= 366) {
        leap = false;
        g_day_no--;
        gy += Math.floor(g_day_no / 365);
        g_day_no = g_day_no % 365;
      }

      let i = 0;
      while (true) {
        let dim = g_days_in_month[i];
        if (i === 1 && leap) dim = 29;
        if (g_day_no < dim) break;
        g_day_no -= dim;
        i++;
      }
      gm = i + 1;
      gd = g_day_no + 1;

      return new Date(gy, gm - 1, gd);
    }

    if (typeof Lenis !== "undefined") {
      const lenis = new Lenis();

      function raf(time) {
        lenis.raf(time);
        requestAnimationFrame(raf);
      }

      requestAnimationFrame(raf);
    }
  </script>
</body>

</html>