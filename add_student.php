<?php
session_start();
if (!(isset($_SESSION["state_login"]) && $_SESSION["type"] <= 2)) {
  header("location:login.php");
  exit();
}

include("connect.php");

?>
<!doctype html>
<html lang="fa" dir="rtl">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>ثبت‌نام هنرجوی جدید | پورتال هنرستان</title>

  <link rel="stylesheet" href="styles/panel_style.css" />
  <link rel="stylesheet" href="styles/profile_style.css" />
  <link rel="stylesheet" href="styles/add_student.css" />

  <link rel="icon" href="images/icons/rahdanesh.png">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/vazirmatn@33.0.3/Vazirmatn-font-face.css" />
</head>

<body>
  <header class="panel-header">
    <div class="panel-container header-wrapper">
      <div class="user-profile-brief">
        <div class="user-avatar-mini">
          <svg viewBox="0 0 24 24" class="avatar-svg-placeholder">
            <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z" />
          </svg>
        </div>
        <div class="user-info-text">
          <span>پنل مدیریت هنرستان</span>
          <small>ثبت‌نام و عضویت هنرجو</small>
        </div>
      </div>

      <nav class="panel-nav" id="panelNav">
        <a href="admin_panel.php">
          <svg viewBox="0 0 24 24" class="nav-svg-icon">
            <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z" />
          </svg>
          صفحه نخست
        </a>
        <a href="#" class="active">
          <svg viewBox="0 0 24 24" class="nav-svg-icon">
            <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z" />
          </svg>
          ثبت دانش‌آموز جدید
        </a>
        <a href="admin_panel.php" class="back-link-btn">
          <svg viewBox="0 0 24 24" class="nav-svg-icon">
            <path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z" />
          </svg>
          بازگشت
        </a>
      </nav>

      <div class="header-actions">
        <button class="theme-toggle" id="themeToggle" title="تغییر حالت شب و روز">
          <svg viewBox="0 0 24 24" class="theme-svg-icon" id="themeIcon">
            <path class="moon-path" d="M12.3 2a10 10 0 0 0-1.9 19.8 10 10 0 0 0 11.8-11.8A10 10 0 0 1 12.3 2z" />
          </svg>
        </button>
      </div>
    </div>
  </header>

  <main class="panel-container profile-layout">
    <!-- کارت فرم ثبت نام -->
    <section class="profile-card">
      <div class="profile-card-header">
        <div class="profile-avatar-large register-icon-badge">
          <svg viewBox="0 0 24 24" class="large-avatar-svg">
            <path
              d="M15 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm-9-2V7H4v3H1v2h3v3h2v-3h3v-2H6zm9 4c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
          </svg>
        </div>
        <h2 class="profile-student-name">فرم ثبت‌نام دانش‌آموز جدید</h2>
        <p class="profile-student-sub">مشخصات زیر را با دقت وارد نموده و سپس دکمه ثبت نهایی را بزنید.</p>
      </div>

      <!-- فرم ارسال اطلاعات به دیتابیس -->
      <form action="add_student_back.php" method="POST" class="register-form">

        <div class="profile-info-grid">

          <!-- نام و نام خانوادگی (اجباری) -->
          <div class="info-item">
            <label for="Stu_fullName">نام و نام خانوادگی <span class="required-star">*</span></label>
            <input type="text" id="Stu_fullName" name="Stu_fullName" class="info-value-box input-field"
              placeholder="مثال: رضا احمدی" required />
          </div>

          <!-- کد ملی (اجباری) -->
          <div class="info-item">
            <label for="Stu_nationalCode">کد ملی <span class="required-star">*</span></label>
            <input type="text" id="Stu_nationalCode" name="Stu_nationalCode" class="info-value-box input-field font-en"
              placeholder="0012345678" maxlength="10" required />
          </div>

          <!-- شماره تلفن دانش‌آموز (اجباری) -->
          <div class="info-item">
            <label for="Stu_phone">شماره تلفن همراه <span class="required-star">*</span></label>
            <input type="tel" id="Stu_phone" name="Stu_phone" class="info-value-box input-field font-en"
              placeholder="09123456789" maxlength="11" required />
          </div>

          <!-- انتخاب کلاس (اجباری) -->
          <div class="info-item">
            <label for="Stu_classID">کلاس / پایه تحصیلی <span class="required-star">*</span></label>
            <div class="select-wrapper">
              <select id="Stu_classID" name="Stu_classID" class="info-value-box input-field select-field" required>
                <option value="" disabled selected hidden>انتخاب کلاس...</option>
                <?php
                $sql = " select * from classes";
                $stmt_class = $connect->prepare($sql);
                $stmt_class->execute();
                while ($class = $stmt_class->fetch(PDO::FETCH_ASSOC)) {
                  echo '<option value="' . $class["C_ID"] . '">'
                    . $class["C_grade"] . ' ' . $class["C_major"] .
                    '</option>';
                }
                ?>
              </select>
            </div>
          </div>

          <!-- نام پدر (اختیاری) -->
          <div class="info-item">
            <label for="Stu_fatherName">نام پدر</label>
            <input type="text" id="Stu_fatherName" name="Stu_fatherName" class="info-value-box input-field"
              placeholder="مثال: علی" />
          </div>

          <!-- شماره تلفن همراه پدر (اختیاری) -->
          <div class="info-item">
            <label for="Stu_fatherPhone">شماره تلفن همراه پدر</label>
            <input type="tel" id="Stu_fatherPhone" name="Stu_fatherPhone" class="info-value-box input-field font-en"
              placeholder="09987654321" maxlength="11" />
          </div>

        </div>

        <!-- دکمه‌های اکشن فرم -->
        <div class="profile-actions-footer register-actions">
          <button type="submit" class="btn-back-home btn-submit-register">
            <svg viewBox="0 0 24 24" class="btn-svg-icon">
              <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z" />
            </svg>
            ثبت و ذخیره هنرجو
          </button>
        </div>
        <div class="profile-actions-footer register-actions">
          <a href="students_list.php" class="btn-back-home btn-listr">
            مشاهده لیست هنرجو ها
</a>
        </div>

        <?php
        if (isset($_SESSION['send_error'])) {
          ?>
          <div class="error_box">
            <span>خطا در ارسال مقادیر به سرور . لطفا دوباره امتحان کنید</span>
          </div>
          <?php
        }
        unset($_SESSION['send_error']);
        ?>

        <?php
        if (isset($_SESSION['error_dup'])) {
          ?>
          <div class="error_box">
            <span>این هنرجو از قبل تعریف شده است</span>
          </div>
          <?php
        }
        unset($_SESSION['error_dup']);
        ?>

        <?php
        if (isset($_SESSION['error_nationalcode'])) {
          ?>
          <div class="error_box">
            <span>کد ملی به درستی وارد نشده است</span>
          </div>
          <?php
        }
        unset($_SESSION['error_nationalcode']);
        ?>

        <?php
        if (isset($_SESSION['error_phone'])) {
          ?>
          <div class="error_box">
            <span> شماره تلفن هنرجو به درستی وارد نشده است</span>
          </div>
          <?php
        }
        unset($_SESSION['error_phone']);
        ?>

        <?php
        if (isset($_SESSION['add_student'])) {
          ?>
          <div class="add_success">
            <span>هنرجو با موفقیت افزوده شد</span>
          </div>
          <?php
        }
        unset($_SESSION['add_student']);
        ?>

      </form>

    </section>


  </main>

  <script src="https://unpkg.com/lenis@1.3.11/dist/lenis.min.js"></script>
  <script type="text/javascript" src="js/theme.js"></script>
</body>

</html>