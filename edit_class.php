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
    <title>ویرایش کلاس | پورتال هنرستان</title>

    <link rel="stylesheet" href="styles/panel_style.css" />
    <link rel="stylesheet" href="styles/profile_style.css" />
    <link rel="stylesheet" href="styles/add_student.css" />
    <link rel="stylesheet" href="styles/students_list_style.css" />
    <link rel="stylesheet" href="styles/font.css">

    <link rel="icon" href="images/icons/rahdanesh.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/vazirmatn@33.0.3/Vazirmatn-font-face.css" />

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
                    <small>ویرایش کلاس</small>
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
                    ویرایش کلاس
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
                <h2 class="profile-student-name">فرم ویرایش اطلاعات کلاس</h2>
                <p class="profile-student-sub">مشخصات زیر را با دقت وارد نموده و سپس دکمه ثبت نهایی را بزنید.</p>
            </div>

            <form action="edit_class_back.php" method="POST" class="register-form">

                <div class="profile-info-grid">

                    <div class="info-item">
                        <label for="C_grade">پایه تحصیلی<span class="required-star">*</span></label>
                        <input type="text" id="C_grade" name="C_grade" class="info-value-box input-field"
                            placeholder="مثال: 10" required value="<?php echo $class['C_grade'] ?>" />
                    </div>

                    <div class="info-item">
                        <label for="C_major"> نام رشته تحصلی <span class="required-star">*</span></label>
                        <input type="text" id="C_major" name="C_major" class="info-value-box input-field"
                            placeholder=" مثال: شبکه و نرم افزار رایانه" required
                            value="<?php echo $class['C_major'] ?>" />
                    </div>
                </div>
                <input type="hidden" name="C_ID" value="<?php echo $class["C_ID"]; ?>">
                <div class="profile-actions-footer register-actions">
                    <button type="submit" class="btn-back-home btn-list">
                        <svg viewBox="0 0 24 24" class="btn-svg-icon">
                            <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z" />
                        </svg>
                        ثبت و ذخیره کلاس
                    </button>
                </div>
                <div class="profile-actions-footer register-actions">
                    <a href="classes_list.php" class="btn-back-home btn-listr">
                        مشاهده لیست کلاس ها
                    </a>
                </div>

            </form>
            <?php
            if (isset($_SESSION['grade_error'])) {
                ?>
                <div class="error_box">
                    <span>پایه تحصیلی به درستی وارد نشده</span>
                </div>
                <?php
            }
            unset($_SESSION['grade_error']);
            ?>
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
            if (isset($_SESSION['edit_class'])) {
                ?>
                <div class="add_success">
                    <span>کلاس با موفقیت ویرایش شد</span>
                </div>
                <?php
            }
            unset($_SESSION['edit_class']);
            ?>
            <?php
            if (isset($_SESSION['error_dup'])) {
                ?>
                <div class="error_box">
                    <span>این کلاس از قبل ثبت شده است</span>
                </div>
                <?php
            }
            unset($_SESSION['error_dup']);
            ?>
        </section>

    </main>
    <script src="https://unpkg.com/lenis@1.3.11/dist/lenis.min.js"></script>
    <script type="text/javascript" src="js/theme.js"></script>
</body>

</html>