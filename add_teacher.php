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
    <title>ثبت‌نام هنرآموز جدید | پورتال هنرستان</title>

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
                    <small>ثبت‌نام و افزودن هنرآموز</small>
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
                    ثبت هنرآموز جدید
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
                        <path class="moon-path"
                            d="M12.3 2a10 10 0 0 0-1.9 19.8 10 10 0 0 0 11.8-11.8A10 10 0 0 1 12.3 2z" />
                    </svg>
                </button>
            </div>
        </div>
    </header>

    <main class="panel-container profile-layout">

        <section class="profile-card">
            <div class="profile-card-header">
                <div class="profile-avatar-large register-icon-badge">
                    <svg viewBox="0 0 24 24" class="large-avatar-svg">
                        <path
                            d="M15 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm-9-2V7H4v3H1v2h3v3h2v-3h3v-2H6zm9 4c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
                    </svg>
                </div>
                <h2 class="profile-student-name">فرم ثبت‌نام هنرآموز جدید</h2>
                <p class="profile-student-sub">مشخصات زیر را با دقت وارد نموده و سپس دکمه ثبت نهایی را بزنید.</p>
            </div>

            <form action="add_teacher_back.php" method="POST" class="register-form">

                <div class="profile-info-grid">

                    <div class="info-item">
                        <label for="T_fullName">نام و نام خانوادگی هنرآموز<span class="required-star">*</span></label>
                        <input type="text" id="T_fullName" name="T_fullName" class="info-value-box input-field"
                            placeholder="مثال: رضا احمدی" required />
                    </div>

                    <div class="info-item">
                        <label for="T_nationalCode">کد ملی هنرآموز<span class="required-star">*</span></label>
                        <input type="text" id="T_nationalCode" name="T_nationalCode"
                            class="info-value-box input-field font-en" placeholder="0012345678" maxlength="10"
                            required />
                    </div>

                    <div class="info-item">
                        <label for="T_phone">شماره تلفن هنرآموز<span class="required-star">*</span></label>
                        <input type="tel" id="T_phone" name="T_phone" class="info-value-box input-field font-en"
                            placeholder="09123456789" maxlength="11" required />
                    </div>

                </div>

                <div class="profile-actions-footer register-actions">
                    <button type="submit" class="btn-back-home btn-submit-register">
                        <svg viewBox="0 0 24 24" class="btn-svg-icon">
                            <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z" />
                        </svg>
                        ثبت و ذخیره هنرآموز
                    </button>
                </div>
                <div class="profile-actions-footer register-actions">
                    <a href="teachers_list.php" class="btn-back-home btn-listr">
                        مشاهده لیست هنرآموز ها
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
                        <span>این هنرآموز از قبل تعریف شده است</span>
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
                        <span> شماره تلفن هنرآموز به درستی وارد نشده است</span>
                    </div>
                    <?php
                }
                unset($_SESSION['error_phone']);
                ?>

                <?php
                if (isset($_SESSION['add_teacher'])) {
                    ?>
                    <div class="add_success">
                        <span>هنرآموز با موفقیت افزوده شد</span>
                    </div>
                    <?php
                }
                unset($_SESSION['add_teacher']);
                ?>

            </form>

        </section>


    </main>

    <script src="https://unpkg.com/lenis@1.3.11/dist/lenis.min.js"></script>
    <script type="text/javascript" src="js/theme.js"></script>
</body>

</html>