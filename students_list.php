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
  <title>مدیریت و لیست هنرجویان | پورتال هنرستان</title>
  <link rel="stylesheet" href="styles/panel_style.css" />
  <link rel="stylesheet" href="styles/students_list_style.css" />
  <link rel="stylesheet" href="styles/add_student.css" />
  <link rel="icon" href="images/icons/rahdanesh.png">
  <link rel="stylesheet" href="js/sweetalert2.min.css">
  <link rel="stylesheet" href="styles/font.css">
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
  <main class="panel-container list-layout">
    <section class="list-section-card">
      <div class="list-card-header">
        <h2 class="list-main-title">لیست اطلاعات هنرجویان</h2>
      </div>

      <div class="search-box">
        <input type="text" id="studentSearch" placeholder="جستجو بر اساس نام، شماره تلفن یا کد ملی">
        <button id="clearSearch" type="button">✖</button>
      </div>

      <div id="searchResultCount" class="search-result-count">
        تعداد هنرجویان: <span id="all_result">0</span> نفر
      </div>

      <div class="students-linear-list" id="students_container">
      </div>

      <div id="noResultMessage" class="no-result-message" style="display: none;">
        🔍
        <h3>هنرجویی پیدا نشد</h3>
        <p>عبارت جستجو را تغییر دهید.</p>
      </div>

      <div class="list-footer-actions">
        <a href="panel.php" class="btn-back-panel">بازگشت به پنل اصلی</a>
        <a href="add_student.php" class="btn-back-panel" style="margin-right: 10px;">افزودن هنرجو</a>
      </div>

      <!-- کانتینر دکمه‌های صفحه‌بندی اصلی -->
      <div id="pager_asli" class="pagination">
        <button class="page-btn" id="prevPage">قبلی</button>
        <div id="pageNumbers"></div>
        <button class="page-btn" id="nextPage">بعدی</button>
      </div>

    </section>
  </main>

  <script type="text/javascript" src="js/theme.js"></script>
  <script src="js/sweetalert2.min.js"></script>

  <script>
    var page = 1;
    var totalPagesCount = 0;
    var searchTimer;

    function begard() {
      var keyword = $.trim($("#studentSearch").val());

      $("#students_container").html(`
      <div class="ajax-loading-box">
          <div class="custom-spinner"></div>
          <span>در حال بارگذاری اطلاعات...</span>
      </div>
    `);

      $.ajax({
        url: 'search_student.php',
        type: 'POST',
        dataType: 'json',
        data: {
          page: page,
          keyword: keyword
        }
      })
        .done(function (msg) {
          var total_results = parseInt(msg[0]) || 0;
          var students_list = msg[1] || [];
          totalPagesCount = parseInt(msg[2]) || 0;

          $("#all_result").text(total_results);
          $("#students_container").html('');

          if (students_list.length === 0) {
            $("#noResultMessage").show();
          } else {
            $("#noResultMessage").hide();

            $.each(students_list, function (index, student) {
              // کنترل کامل مقادیر null یا خالی
              var fatherName = (student.Stu_fatherName && student.Stu_fatherName !== 'null' && student.Stu_fatherName !== 'undefined') ? student.Stu_fatherName : "تعریف نشده";
              var fatherPhone = (student.Stu_fatherPhone && student.Stu_fatherPhone !== 'null' && student.Stu_fatherPhone !== 'undefined') ? student.Stu_fatherPhone : "تعریف نشده";

              var className = "تعریف نشده";
              if (student.C_grade && student.C_major && student.C_grade !== 'null' && student.C_major !== 'null') {
                className = student.C_grade + " " + student.C_major;
              } else if (student.C_grade && student.C_grade !== 'null') {
                className = student.C_grade;
              } else if (student.C_major && student.C_major !== 'null') {
                className = student.C_major;
              }

              $("#students_container").append(`
            <div class="student-linear-row">  
              <div class="student-info-data-grid">  
                <div class="data-cell">  
                  <span class="cell-label">نام و نام خانوادگی:</span>  
                  <span class="cell-value bold-text searchable">${student.Stu_fullName || '---'}</span>  
                </div>  

                <div class="data-cell">  
                  <span class="cell-label">نام کلاس:</span>  
                  <span class="cell-value searchable">${className}</span>  
                </div>  

                <div class="data-cell">  
                  <span class="cell-label">نام پدر:</span>  
                  <span class="cell-value searchable">${fatherName}</span>  
                </div>  

                <div class="data-cell">  
                  <span class="cell-label">کد ملی:</span>  
                  <span class="cell-value font-en searchable">${student.Stu_nationalCode || '---'}</span>  
                </div>  

                <div class="data-cell">  
                  <span class="cell-label">شماره تلفن:</span>  
                  <span class="cell-value font-en searchable">${student.Stu_phone || '---'}</span>  
                </div>  

                <div class="data-cell">  
                  <span class="cell-label">تلفن پدر:</span>  
                  <span class="cell-value font-en searchable">${fatherPhone}</span>  
                </div>  
              </div>  

              <div class="student-action-cell">  
                <a href="edit_student.php?id=${student.Stu_ID}" class="btn-edit-student" title="ویرایش اطلاعات">  
                  <span>ویرایش</span>  
                </a>  
                <a href="delete_student.php?id=${student.Stu_ID}" class="btn-delete-student" data-name="${student.Stu_fullName}">  
                  حذف  
                </a>  
              </div>  
            </div>
          `);
            });
          }

          // رندر دکمه‌ها
          renderPagination();
        })
        .fail(function () {
          $("#students_container").html('<p style="color:red; text-align:center;">خطا در دریافت اطلاعات.</p>');
        });
    }

    function addPageBtn(p) {
      var activeClass = (p === page) ? 'active' : '';
      $("#pageNumbers").append('<button class="page-number ' + activeClass + '" data-page="' + p + '">' + p + '</button>');
    }

    function renderPagination() {
      $("#pageNumbers").html('');

      // اگر یک صفحه یا کمتر داشتیم دکمه‌های بعدی/قبلی غیرفعال می‌شوند
      $("#prevPage").prop("disabled", page <= 1);
      $("#nextPage").prop("disabled", page >= totalPagesCount || totalPagesCount === 0);

      if (totalPagesCount <= 0) return;

      // اگر کل صفحات کم یا مساوی ۹ باشد همه شماره‌ها را بساز
      if (totalPagesCount <= 9) {
        for (var i = 1; i <= totalPagesCount; i++) {
          addPageBtn(i);
        }
        return;
      }

      // الگوریتم برای بیشتر از ۹ صفحه
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

      // سرچ زنده
      $("#studentSearch").on("input", function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function () {
          page = 1;
          begard();
        }, 300);
      });

      // پاک کردن کادر سرچ
      $("#clearSearch").click(function () {
        $("#studentSearch").val('');
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
      var url = $(this).attr("href");
      var name = $(this).data("name");

      Swal.fire({
        title: "حذف هنرجو",
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
    });
  </script>

</body>

</html>