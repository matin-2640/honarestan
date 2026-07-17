<!-- teacher_panel.html -->

<!doctype html>
<html lang="fa" dir="rtl" data-theme="light">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
  <title>پنل مدیریت پورتال آموزشی</title>
  <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet"
    type="text/css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link rel="stylesheet" href="styles/teacher_panel.css" />
  <script>
    (function () {
      const savedTheme = localStorage.getItem("theme") || "light";
      document.documentElement.setAttribute("data-theme", savedTheme);
    })();
  </script>
</head>

<body>
  <header class="main-header">
    <button class="menu-toggle-btn" id="menuToggle" aria-label="منوی اصلی">
      <i class="fa-solid fa-bars"></i>
    </button>
    <div class="header-logo">
      <img src="images/icons/user.png" width="25px" height="25px" />
      <span>پنل مدیریتی معلم</span>
    </div>
    <button class="theme-toggle-btn" id="themeToggle" aria-label="تغییر تم">
      <i class="fa-solid fa-moon"></i>
    </button>
  </header>

  <div class="panel-container">
    <aside class="sidebar" id="sidebar">
      <div class="sidebar-brand">
        <img src="images/icons/user.png" width="20px" height="20px" />
        <span>پنل معلم سیستم</span>
      </div>
      <nav class="sidebar-nav">
        <ul>
          <li>
            <a href="#" class="active"><i class="fa-solid fa-chart-pie"></i>
              <span>کلاس مجازی</span></a>
          </li>
          <li>
            <a href="#"><i class="fa-solid fa-folder-open"></i>
              <span>مدیریت نمرات ترم</span></a>
          </li>
          <li>
            <a href="#">
              <img src="images/icons/uploadnote.png" width="20px" height="20px" />
              <span>بارگذاری جزوه</span></a>
          </li>
          <li>
            <a href="#">
              <img src="images/icons/users.png" width="20px" height="20px" />
              <span>مشاهده لیست هنرجویان</span></a>
          </li>
          <li>
            <a href="#">
              <img src="images/icons/classes.png" width="20px" height="20px" />
              <span>مشاهده لیست کلاس ها</span></a>
          </li>
        </ul>
      </nav>
      <div class="sidebar-footer">
        <a href="index.php" class="back-home-btn">
          <img src="images/icons/back.png" width="20px" height="20px" />
          <span>بازگشت به صفحه اصلی</span>
        </a>
        <a href="logout.php" class="logout-btn">
          <img src="images/icons/leave.png" width="20px" height="20px" />
          <span>خروج از حساب</span>
        </a>
      </div>
    </aside>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <main class="main-content">
      <section class="top-stats-grid">
        <div class="stat-card card-gradient-1">
          <div class="stat-icon">
            <img src="images/icons/portal.png" width="30px" height="30px" />
          </div>
          <div class="stat-info">
            <h3>تعداد هنرجویان</h3>
            <p class="stat-number">۱۲۴</p>
          </div>
        </div>
        <div class="stat-card card-gradient-2">
          <div class="stat-icon">
            <img src="images/icons/classesgreen.png" width="30px" height="30px" />
          </div>
          <div class="stat-info">
            <h3>تعداد کلاس ها</h3>
            <p class="stat-number">۱۸</p>
          </div>
        </div>
        <div class="stat-card card-gradient-3">
          <div class="stat-icon">
            <img src="images/icons/timeteach.png" width="30px" height="30px" />
          </div>
          <div class="stat-info">
            <h3>ساعت تدریس در هنرستان</h3>
            <p class="stat-number">۴۵</p>
          </div>
        </div>
      </section>

      <div class="dashboard-layout-grid">
        <div class="right-column-group">
          <div class="panel-card quick-access-box">
            <div class="card-header">
              <h2>دسترسی‌های سریع</h2>
            </div>
            <div class="quick-access-grid">
              <a href="#" class="quick-btn">
                <img src="images/icons/scoreblue.png" width="25px" height="25px" />
                <span>ثبت نمره</span>
              </a>
              <a href="#" class="quick-btn">
                <img src="images/icons/playgreen.png" width="25px" height="25px" />
                <span>ورود به کلاس مجازی</span>
              </a>
              <a href="#" class="quick-btn">
                <img src="images/icons/usersyellow.png" width="25px" height="25px" />
                <span>مشاهده لیست هنرجویان</span>
              </a>
              <a href="#" class="quick-btn">
                <img src="images/icons/uploadnotecyan.png" width="25px" height="25px" />
                <span>بارگذاری جزوه</span>
              </a>
              <a href="#" class="quick-btn">
                <img src="images/icons/bell.png" width="25px" height="25px" />
                <span>بارگذاری اعلان آموزشی</span>
              </a>
            </div>
          </div>

          <div class="panel-card chart-box">
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
                  <span class="legend-color present-color"></span>
                  <span>حاضر: ۸۶٪</span>
                </div>
                <div class="legend-item">
                  <span class="legend-color absent-color"></span>
                  <span>غایب: ۱۴٪</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="left-column-group">
          <div class="panel-card calendar-box">
            <div class="card-header">
              <div class="calendar-header-title">
                <i class="fa-solid fa-calendar-days text-primary"></i>
                <h2 id="calendarMonthYear">تیر ۱۴۰۵</h2>
              </div>
              <span class="today-tag" id="currentDayName">امروز: چهارشنبه ۲۴ تیر</span>
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
              <div class="days" id="calendarDays">
                <div class="empty"></div>
                <div>۱</div>
                <div>۲</div>
                <div>۳</div>
                <div>۴</div>
                <div>۵</div>
                <div>۶</div>
                <div>۷</div>
                <div>۸</div>
                <div>۹</div>
                <div>۱۰</div>
                <div>۱۱</div>
                <div>۱۲</div>
                <div>۱۳</div>
                <div>۱۴</div>
                <div>۱۵</div>
                <div>۱۶</div>
                <div>۱۷</div>
                <div>۱۸</div>
                <div>۱۹</div>
                <div>۲۰</div>
                <div>۲۱</div>
                <div>۲۲</div>
                <div>۲۳</div>
                <div class="today">۲۴</div>
                <div>۲۵</div>
                <div>۲۶</div>
                <div>۲۷</div>
                <div>۲۸</div>
                <div>۲۹</div>
                <div>۳۰</div>
                <div>۳۱</div>
              </div>
            </div>
          </div>

          <div class="panel-card announcements-box">
            <div class="card-header">
              <h2>اطلاعیه‌های اخیر</h2>
            </div>
            <div id="calendarDays" class="days"></div>
          </div>
          </section>
        </div>

        <footer class="panel-footer">
          <a href="index.php" class="back-home-button-main">
            <img src="images/icons/back.png" width="25px" height="25px" />
            <span>بازگشت به صفحه اصلی پورتال</span>
          </a>
        </footer>
    </main>
  </div>

  <script>
    const menuToggle = document.getElementById("menuToggle");
    const sidebar = document.getElementById("sidebar");
    const sidebarOverlay = document.getElementById("sidebarOverlay");

    function toggleMenu() {
      sidebar.classList.toggle("active");
      sidebarOverlay.classList.toggle("active");
    }

    menuToggle.addEventListener("click", toggleMenu);
    sidebarOverlay.addEventListener("click", toggleMenu);

    menuToggle.addEventListener(
      "touchstart",
      (e) => {
        e.preventDefault();
        toggleMenu();
      },
      { passive: false },
    );

    const themeToggle = document.getElementById("themeToggle");
    const htmlElement = document.documentElement;

    function updateThemeUI(theme) {
      htmlElement.setAttribute("data-theme", theme);
      const icon = themeToggle.querySelector("i");
      if (theme === "dark") {
        icon.className = "fa-solid fa-sun";
        icon.style.color = "#eab308";
      } else {
        icon.className = "fa-solid fa-moon";
        icon.style.removeProperty("color");
      }
    }

    const initialTheme = htmlElement.getAttribute("data-theme") || "light";
    updateThemeUI(initialTheme);

    themeToggle.addEventListener("click", () => {
      const currentTheme = htmlElement.getAttribute("data-theme");
      const newTheme = currentTheme === "dark" ? "light" : "dark";

      localStorage.setItem("theme", newTheme);
      updateThemeUI(newTheme);
    });
  </script>
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