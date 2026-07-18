<?php
session_start();
if (!(isset($_SESSION["state_login"]) && $_SESSION["type"] <= 2)) {
  header("location:login.php");
  exit();
}

include("connect.php");

$sql_student = "SELECT Students. * , Classes.C_grade , Classes.C_major FROM  Students
        INNER JOIN Classes ON Students.Stu_classID = Classes.C_ID";

$stmt_student = $connect->prepare($sql_student);
$stmt_student->execute();
?>

<!doctype html>
<html lang="fa" dir="rtl">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>مدیریت و لیست هنرجویان | پورتال هنرستان</title>

  <!-- اتصال به استایل‌های پایه پنل و استایل اختصاصی لیست خطی -->
  <link rel="stylesheet" href="styles/panel_style.css" />
  <link rel="stylesheet" href="styles/students_list_style.css" />

  <link rel="icon" href="images/icons/rahdanesh.png">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/vazirmatn@33.0.3/Vazirmatn-font-face.css" />
</head>

<body>

  <main class="panel-container list-layout">
    <section class="list-section-card">
      <div class="list-card-header">
        <h2 class="list-main-title">لیست اطلاعات هنرجویان</h2>
      </div>
      <div class="search-box">

    <input
        type="text"
        id="studentSearch"
        placeholder="جستجو بر اساس نام، شماره تلفن یا کد ملی">

    <button id="clearSearch" type="button">
        ✖
    </button>

</div>

      <div id="searchResultCount" class="search-result-count">تعداد هنرجویان</div>

      <div class="students-linear-list">
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
                <span class="cell-value searchable" ><?php echo $students["C_grade"];
                echo " ";
                echo $students["C_major"] ?></span>
              </div>

              <div class="data-cell">
                <span class="cell-label">نام پدر:</span>
                <span class="cell-value searchable"><?php echo $students["Stu_fatherName"];
                if ($students["Stu_fatherName"] == "")
                  echo "تعریف نشده";
                ?></span>
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
                <span class="cell-value font-en searchable"><?php echo $students["Stu_fatherName"];
                if ($students["Stu_fatherPhone"] == "")
                  echo "تعریف نشده";
                ?></span>
              </div>

            </div>

            <div class="student-action-cell">
              <a href="edit_student.php?id=1" class="btn-edit-student" title="ویرایش اطلاعات">
                <span>ویرایش</span>
              </a>
            </div>
          </div>
        <?php } ?>

      </div>

      <div id="noResultMessage" class="no-result-message">
    🔍
    <h3>هنرجویی پیدا نشد</h3>
    <p>عبارت جستجو را تغییر دهید.</p>
</div>

      <div class="list-footer-actions">
        <a href="panel.php" class="btn-back-panel">
          بازگشت به پنل اصلی
        </a>
        <a href="add_student.php" class="btn-back-panel" style="margin-right: 10px;">
          افزودن هنرجو
        </a>
      </div>
    </section>
  </main>

  <script src="https://unpkg.com/lenis@1.3.11/dist/lenis.min.js"></script>
  <script type="text/javascript" src="js/theme.js"></script>
</body>

</html>