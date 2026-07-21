<?php
session_start();
if (!(isset($_SESSION["state_login"]) && $_SESSION["type"] <= 2)) {
    header("location:login.php");
    exit();
}

include("connect.php");

$id = $_GET["id"];

$sql = "SELECT * FROM courses WHERE Co_ID = ?";
$stmt = $connect->prepare($sql);
$stmt->execute([$id]);

$course = $stmt->fetch(PDO::FETCH_ASSOC);

?>
<!doctype html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ویرایش درس جدید | پورتال هنرستان</title>

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
                    <small>ویرایش درس</small>
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
                    ویرایش اطلاعات درس
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
                <h2 class="profile-student-name">فرم ویراش درس </h2>
                <p class="profile-student-sub">مشخصات زیر را با دقت وارد نموده و سپس دکمه ثبت نهایی را بزنید.</p>
            </div>


            <form action="edit_course_back.php" method="POST" class="register-form">

                <div class="profile-info-grid">

                    <div class="info-item">
                        <label for="Co_name">نام درس<span class="required-star">*</span></label>
                        <input type="text" id="Co_name" name="Co_name" class="info-value-box input-field"
                            placeholder="مثال : فارسی 1" required value="<?php echo $course["Co_name"] ?>" />
                    </div>

                    <div class="info-item">
                        <label for="Co_num">تعداد واحد درسی</label>
                        <input type="text" id="Co_num" name="Co_num" class="info-value-box input-field font-en"
                            placeholder="4" maxlength="10" value="<?php echo $course["Co_num"] ?>" />
                    </div>

                    <div class="info-item">
                        <label for="Co_classID">کلاس مربوط به درس<span class="required-star">*</span></label>
                        <div class="select-wrapper">
                            <select id="Co_classID" name="Co_classID" class="info-value-box input-field select-field"
                                required>
                                <option value="" disabled hidden>انتخاب کلاس...</option>
                                <?php
                                $sql = "SELECT * FROM Classes";
                                $stmt_class = $connect->prepare($sql);
                                $stmt_class->execute();

                                while ($class = $stmt_class->fetch(PDO::FETCH_ASSOC)) {

                                    $selected = ($class["C_ID"] == $course["Co_classID"]) ? "selected" : "";

                                    echo '<option value="' . $class["C_ID"] . '" ' . $selected . '>'
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
                                $sql = "SELECT * FROM teachers";
                                $stmt_teacher = $connect->prepare($sql);
                                $stmt_teacher->execute();

                                while ($teacher = $stmt_teacher->fetch(PDO::FETCH_ASSOC)) {

                                    $selected = ($teacher["T_ID"] == $course["Co_teacherID"]) ? "selected" : "";

                                    echo '<option value="' . $teacher["T_ID"] . '" ' . $selected . '>'
                                        . $teacher["T_fullName"] .
                                        '</option>';
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
                                <option value="0" <?php if ($course["Co_type"] == 0)
                                    echo "selected" ?>>پودمانی</option>
                                    <option value="1" <?php if ($course["Co_type"] == 1)
                                    echo "selected" ?>>غیر پودمانی
                                    </option>
                                </select>

                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="Co_ID" value="<?php echo $course["Co_ID"]; ?>">
                <!-- دکمه‌های اکشن فرم -->
                <div class="profile-actions-footer register-actions">
                    <button type="submit" class="btn-back-home btn-list">
                        <svg viewBox="0 0 24 24" class="btn-svg-icon">
                            <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z" />
                        </svg>
                        ویرایش درس
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
                if (isset($_SESSION['edit_course'])) {
                    ?>
                    <div class="add_success">
                        <span>کتاب با موفقیت ویرایش شد</span>
                    </div>
                    <?php
                }
                unset($_SESSION['edit_course']);
                ?>

            </form>

        </section>


    </main>

    <script src="https://unpkg.com/lenis@1.3.11/dist/lenis.min.js"></script>
    <script type="text/javascript" src="js/theme.js"></script>
</body>

</html>