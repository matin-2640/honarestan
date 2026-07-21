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
    <title>مدیریت و لیست کتاب‌ها | پورتال هنرستان</title>

    <link rel="stylesheet" href="styles/panel_style.css" />
    <link rel="stylesheet" href="styles/students_list_style.css" />
    <link rel="stylesheet" href="styles/add_student.css" />
    <link rel="stylesheet" href="js/sweetalert2.min.css">

    <link rel="icon" href="images/icons/rahdanesh.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/vazirmatn@33.0.3/Vazirmatn-font-face.css" />
    <script src="js/jquery-1.10.2.min.js"></script>
</head>

<body>

    <main class="panel-container list-layout">
        <section class="list-section-card">
            <div class="list-card-header">
                <h2 class="list-main-title">لیست اطلاعات کتاب‌ها</h2>
            </div>

            <!-- باکس جستجو -->
            <div class="search-box">
                <input type="text" id="courseSearch" placeholder="جستجو بر اساس نام درس، نام معلم یا رشته تحصیلی...">
                <button id="clearSearch" type="button">✖</button>
            </div>

            <!-- تعداد نتایج -->
            <div id="searchResultCount" class="search-result-count">
                تعداد دروس: <span id="all_result">0</span> مورد
            </div>

            <!-- کانتینر لیست دروس -->
            <div class="students-linear-list" id="courses_container">
            </div>

            <!-- پیام عدم وجود نتیجه -->
            <div id="noResultMessage" class="no-result-message" style="display: none;">
                🔍
                <h3>درسی پیدا نشد</h3>
                <p>عبارت جستجو را تغییر دهید.</p>
            </div>

            <div class="list-footer-actions">
                <a href="panel.php" class="btn-back-panel">بازگشت به پنل اصلی</a>
                <a href="add_course.php" class="btn-back-panel" style="margin-right: 10px;">افزودن درس جدید</a>
            </div>

            <!-- دکمه‌های صفحه‌بندی -->
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