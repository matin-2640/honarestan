<?php
session_start();
if (!(isset($_SESSION["state_login"]) && $_SESSION["type"] <= 2)) {
    header("location:login.php");
    exit();
}

include("connect.php");

$sql_class = " select * from classes";
$stmt_class = $connect->prepare($sql_class);
$stmt_class->execute();
?>
<!doctype html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>تغییر رمز عبور| پورتال هنرستان</title>

    <link rel="stylesheet" href="styles/panel_style.css" />
    <link rel="stylesheet" href="styles/profile_style.css" />
    <link rel="stylesheet" href="styles/add_student.css" />
    <link rel="stylesheet" href="styles/students_list_style.css" />
    <link rel="stylesheet" href="styles/font.css">
    <link rel="icon" href="images/icons/rahdanesh.png">

    <style>
        .error_box {
            width: 100%;
            background-color: rgba(235, 37, 37, 0.08);
            border: 1px solid rgba(235, 37, 37, 0.2);
            padding: 16px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-top: 15px;
            animation: floatTooltip 4s ease-in-out infinite;
        }

        [data-theme="dark"] .error_box {
            background-color: rgba(246, 59, 59, 0.1);
            border-color: rgba(246, 59, 59, 0.2);
        }
    </style>
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
                    <small>تغییر رمز عبور</small>
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
                    تغییر رمز عبور
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
                <h2 class="profile-student-name">فرم تغییر رمز عبور</h2>
                <p class="profile-student-sub">مشخصات زیر را با دقت وارد نموده و سپس دکمه ثبت نهایی را بزنید.</p>
            </div>

            <form action="admin_pass_back.php" method="POST" class="register-form">

                <style>
                    @media (min-width: 769px) {
                        .profile-info-grid {
                            margin-right: 30%;
                        }
                    }
                </style>
                <div class="profile-info-grid">

                    <div class="info-item">
                        <label for="Ad_password">رمز عبور فعلی<span class="required-star">*</span></label>
                        <input type="text" id="Ad_password" name="Ad_password" class="info-value-box input-field"
                            required />
                        <label for="Ad_newPassword"> رمز عبور جدید <span class="required-star">*</span></label>
                        <input type="password" id="Ad_newPassword" name="Ad_newPassword"
                            class="info-value-box input-field" required />
                    </div>
                    <br>
                    <div class="profile-actions-footer register-actions">
                        <button type="submit" class="btn-back-home btn-submit-register">
                            <svg viewBox="0 0 24 24" class="btn-svg-icon">
                                <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z" />
                            </svg>
                            تغییر رمز عبور
                        </button>
                    </div>

            </form>
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
                    <span>رمز عبور اشتباه است</span>
                </div>
                <?php
            }
            unset($_SESSION['error_dup']);
            ?>
            <?php
            if (isset($_SESSION['change'])) {
                ?>
                <div class="add_success">
                    <span>رمز عبور با موفقیت تغییر یافت</span>
                </div>
                <?php
            }
            unset($_SESSION['change']);
            ?>
        </section>

    </main>
    <script src="https://unpkg.com/lenis@1.3.11/dist/lenis.min.js"></script>
    <script type="text/javascript" src="js/theme.js"></script>
</body>

</html>