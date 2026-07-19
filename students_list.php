<?php
session_start();
if (!(isset($_SESSION["state_login"]) && $_SESSION["type"] <= 2)) {
  header("location:login.php");
  exit();
}

include("connect.php");

if (isset($_POST['ajax_page'])) {
  $page = intval($_POST['ajax_page']);
  $students_per_page = 15;
  $start = ($page - 1) * $students_per_page;

  $sql_student = "SELECT Students.*, Classes.C_grade, Classes.C_major FROM Students 
  INNER JOIN Classes ON Students.Stu_classID = Classes.C_ID LIMIT $start,$students_per_page";

  $stmt_student = $connect->prepare($sql_student);
  $stmt_student->execute();
  $students_list = $stmt_student->fetchAll(PDO::FETCH_ASSOC);

  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($students_list);
  exit();
}

$sql = "SELECT * FROM Students";
$stmt = $connect->prepare($sql);
$stmt->execute();

$total_students = $stmt->rowCount();
$students_per_page = 15;
$total_pages = ceil($total_students / $students_per_page);

$page = 1;
$start = ($page - 1) * $students_per_page;

$sql_student = "SELECT Students.*, Classes.C_grade, Classes.C_major FROM Students 
INNER JOIN Classes ON Students.Stu_classID = Classes.C_ID LIMIT $start,$students_per_page";

$stmt_student = $connect->prepare($sql_student);
$stmt_student->execute();
?>

