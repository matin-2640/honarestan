<?php
session_start();
if (!(isset($_SESSION["state_login"]) && $_SESSION["type"] == 0)) {
  header("location:login.php");
  exit();
}
include("connect.php");

$id = $_SESSION["ID"];

$sql = " select * from students where Stu_ID = :id LIMIT 1";

$stmt = $connect->prepare($sql);

$stmt->bindParam(":id", $id, PDO::PARAM_STR);

$stmt->execute();

$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="fa" dir="rtl">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>پنل کاربری <?php echo ($user["Stu_fullName"]); ?> | پورتال هنرستان</title>

  <link rel="stylesheet" href="styles/panel_style.css" />

  <link rel="icon" href="images/icons/rahdanesh.png">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/vazirmatn@33.0.3/Vazirmatn-font-face.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link rel="stylesheet" href="styles/font.css">
</head>

<body>
  <header class="panel-header">
    <div class="panel-container header-wrapper">
      <div class="user-profile-brief">
        <div class="user-avatar-mini">
          <img src="images/icons/user.png" width="30px" height="30px" />
        </div>
        <div class="user-info-text">
          <span>پنل کاربری <?php echo ($user["Stu_fullName"]); ?></span>
          <small>خوش آمدید، هنرجو عزیز</small>
        </div>
      </div>

      <nav class="panel-nav" id="panelNav">
        <a href="#" class="active"><img src="images/icons/first.png" width="18px" height="18px" />
          صفحه نخست</a>
        <a href="profile.php"><img src="images/icons/profile.png" width="18px" height="18px" />
          مشاهده پروفایل</a>
        <a href="#"><img src="images/icons/paygray.png" width="18px" height="18px" />پردخت شهریه</a>
        <a href="#"><img src="images/icons/check.png" width="18px" height="18px" />
          />>تاریخچه پرداخت ها</a>
        <a href="index.php"><img src="images/icons/back.png" width="18px" height="18px" />
          بازگشت به صفحه اصلی</a>
        <a href="logout.php" class="logout-link"><img src="images/icons/leave.png" width="18px" height="18px" />
          خروج از حساب</a>
      </nav>

      <div class="header-actions">
        <button class="theme-toggle" id="themeToggle" title="تغییر حالت شب و روز">
          <i class="fa-solid fa-moon"></i>
        </button>
        <button class="menu-toggle" id="menuToggle" aria-label="باز کردن منو">
          <i class="fa-solid fa-bars"></i>
        </button>
      </div>
    </div>
  </header>

  <main class="panel-container panel-grid-layout">
    <div class="panel-main-content">
      <section class="quick-actions-grid">
        <div class="action-card">
          <div class="action-icon">
            <img src="images/icons/play.png" width="30px" height="30px" />
          </div>
          <span>کلاس مجازی</span>
        </div>
        <div class="action-card">
          <div class="action-icon">
            <img src="images/icons/score.png" width="30px" height="30px" />
          </div>
          <span>دریافت کارنامه</span>
        </div>
        <div class="action-card">
          <div class="action-icon">
            <img src="images/icons/pay.png" width="30px" height="30px" />
          </div>
          <span>پرداخت شهریه</span>
        </div>
        <div class="action-card">
          <div class="action-icon">
            <img src="images/icons/write.png" width="30px" height="30px" />
          </div>
          <span>ارسال تکالیف</span>
        </div>
      </section>

      <section class="chart-section-wrapper">
        <div class="chart-card">
          <h3>میزان حضور این ماه شما در کلاس</h3>
          <div class="chart-container">
            <div class="circular-chart-box">
              <svg viewBox="0 0 36 36" class="circular-chart">
                <path class="circle-bg" d="M18 2.0845
                                        a 15.9155 15.9155 0 0 1 0 31.831
                                        a 15.9155 15.9155 0 0 1 0 -31.831" />
                <path class="circle-progress" stroke-dasharray="75, 100" d="M18 2.0845
                                        a 15.9155 15.9155 0 0 1 0 31.831
                                        a 15.9155 15.9155 0 0 1 0 -31.831" />
              </svg>
              <div class="percentage-text">
                <span class="number">۷۵٪</span>
                <span class="label">حضور موفق</span>
              </div>
            </div>

            <div class="chart-legend">
              <div class="legend-item">
                <span class="dot present"></span>
                <span>حاضر (۷۵٪)</span>
              </div>
              <div class="legend-item">
                <span class="dot absent"></span>
                <span>غایب (۲۵٪)</span>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section class="info-list-wrapper">
        <div class="info-strip-card">
          <div class="info-strip-icon">
            <img src="images/icons/note.png" width="25px" height="25px" />
          </div>
          <div class="info-strip-text">
            <h4>عنوان جزوه اول</h4>
            <p>شرح جزوه اول</p>
          </div>
        </div>
        <div class="info-strip-card">
          <div class="info-strip-icon">
            <img src="images/icons/note.png" width="25px" height="25px" />
          </div>
          <div class="info-strip-text">
            <h4>عنوان جزوه دوم</h4>
            <p>شرح جزوه دوم</p>
          </div>
        </div>
        <div class="info-strip-card">
          <div class="info-strip-icon">
            <img src="images/icons/note.png" width="25px" height="25px" />
          </div>
          <div class="info-strip-text">
            <h4>عنوان جزوه سوم</h4>
            <p>شرح جزوه سوم</p>
          </div>
        </div>
      </section>
    </div>

    <aside class="panel-sidebar">
      <div class="sidebar-card">
        <div class="sidebar-header">
          <img src="images/icons/notif.png" width="20px" height="20px" />
          <h3>جدیدترین اخبار</h3>
        </div>
        <ul class="sidebar-news-list">
          <li>
            <a href="hNews.html" class="news-item-link">
              <span class="news-bullet"></span>
              <span class="news-title-text">خبر ۱: زمان‌بندی دقیق امتحانات نهایی اعلام شد</span>
            </a>
          </li>
          <li>
            <a href="hNews.html" class="news-item-link">
              <span class="news-bullet"></span>
              <span class="news-title-text">خبر ۲: کارگاه برنامه‌نویسی پایتون ویژه تابستان</span>
            </a>
          </li>
          <li>
            <a href="hNews.html" class="news-item-link">
              <span class="news-bullet"></span>
              <span class="news-title-text">خبر ۳: نتایج مسابقات عکاسی استانی مشخص شد</span>
            </a>
          </li>
          <li>
            <a href="hNews.html" class="news-item-link">
              <span class="news-bullet"></span>
              <span class="news-title-text">خبر ۴: راه‌اندازی کارگاه حسابداری تحت وب هنرستان</span>
            </a>
          </li>
          <li>
            <a href="hNews.html" class="news-item-link">
              <span class="news-bullet"></span>
              <span class="news-title-text">خبر ۵: اردو علمی بازدید از پارک علم و فناوری</span>
            </a>
          </li>
        </ul>
      </div>
    </aside>
  </main>

  <script src="https://unpkg.com/lenis@1.3.11/dist/lenis.min.js"></script>
  <script type="text/javascript" src="js/theme.js"></script>
</body>

</html>