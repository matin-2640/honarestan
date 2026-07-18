<?php
session_start();

include("connect.php");

if (!(isset($_SESSION["state_login"]) && $_SESSION["type"] <= 2)) {
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
  </style>
</head>

<body>
  <header class="main-header">
    <div class="header-right">
      <button id="menuToggle" class="menu-toggle-btn">
        <i class="fa-solid fa-bars"></i>
      </button>
      <div class="header-logo">
        <span class="brand-color">پنل مدیریت</span> | هنرستان راه دانش
      </div>
    </div>
    <div class="header-left">
      <button id="themeToggle" class="theme-toggle-btn">
        <i class="fa-solid fa-moon"></i>
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
            <a href="#" class="active">
              <img src="images/icons/userswhite.png" width="20px" height="20px" />لیست هنرجویان
            </a>
          </li>
          <li>
            <a href="#">
              <img src="images/icons/teachers.png" width="20px" height="20px" />
              لیست معلمین</a>
          </li>
          <li>
            <a href="#">
              <img src="images/icons/role.png" width="25px" height="25px" />
              تعریف نقش</a>
          </li>
          <li>
            <a href="#">
              <img src="images/icons/manageroles.png" width="20px" height="20px" />مدیریت نقش ها</a>
          </li>
          <li>
            <a href="#"><i class="fa-solid fa-calendar-days"></i>کارنامه ها</a>
          </li>
          <li>
            <a href="#"><i class="fa-solid fa-envelope-open-text"></i>ارسال خبر جدید</a href="#">
            <img src="images/icons/scorewhite.png" width="20px" height="20px" />
            کارنامه ها</a>
          </li>
          <li>
            <a href="#">
              <img src="images/icons/bellwhite.png" width="20px" height="20px" />ارسال خبر جدید</a>
          </li>
          <li>
            <a href="#"><i class="fa-solid fa-shield-halved"></i>ارسال پیام اس ام
              اسی</a href="#">
            <img src="images/icons/newimg.png" width="20px" height="20px" />افزودن عکس جدید</a>
          </li>
          <li>
            <a href="#">
              <img src="images/icons/sendsms.png" width="20px" height="20px" />ارسال پیام اس ام اسی</a>
          </li>
        </ul>
      </div>

      <div class="sidebar-footer">
        <a href="#" class="back-home-btn">
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
            <img src="images/icons/sendsms.png" width="30px" height="30px" />
          </div>
          <div class="stat-info">
            <h3>ارسال موفق</h3>
            <div class="stat-number">۱,۲۵۰</div>
          </div>
        </div>
        <div class="stat-card card-gradient-5">
          <div class="stat-icon">
            <img src="images/icons/visit.png" width="30px" height="30px" />
          </div>
          <div class="stat-info">
            <h3>بازدید امروز</h3>
            <div class="stat-number">۴۵۰ بار</div>
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
                <a href="#" class="action-btn edit-btn">لیست نمرات</a>
                <a href="#" class="action-btn edit-btn"> عملگر ۳</a>
                <a href="#" class="action-btn edit-btn"> عملگر ۴</a>
              </div>

              <div class="split-row">
                <a href="#" class="quick-btn-split">
                  <img src="images/icons/bellblue.png" width="18px" height="18px" />
                  <span>افزودن خبر</span>
                </a>
                <a href="#" class="quick-btn-split">
                  <img src="images/icons/uploaimg.png" width="18px" height="18px" />
                  <span>افزودن عکس به گالری</span>
                </a>
              </div>
              <div class="full-row">
                <a href="#" class="quick-btn-full">
                  <img src="images/icons/sendsmsred.png" width="18px" height="18px" />
                  <span>ارسال اطلاعیه اس‌ام‌اسی</span>
                </a>
              </div>
            </div>
          </section>

          <section class="panel-card">
            <div class="card-header">
              <h2>مدیریت اعضا</h2>
            </div>
            <div class="members-management-grid">
              <div class="management-sub-section">
                <h3 class="sub-section-title">مدیریت دانش‌آموزان</h3>
                <div class="action-buttons-group">
                  <a href="add_student.php" class="action-btn add-btn">
                    <img src="images/icons/adduser.png" width="18px" height="18px" />
                    افزودن دانش‌آموز جدید</a>
                  <a href="#" class="action-btn edit-btn">
                    <img src="images/icons/edituser.png" width="18px" height="18px" />
                    ویرایش اطلاعات دانش‌آموزان</a>
                  <a href="#" class="action-btn delete-btn">
                    <img src="images/icons/deleteuser.png" width="18px" height="18px" />
                    حذف دانش‌آموز</a>
                </div>
              </div>

              <div class="management-sub-section">
                <h3 class="sub-section-title">مدیریت معلمان</h3>
                <div class="action-buttons-group">
                  <a href="#" class="action-btn add-btn">
                    <img src="images/icons/adduser.png" width="18px" height="18px" />
                    افزودن معلم جدید</a>
                  <a href="#" class="action-btn edit-btn">
                    <img src="images/icons/edituser.png" width="18px" height="18px" />
                    ویرایش اطلاعات معلمان</a>
                  <a href="#" class="action-btn delete-btn">
                    <img src="images/icons/deleteuser.png" width="18px" height="18px" />
                    حذف معلم</a>
                </div>
              </div>
              <div class="management-sub-section">
                <h3 class="sub-section-title">مدیریت کلاس ها</h3>
                <div class="action-buttons-group">
                  <a href="add_class.php" class="action-btn add-btn">
                    افزودن کلاس جدید</a>
                  <a href="#" class="action-btn edit-btn">
                    ویرایش اطلاعات کلاس ها</a>
                  <a href="#" class="action-btn delete-btn">
                    حذف کلاس</a>
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
              <a href="#" class="monitoring-item">
                <div class="monitoring-info">
                  <img src="images/icons/play.png" width="18px" height="18px" />
                  <span>نظارت بر کلاس مجازی</span>
                </div>
                <img src="images/icons/Chevron-left.png" width="18px" height="18px" />
              </a>
              <a href="#" class="monitoring-item">
                <div class="monitoring-info">
                  <img src="images/icons/usercheck.png" width="18px" height="18px" />
                  <span>نظارت بر معلمان</span>
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
                  <path class="circle-progress" stroke-dasharray="86, 100"
                    d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                  <text x="18" y="20.35" class="percentage">۸۶٪</text>
                </svg>
              </div>
              <div class="chart-legend">
                <div class="legend-item">
                  <span class="legend-color present-color"></span> حاضر (۸۶٪)
                </div>
                <div class="legend-item">
                  <span class="legend-color absent-color"></span> غایب (۱۴٪)
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