<!doctype html>
<html lang="fa" dir="rtl">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>مدیریت و لیست هنرجویان | پورتال هنرستان</title>

  <link rel="stylesheet" href="styles/panel_style.css" />
  <link rel="stylesheet" href="styles/students_list_style.css" />

  <link rel="icon" href="images/icons/rahdanesh.png">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/vazirmatn@33.0.3/Vazirmatn-font-face.css" />

  <script src="js/jquery-1.10.2.min.js"></script>

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

      <div id="searchResultCount" class="search-result-count">تعداد هنرجویان: <?php echo $total_students; ?> نفر</div>

      <div class="students-linear-list" id="students_container">
        <?php
        while ($students = $stmt_student->fetch(PDO::FETCH_ASSOC)) {
          ?>
          <div class="student-linear-row">
            <div class="student-info-data-grid">

              <div class="data-cell">
                <span class="cell-label">نام و نام خانوادگی:</span>
                <span class="cell-value bold-text searchable"><?php echo $students["Stu_fullName"] ?></span>
              </div>

              <div class="data-cell">
                <span class="cell-label">نام کلاس:</span>
                <span
                  class="cell-value searchable"><?php echo $students["C_grade"] . " " . $students["C_major"]; ?></span>
              </div>

              <div class="data-cell">
                <span class="cell-label">نام پدر:</span>
                <span
                  class="cell-value searchable"><?php echo $students["Stu_fatherName"] == "" ? "تعریف نشده" : $students["Stu_fatherName"]; ?></span>
              </div>

              <div class="data-cell">
                <span class="cell-label">کد ملی:</span>
                <span class="cell-value font-en searchable"><?php echo $students["Stu_nationalCode"] ?></span>
              </div>

              <div class="data-cell">
                <span class="cell-label">شماره تلفن:</span>
                <span class="cell-value font-en searchable"><?php echo $students["Stu_phone"] ?></span>
              </div>

              <div class="data-cell">
                <span class="cell-label">تلفن پدر:</span>
                <span
                  class="cell-value font-en searchable"><?php echo $students["Stu_fatherPhone"] == "" ? "تعریف نشده" : $students["Stu_fatherPhone"]; ?></span>
              </div>

            </div>

            <div class="student-action-cell">
              <a href="edit_student.php?id=<?php echo $students["Stu_ID"]; ?>" class="btn-edit-student"
                title="ویرایش اطلاعات">
                <span>ویرایش</span>
              </a>
            </div>
          </div>
        <?php } ?>
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

      <!-- کانتینر دکمه‌های صفحه‌بندی -->
      <div id="pager_asli" style="margin-top: 15px; display: flex; gap: 2px;">
        <?php
        for ($i = 1; $i <= $total_pages; $i++) {
          if ($i == $page) {
            echo "<a style='margin-right : 10px; background-color:rgba(250, 64, 64, 0.42);' href='#' class='btn-back-panel pager active'>$i</a> ";
          } else {
            echo "<a style='margin-right : 10px;' href='#' class='btn-back-panel pager'>$i</a> ";
          }
        }
        ?>
      </div>
    </section>
  </main>

  <script>
    var totalPages = <?php echo $total_pages; ?>;

    $(document).ready(function () {
      $(document).on('click', '#pager_asli .pager', function (e) {
        e.preventDefault();

        var targetPage = parseInt($(this).text());
        var clickedBtn = $(this);
        var isResponseReceived = false;

        $("#students_container").html(`
          <div class="ajax-loading-box" id="ajax_loader">
            <div class="custom-spinner"></div>
            <span style="font-family:tahoma; font-size:13px; color:#555;">در حال بارگذاری اطلاعات...</span>
          </div>
        `);

        var errorTimer = setTimeout(function () {
          if (!isResponseReceived) {
            $("#students_container").html(`
              <div class="error-ajax-box">
                <div class="error-icon">⚠️</div>
                <h3 style="font-family:tahoma; font-size:15px; margin:0;">خطا در برقراری ارتباط!</h3>
                <p style="font-family:tahoma; font-size:12px; margin-top:5px; color:#777;">پاسخی از سرور دریافت نشد. لطفاً مجدداً تلاش کنید.</p>
              </div>
            `);
          }
        }, 4000);

        $.ajax({
          url: 'students_list.php',
          type: 'POST',
          dataType: 'json',
          data: { ajax_page: targetPage }
        })
          .done(function (msg) {
            isResponseReceived = true;
            clearTimeout(errorTimer);

            $("#students_container").html('');

            $.each(msg, function (index, student) {
              var fatherName = student.Stu_fatherName ? student.Stu_fatherName : "تعریف نشده";
              var fatherPhone = student.Stu_fatherPhone ? student.Stu_fatherPhone : "تعریف نشده";

              $("#students_container").append(`
              <div class="student-linear-row">
                <div class="student-info-data-grid">
                  <div class="data-cell"><span class="cell-label">نام و نام خانوادگی:</span><span class="cell-value bold-text searchable">${student.Stu_fullName}</span></div>
                  <div class="data-cell"><span class="cell-label">نام کلاس:</span><span class="cell-value searchable">${student.C_grade} ${student.C_major}</span></div>
                  <div class="data-cell"><span class="cell-label">نام پدر:</span><span class="cell-value searchable">${fatherName}</span></div>
                  <div class="data-cell"><span class="cell-label">کد ملی:</span><span class="cell-value font-en searchable">${student.Stu_nationalCode}</span></div>
                  <div class="data-cell"><span class="cell-label">شماره تلفن:</span><span class="cell-value font-en searchable">${student.Stu_phone}</span></div>
                  <div class="data-cell"><span class="cell-label">تلفن پدر:</span><span class="cell-value font-en searchable">${fatherPhone}</span></div>
                </div>
                <div class="student-action-cell">
                  <a href="edit_student.php?id=${student.Stu_ID}" class="btn-edit-student" title="ویرایش اطلاعات"><span>ویرایش</span></a>
                </div>
              </div>
            `);
            });

            $("#pager_asli").html('');
            for (var i = 1; i <= totalPages; i++) {
              if (i == targetPage) {
                $("#pager_asli").append("<a style='margin-right : 10px; background-color:rgba(250, 64, 64, 0.42);' href='#' class='btn-back-panel pager active'>" + i + "</a> ");
              } else {
                $("#pager_asli").append("<a style='margin-right : 10px;' href='#' class='btn-back-panel pager'>" + i + "</a> ");
              }
            }
          });
      });
    });
  </script>

  <script src="https://unpkg.com/lenis@1.3.11/dist/lenis.min.js"></script>
  <script type="text/javascript" src="js/theme.js"></script>
</body>

</html>