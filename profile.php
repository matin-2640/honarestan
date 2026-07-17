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
  <title>پروفایل <?php echo ($user["Stu_fullName"]); ?> | پورتال هنرستان</title>

  <!-- اتصال به استایل‌های پایه پنل و استایل جدید پروفایل -->
  <link rel="stylesheet" href="styles/panel_style.css" />
  <link rel="stylesheet" href="styles/profile_style.css" />

  <link rel="icon" href="images/icons/rahdanesh.png">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/vazirmatn@33.0.3/Vazirmatn-font-face.css" />
</head>

<body>
  <header class="panel-header">
    <div class="panel-container header-wrapper">
      <div class="user-profile-brief">
        <div class="user-avatar-mini">
          <!-- استفاده از یک SVG داخلی بسیار شیک به جای آیکون بیرونی برای آواتار -->
          <svg viewBox="0 0 24 24" class="avatar-svg-placeholder">
            <path
              d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
          </svg>
        </div>
        <div class="user-info-text">
          <span>پنل کاربری <?php echo ($user["Stu_fullName"]); ?></span>
          <small>مشاهده پروفایل کاربری</small>
        </div>
      </div>

      <nav class="panel-nav" id="panelNav">
        <a href="panel.php">
          <!-- نمونه SVG داخلی برای آیکون خانه -->
          <svg viewBox="0 0 24 24" class="nav-svg-icon">
            <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z" />
          </svg>
          صفحه نخست
        </a>
        <a href="#" class="active">
          <svg viewBox="0 0 24 24" class="nav-svg-icon">
            <path
              d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
          </svg>
          مشاهده پروفایل
        </a>
        <a href="panel.php" class="back-link-btn">
          <svg viewBox="0 0 24 24" class="nav-svg-icon">
            <path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z" />
          </svg>
          بازگشت به پنل
        </a>
      </nav>

      <div class="header-actions">
        <button class="theme-toggle" id="themeToggle" title="تغییر حالت شب و روز">
          <!-- آیکون تم با SVG بومی بدون نیاز به فونت خارجی -->
          <svg viewBox="0 0 24 24" class="theme-svg-icon" id="themeIcon">
            <path class="moon-path" d="M12.3 2a10 10 0 0 0-1.9 19.8 10 10 0 0 0 11.8-11.8A10 10 0 0 1 12.3 2z" />
          </svg>
        </button>
      </div>
    </div>
  </header>

  <main class="panel-container profile-layout">
    <!-- کارت اصلی اطلاعات کاربری -->
    <section class="profile-card">
      <div class="profile-card-header">
        <div class="profile-avatar-large">
          <svg viewBox="0 0 24 24" class="large-avatar-svg">
            <path
              d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
          </svg>
        </div>
        <h2 class="profile-student-name">پروفایل کاربری هنرجو</h2>
        <p class="profile-student-sub">اطلاعات ثبتی شما در سیستم آموزشی هنرستان</p>
      </div>

      <div class="profile-info-grid">
        <!-- فیلدها آماده برای داینامیک شدن توسط شما -->
        <div class="info-item">
          <label>نام و نام خانوادگی</label>
          <div class="info-value-box">
            <span id="student_name"><?php echo ($user["Stu_fullName"]); ?></span>
          </div>
        </div>

        <div class="info-item">
          <label>نام پدر</label>
          <div class="info-value-box">
            <span id="father_name"><?php echo ($user["Stu_fatherName"]);
            if ($user["Stu_fatherName"] == "") {
              echo "تعریف نشده";
            }
            ?></span>
          </div>
        </div>

        <div class="info-item">
          <label>شماره تلفن</label>
          <div class="info-value-box font-en">
            <span id="student_phone"><?php echo ($user["Stu_phone"]); ?></span>
          </div>
        </div>

        <div class="info-item">
          <label>شماره تلفن پدر</label>
          <div class="info-value-box font-en">
            <span id="father_phone"><?php echo ($user["Stu_fatherPhone"]);
            if ($user["Stu_fatherPhone"] == "") {
              echo "تعریف نشده";
            }
            ?></span>
          </div>
        </div>

        <div class="info-item">
          <label>کد ملی</label>
          <div class="info-value-box font-en">
            <span id="national_id"><?php echo ($user["Stu_nationalCode"]); ?></span>
          </div>
        </div>

        <div class="info-item">
          <label>کلاس / رشته تحصیلی</label>
          <div class="info-value-box">
            <span id="student_class">هنوز تعریف نشده</span>
          </div>
        </div>
      </div>

      <div class="profile-actions-footer">
        <a href="panel.php" class="btn-back-home">
          <svg viewBox="0 0 24 24" class="btn-svg-icon">
            <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z" />
          </svg>
          بازگشت به صفحه نخست پنل
        </a>
      </div>
    </section>
  </main>

  <script src="https://unpkg.com/lenis@1.3.11/dist/lenis.min.js"></script>
  <script type="text/javascript" src="js/theme.js"></script>
</body>

</html>