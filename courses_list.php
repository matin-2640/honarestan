<?php
session_start();
if (!(isset($_SESSION["state_login"]) && $_SESSION["type"] == 2 || $_SESSION["type"] == 3) || $_SESSION["type"] == 4) {
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
    <title>مدیریت و لیست کتاب‌ها | پورتال هنرستان</title>

    <link rel="stylesheet" href="styles/panel_style.css" />
    <link rel="stylesheet" href="styles/students_list_style.css" />
    <link rel="stylesheet" href="styles/add_student.css" />
    <link rel="stylesheet" href="js/sweetalert2.min.css">
    <link rel="stylesheet" href="styles/font.css">

    <link rel="icon" href="images/icons/rahdanesh.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/vazirmatn@33.0.3/Vazirmatn-font-face.css" />
    <script src="js/jquery-1.10.2.min.js"></script>

    <style>
        .page-number {
            margin-top: 15px;
            margin-right: 7px;
        }

        .page-btn {
            margin-top: 15px;
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
                        <a href="courses_list.php" class="ram-active"><img src="images/icons/manageroles.png" width="20"
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
                        <a href="admin_pass.php"><img src="images/icons/edituser.png" width="20"
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

    <main class="panel-container list-layout">
        <section class="list-section-card">
            <div class="list-card-header">
                <h2 class="list-main-title">لیست اطلاعات کتاب‌ها</h2>
            </div>

            <div class="search-box">
                <input type="text" id="courseSearch" placeholder="جستجو بر اساس نام درس، نام معلم یا رشته تحصیلی...">
                <button id="clearSearch" type="button">✖</button>
            </div>

            <div id="searchResultCount" class="search-result-count">
                تعداد دروس: <span id="all_result">0</span> مورد
            </div>

            <div class="students-linear-list" id="courses_container">
            </div>

            <div id="noResultMessage" class="no-result-message" style="display: none;">
                🔍
                <h3>درسی پیدا نشد</h3>
                <p>عبارت جستجو را تغییر دهید.</p>
            </div>

            <div class="list-footer-actions">
                <a href="panel.php" class="btn-back-panel">بازگشت به پنل اصلی</a>
                <a href="add_course.php" class="btn-back-panel" style="margin-right: 10px;">افزودن درس جدید</a>
            </div>

            <div id="pager_asli" class="pagination">
                <button class="page-btn" id="prevPage">قبلی</button>
                <div id="pageNumbers"></div>
                <button class="page-btn" id="nextPage">بعدی</button>
            </div>

        </section>
    </main>

    <?php if (isset($_SESSION['error'])) { ?>
        <div class="error_box">
            <span>خطا در ارسال مقادیر به سرور. لطفاً دوباره امتحان کنید</span>
        </div>
    <?php }
    unset($_SESSION['error']); ?>

    <?php if (isset($_SESSION['success'])) { ?>
        <div class="add_success">
            <span>درس با موفقیت حذف شد</span>
        </div>
    <?php }
    unset($_SESSION['success']); ?>

    <script src="https://unpkg.com/lenis@1.3.11/dist/lenis.min.js"></script>
    <script type="text/javascript" src="js/theme.js"></script>
    <script src="js/sweetalert2.min.js"></script>

    <script>
        var page = 1;
        var totalPagesCount = 0;
        var searchTimer;

        function begard() {
            var keyword = $.trim($("#courseSearch").val());

            $("#courses_container").html(`
                <div class="ajax-loading-box">
                    <div class="custom-spinner"></div>
                    <span>در حال بارگذاری اطلاعات...</span>
                </div>
            `);

            $.ajax({
                url: 'search_course.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    page: page,
                    keyword: keyword
                }
            })
                .done(function (msg) {
                    var total_results = parseInt(msg[0]) || 0;
                    var courses_list = msg[1] || [];
                    totalPagesCount = parseInt(msg[2]) || 0;

                    $("#all_result").text(total_results);
                    $("#courses_container").html('');

                    if (courses_list.length === 0) {
                        $("#noResultMessage").show();
                    } else {
                        $("#noResultMessage").hide();

                        $.each(courses_list, function (index, course) {
                            var gradeText = course.C_grade || '';
                            if (course.C_grade == 10) gradeText = "دهم";
                            else if (course.C_grade == 11) gradeText = "یازدهم";
                            else if (course.C_grade == 12) gradeText = "دوازدهم";

                            var className = "تعریف نشده";
                            if (gradeText || course.C_major) {
                                className = (gradeText + " " + (course.C_major || '')).trim();
                            }

                            var teacherName = course.T_fullName ? course.T_fullName : "تعریف نشده";
                            var courseType = (course.Co_type == 0) ? "پودمانی" : "غیر پودمانی";

                            $("#courses_container").append(`
                            <div class="student-linear-row">
                                <div class="student-info-data-grid">
                                    <div class="data-cell">
                                        <span class="cell-label">نام درس</span>
                                        <span class="cell-value bold-text searchable">${course.Co_name || '---'}</span>
                                    </div>

                                    <div class="data-cell">
                                        <span class="cell-label">تعداد واحد درسی</span>
                                        <span class="cell-value font-en searchable">${course.Co_num || '0'}</span>
                                    </div>

                                    <div class="data-cell">
                                        <span class="cell-label">کلاس درس</span>
                                        <span class="cell-value searchable">${className}</span>
                                    </div>

                                    <div class="data-cell">
                                        <span class="cell-label">معلم درس</span>
                                        <span class="cell-value searchable">${teacherName}</span>
                                    </div>

                                    <div class="data-cell">
                                        <span class="cell-label">وضعیت درس</span>
                                        <span class="cell-value searchable">${courseType}</span>
                                    </div>
                                </div>

                                <div class="student-action-cell">
                                    <a href="edit_course.php?id=${course.Co_ID}" class="btn-edit-student" title="ویرایش اطلاعات">
                                        <span>ویرایش</span>
                                    </a>
                                    <a href="delete_course.php?id=${course.Co_ID}" class="btn-delete-student" data-name="${course.Co_name}">
                                        حذف
                                    </a>
                                </div>
                            </div>
                        `);
                        });
                    }

                    renderPagination();
                })
                .fail(function () {
                    $("#courses_container").html('<p style="color:red; text-align:center;">خطا در دریافت اطلاعات.</p>');
                });
        }

        function addPageBtn(p) {
            var activeClass = (p === page) ? 'active' : '';
            $("#pageNumbers").append('<button class="page-number ' + activeClass + '" data-page="' + p + '">' + p + '</button>');
        }

        function renderPagination() {
            $("#pageNumbers").html('');

            $("#prevPage").prop("disabled", page <= 1);
            $("#nextPage").prop("disabled", page >= totalPagesCount || totalPagesCount === 0);

            if (totalPagesCount <= 0) return;

            if (totalPagesCount <= 9) {
                for (var i = 1; i <= totalPagesCount; i++) {
                    addPageBtn(i);
                }
                return;
            }

            addPageBtn(1);
            addPageBtn(2);
            addPageBtn(3);

            var startMiddle = page - 1;
            var endMiddle = page + 1;

            if (startMiddle > 4) {
                $("#pageNumbers").append('<span class="dots">...</span>');
            }

            for (var j = startMiddle; j <= endMiddle; j++) {
                if (j > 3 && j < totalPagesCount - 2) {
                    addPageBtn(j);
                }
            }

            if (endMiddle < totalPagesCount - 3) {
                $("#pageNumbers").append('<span class="dots">...</span>');
            }

            addPageBtn(totalPagesCount - 2);
            addPageBtn(totalPagesCount - 1);
            addPageBtn(totalPagesCount);
        }

        $(document).ready(function () {
            begard();

            $("#courseSearch").on("input", function () {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(function () {
                    page = 1;
                    begard();
                }, 300);
            });

            $("#clearSearch").click(function () {
                $("#courseSearch").val('');
                page = 1;
                begard();
            });

            $(document).on("click", ".page-number", function () {
                var selectedPage = parseInt($(this).data("page"));
                if (selectedPage !== page) {
                    page = selectedPage;
                    begard();
                }
            });

            $("#prevPage").click(function () {
                if (page > 1) {
                    page--;
                    begard();
                }
            });

            $("#nextPage").click(function () {
                if (page < totalPagesCount) {
                    page++;
                    begard();
                }
            });
        });

        $(document).on("click", ".btn-delete-student", function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            var url = $(this).attr("href");
            var name = $(this).data("name");

            Swal.fire({
                title: "حذف درس",
                text: "آیا از حذف «" + name + "» مطمئن هستید؟",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "بله",
                cancelButtonText: "انصراف"
            }).then(function (result) {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            });

            return false;
        });
    </script>
</body>

</html>