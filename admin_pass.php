<?php
session_start();
if (!(isset($_SESSION["state_login"]) && $_SESSION["type"] == 2 || $_SESSION["type"] == 3) || $_SESSION["type"] == 4) {
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
    <style>
        #rahdaneshAdminMenu,
        #rahdaneshAdminMenu * {
            box-sizing: border-box;
        }

        #rahdaneshAdminMenu {
            direction: rtl;
            font-family: Tahoma, Arial, sans-serif;
        }

        #rahdaneshAdminMenu .ram-header {
            position: fixed;
            top: 0;
            right: 0;
            left: 0;
            width: 100%;
            height: 70px;
            z-index: 2147483646;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            background: #fff;
            border-bottom: 1px solid #e5e7eb;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
        }

        #rahdaneshAdminMenu .ram-header-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        #rahdaneshAdminMenu .ram-logo {
            font-size: 17px;
            font-weight: 800;
            color: #1f2937;
            white-space: nowrap;
        }

        #rahdaneshAdminMenu .ram-logo span {
            color: #2563eb;
        }

        #rahdaneshAdminMenu .ram-toggle {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: 42px !important;
            height: 42px !important;
            min-width: 42px !important;
            padding: 0 !important;
            margin: 0 !important;
            border: 0 !important;
            border-radius: 8px !important;
            background: #e5e7eb !important;
            cursor: pointer !important;
            outline: 0 !important;
            box-shadow: none !important;
        }

        #rahdaneshAdminMenu .ram-toggle:hover {
            background: #2563eb !important;
        }

        #rahdaneshAdminMenu .ram-toggle img {
            display: block !important;
            width: 25px !important;
            height: 25px !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        #rahdaneshAdminMenu .ram-sidebar {
            position: fixed !important;
            top: 70px !important;
            right: -280px !important;
            bottom: 0 !important;
            width: 280px !important;
            height: calc(100vh - 70px) !important;
            z-index: 2147483647 !important;
            display: flex !important;
            flex-direction: column !important;
            justify-content: space-between !important;
            padding: 20px 0 !important;
            margin: 0 !important;
            background: #1e293b !important;
            color: #f8fafc !important;
            box-shadow: -5px 0 20px rgba(0, 0, 0, 0.25) !important;
            transition: right 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
            overflow: hidden !important;
        }

        #rahdaneshAdminMenu .ram-sidebar.ram-open {
            right: 0 !important;
        }

        #rahdaneshAdminMenu .ram-brand {
            display: flex !important;
            align-items: center !important;
            gap: 10px !important;
            padding: 0 20px 15px !important;
            margin: 0 !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;
            color: #fff !important;
            font-size: 16px !important;
            font-weight: 700 !important;
        }

        #rahdaneshAdminMenu .ram-brand img {
            display: block !important;
            width: 25px !important;
            height: 25px !important;
            flex: none !important;
        }

        #rahdaneshAdminMenu .ram-nav {
            flex: 1 !important;
            padding: 20px 10px !important;
            margin: 0 !important;
            overflow-y: auto !important;
            overflow-x: hidden !important;
        }

        #rahdaneshAdminMenu .ram-nav ul {
            display: block !important;
            list-style: none !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        #rahdaneshAdminMenu .ram-nav li {
            display: block !important;
            padding: 0 !important;
            margin: 0 0 8px !important;
        }

        #rahdaneshAdminMenu .ram-nav a {
            display: flex !important;
            align-items: center !important;
            gap: 12px !important;
            width: 100% !important;
            padding: 12px 15px !important;
            margin: 0 !important;
            border: 0 !important;
            border-radius: 8px !important;
            background: transparent !important;
            color: #cbd5e1 !important;
            font-size: 14px !important;
            font-weight: 500 !important;
            text-decoration: none !important;
            line-height: normal !important;
            box-shadow: none !important;
            transition:
                background 0.2s,
                color 0.2s !important;
        }

        #rahdaneshAdminMenu .ram-nav a:hover,
        #rahdaneshAdminMenu .ram-nav a.ram-active {
            background: #2563eb !important;
            color: #fff !important;
        }

        #rahdaneshAdminMenu .ram-nav a img {
            display: block !important;
            width: 20px !important;
            height: 20px !important;
            flex: none !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        #rahdaneshAdminMenu .ram-footer {
            display: flex !important;
            flex-direction: column !important;
            gap: 10px !important;
            padding: 15px !important;
            margin: 0 !important;
            border-top: 1px solid rgba(255, 255, 255, 0.1) !important;
        }

        #rahdaneshAdminMenu .ram-footer a {
            display: flex !important;
            align-items: center !important;
            gap: 10px !important;
            width: 100% !important;
            padding: 10px !important;
            margin: 0 !important;
            border-radius: 6px !important;
            font-size: 13px !important;
            text-decoration: none !important;
            line-height: normal !important;
        }

        #rahdaneshAdminMenu .ram-footer a img {
            display: block !important;
            width: 20px !important;
            height: 20px !important;
            flex: none !important;
        }

        #rahdaneshAdminMenu .ram-home {
            background: rgba(255, 255, 255, 0.05) !important;
            color: #e2e8f0 !important;
        }

        #rahdaneshAdminMenu .ram-home:hover {
            background: rgba(255, 255, 255, 0.15) !important;
        }

        #rahdaneshAdminMenu .ram-logout {
            background: rgba(220, 38, 38, 0.15) !important;
            color: #fca5a5 !important;
        }

        #rahdaneshAdminMenu .ram-logout:hover {
            background: rgba(239, 68, 68, 0.4) !important;
            color: #fff !important;
        }

        #rahdaneshAdminMenu .ram-overlay {
            position: fixed !important;
            top: 70px !important;
            right: 0 !important;
            bottom: 0 !important;
            left: 0 !important;
            width: 100% !important;
            height: calc(100vh - 70px) !important;
            z-index: 2147483645 !important;
            background: rgba(0, 0, 0, 0.5) !important;
            opacity: 0 !important;
            visibility: hidden !important;
            pointer-events: none !important;
            transition:
                opacity 0.3s,
                visibility 0.3s !important;
        }

        #rahdaneshAdminMenu .ram-overlay.ram-show {
            opacity: 1 !important;
            visibility: visible !important;
            pointer-events: auto !important;
        }

        @media (max-width: 600px) {
            #rahdaneshAdminMenu .ram-header {
                height: 60px;
                padding: 0 12px;
            }

            #rahdaneshAdminMenu .ram-logo {
                font-size: 14px;
            }

            #rahdaneshAdminMenu .ram-toggle {
                width: 40px !important;
                height: 40px !important;
                min-width: 40px !important;
            }

            #rahdaneshAdminMenu .ram-sidebar {
                top: 60px !important;
                width: 280px !important;
                height: calc(100vh - 60px) !important;
                right: -280px !important;
            }

            #rahdaneshAdminMenu .ram-overlay {
                top: 60px !important;
                height: calc(100vh - 60px) !important;
            }
        }
    </style>

    <div id="rahdaneshAdminMenu">
        <header class="ram-header">
            <div class="ram-header-right">
                <button type="button" id="ramToggle" class="ram-toggle">
                    <img src="images/icons/menu.png" width="25" height="25" />
                </button>
                <div class="ram-logo"><span>پنل مدیریت</span> | هنرستان راه دانش</div>
            </div>
        </header>

        <nav id="ramSidebar" class="ram-sidebar">
            <div class="ram-brand">
                <img src="images/icons/user.png" width="25" height="25" /><span>داشبورد مدیریت</span>
            </div>

            <div class="ram-nav">
                <ul>
                    <li>
                        <a href="panel.php"><img src="images/icons/first.png" width="20"
                                height="20" /><span>خانه</span></a>
                    </li>
                    <li>
                        <a href="teachers_list.php"><img src="images/icons/teachers.png" width="20"
                                height="20" /><span>لیست معلمین</span></a>
                    </li>
                    <li>
                        <a href="classes_list.php"><img src="images/icons/school.png" width="20"
                                height="20" /><span>لیست کلاس ها</span></a>
                    </li>
                    <li>
                        <a href="courses_list.php"><img src="images/icons/manageroles.png" width="20"
                                height="20" /><span>لیست دروس</span></a>
                    </li>
                    <li>
                        <a href="add_score.php"><img src="images/icons/scorewhite.png" width="20"
                                height="20" /><span>ثبت نمره</span></a>
                    </li>
                    <li>
                        <a href="send_sms.php"><img src="images/icons/sendsms.png" width="20" height="20" /><span>ارسال
                                پیام</span></a>
                    </li>
                    <li>
                        <a href="admin_pass.php" class="ram-active"><img src="images/icons/edituser.png" width="20"
                                height="20" /><span>تغییر رمز عبور</span></a>
                    </li>
                    <li>
                        <a href="attendance_reports.php"><img src="images/icons/visit.png" width="20"
                                height="20" /><span>لیست حضور و غیاب</span></a>
                    </li>
                    <li>
                        <a href="certificate.php"><img src="images/icons/manageroles.png" width="20"
                                height="20" /><span>بازگذاری لوح تقدیر</span></a>
                    </li>
                    <li>
                        <a href="database_reset.php"><img src="images/icons/reset.png" width="20"
                                height="20" /><span>پاکسازی اطلاعات سال گذشته</span></a>
                    </li>
                </ul>
            </div>

            <div class="ram-footer">
                <a href="index.php" class="ram-home"><img src="images/icons/back.png" width="20"
                        height="20" /><span>بازگشت به سایت</span></a>
                <a href="logout.php" class="ram-logout"><img src="images/icons/leave.png" width="20"
                        height="20" /><span>خروج از حساب</span></a>
            </div>
        </nav>

        <div id="ramOverlay" class="ram-overlay"></div>
    </div>

    <script>
        (function () {
            function initRahdaneshMenu() {
                const root = document.getElementById("rahdaneshAdminMenu");
                if (!root) return;
                const toggle = root.querySelector("#ramToggle");
                const sidebar = root.querySelector("#ramSidebar");
                const overlay = root.querySelector("#ramOverlay");
                if (!toggle || !sidebar || !overlay) return;
                function open() {
                    sidebar.classList.add("ram-open");
                    overlay.classList.add("ram-show");
                }
                function close() {
                    sidebar.classList.remove("ram-open");
                    overlay.classList.remove("ram-show");
                }
                toggle.addEventListener("click", function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    sidebar.classList.contains("ram-open") ? close() : open();
                });
                overlay.addEventListener("click", close);
                root.querySelectorAll(".ram-nav a,.ram-footer a").forEach(function (a) {
                    a.addEventListener("click", close);
                });
                document.addEventListener("keydown", function (e) {
                    if (e.key === "Escape") close();
                });
            }
            if (document.readyState === "loading") {
                document.addEventListener("DOMContentLoaded", initRahdaneshMenu);
            } else {
                initRahdaneshMenu();
            }
        })();
    </script>
    <br><br>
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
                    <br>
                    <a href="admin_panel.php">
                        <div class="profile-actions-footer register-actions">
                            <button style="background-color: #2563eb;" type="submit"
                                class="btn-back-home btn-submit-register">
                                بازگشت به پنل مدیریت
                            </button>
                        </div>
                    </a>

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