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
    <title>ثبت درس جدید | پورتال هنرستان</title>

    <link rel="stylesheet" href="styles/panel_style.css" />
    <link rel="stylesheet" href="styles/profile_style.css" />
    <link rel="stylesheet" href="styles/add_student.css" />
    <link rel="stylesheet" href="styles/font.css">

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
                    <small>ثبت درس جدید</small>
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
                    ثبت درس جدید
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
                <h2 class="profile-student-name">فرم ثبت درس جدید</h2>
                <p class="profile-student-sub">مشخصات زیر را با دقت وارد نموده و سپس دکمه ثبت نهایی را بزنید.</p>
            </div>


            <form action="add_course_back.php" method="POST" class="register-form">

                <div class="profile-info-grid">

                    <div class="info-item">
                        <label for="Co_name">نام درس<span class="required-star">*</span></label>
                        <input type="text" id="Co_name" name="Co_name" class="info-value-box input-field"
                            placeholder="مثال : فارسی 1" required />
                    </div>

                    <div class="info-item">
                        <label for="Co_num">تعداد واحد درسی</label>
                        <input type="text" id="Co_num" name="Co_num" class="info-value-box input-field font-en"
                            placeholder="4" maxlength="10" />
                    </div>

                    <div class="info-item">
                        <label for="Co_classID">کلاس مربوط به درس<span class="required-star">*</span></label>
                        <div class="select-wrapper">
                            <select id="Co_classID" name="Co_classID" class="info-value-box input-field select-field"
                                required>
                                <option value="" disabled selected hidden>انتخاب کلاس...</option>
                                <?php
                                $sql_class = " select * from classes";
                                $stmt_class = $connect->prepare($sql_class);
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

                    <div class="info-item">
                        <label for="Co_teacherID">مدرس درس<span class="required-star">*</span></label>
                        <div class="select-wrapper">
                            <select id="Co_teacherID" name="Co_teacherID"
                                class="info-value-box input-field select-field" required>
                                <option value="" disabled selected hidden>انتخاب هنرآموز...</option>
                                <?php
                                $sql = " select * from teachers";
                                $stmt_teacher = $connect->prepare($sql);
                                $stmt_teacher->execute();
                                while ($Teacher = $stmt_teacher->fetch(PDO::FETCH_ASSOC)) {
                                    echo '<option value="' . $Teacher["T_ID"] . '">'
                                        . $Teacher["T_fullName"] . '</option>';
                                }
                                ?>
                            </select>

                        </div>
                    </div>

                    <div class="info-item">
                        <label for="Co_type">وضعیت درس<span class="required-star">*</span></label>
                        <div class="select-wrapper">
                            <select id="Co_type" name="Co_type" class="info-value-box input-field select-field"
                                required>
                                <option value="" disabled selected hidden>انتخاب وضعیت درس...</option>
                                <option value="0">پودمانی</option>
                                <option value="1">غیر پودمانی</option>
                            </select>

                        </div>
                    </div>
                </div>

                <!-- دکمه‌های اکشن فرم -->
                <div class="profile-actions-footer register-actions">
                    <button type="submit" class="btn-back-home btn-submit-register">
                        <svg viewBox="0 0 24 24" class="btn-svg-icon">
                            <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z" />
                        </svg>
                        ثبت و ذخیره درس
                    </button>
                </div>
                <div class="profile-actions-footer register-actions">
                    <a href="courses_list.php" class="btn-back-home btn-listr">
                        مشاهده لیست دروس
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
                        <span>این کتاب از قبل تعریف شده است</span>
                    </div>
                    <?php
                }
                unset($_SESSION['error_dup']);
                ?>

                <?php
                if (isset($_SESSION['add_course'])) {
                    ?>
                    <div class="add_success">
                        <span>کتاب با موفقیت افزوده شد</span>
                    </div>
                    <?php
                }
                unset($_SESSION['add_course']);
                ?>

            </form>

        </section>


    </main>

    <script src="https://unpkg.com/lenis@1.3.11/dist/lenis.min.js"></script>
    <script type="text/javascript" src="js/theme.js"></script>
</body>

</html>