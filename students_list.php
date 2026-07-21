<?php
session_start();
if (!(isset($_SESSION["state_login"]) && $_SESSION["type"] <= 2)) {
  header("location:login.php");
  exit();
}

include("connect.php");

if (isset($_POST['ajax_page'])) {
  $page = intval($_POST['ajax_page']);
  $students_per_page = 1;
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
$students_per_page = 1;
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
  <link rel="stylesheet" href="styles/add_student.css" />

  <link rel="icon" href="images/icons/rahdanesh.png">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/vazirmatn@33.0.3/Vazirmatn-font-face.css" />

  <link rel="stylesheet" href="js/sweetalert2.min.css">

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
              <a href="delete_student.php?id=<?php echo $students['Stu_ID']; ?>" class="btn-delete-student  "
                data-name="<?php echo $students['Stu_fullName']; ?>">
                حذف
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
      <div id="pager_asli" class="pagination">

        <button class="page-btn" id="prevPage">
          قبلی
        </button>

        <div id="pageNumbers">

          <?php
          for ($i = 1; $i <= $total_pages; $i++) {

            if ($i == $page) {

              echo "<button class='page-number active'>$i</button>";

            } else {

              echo "<button class='page-number'>$i</button>";

            }

          }
          ?>

        </div>

        <button class="page-btn" id="nextPage">
          بعدی
        </button>

      </div>
    </section>
  </main>
  <?php
  if (isset($_SESSION['error'])) {
    ?>
    <div class="error_box">
      <span>خطا در ارسال مقادیر به سرور . لطفا دوباره امتحان کنید</span>
    </div>
    <?php
  }
  unset($_SESSION['error']);
  ?>

  <?php
  if (isset($_SESSION['success'])) {
    ?>
    <div class="add_success">
      <span>هنرجو با موفقیت حذف شد</span>
    </div>
    <?php
  }
  unset($_SESSION['success']);
  ?>

  <script>

    var totalPages = <?php echo $total_pages; ?>;
    var currentPage = 1;


    function loadPage(page) {

      currentPage = page;


      $("#students_container").html(`
        <div class="ajax-loading-box">
            <div class="custom-spinner"></div>
            <span>در حال بارگذاری اطلاعات...</span>
        </div>
`);


      $.ajax({

        url: 'students_list.php',

        type: 'POST',

        dataType: 'json',

        data: {
          ajax_page: page
        }


      })


        .done(function (msg) {


          $("#students_container").html('');


          $.each(msg, function (index, student) {


            var fatherName = student.Stu_fatherName ? student.Stu_fatherName : "تعریف نشده";

            var fatherPhone = student.Stu_fatherPhone ? student.Stu_fatherPhone : "تعریف نشده";


            $("#students_container").append(`

            <div class="student-linear-row">

                <div class="student-info-data-grid">


                    <div class="data-cell">
                    <span class="cell-label">نام و نام خانوادگی:</span>
                    <span class="cell-value bold-text">${student.Stu_fullName}</span>
                    </div>


                    <div class="data-cell">
                    <span class="cell-label">نام کلاس:</span>
                    <span class="cell-value">${student.C_grade} ${student.C_major}</span>
                    </div>


                    <div class="data-cell">
                    <span class="cell-label">نام پدر:</span>
                    <span class="cell-value">${fatherName}</span>
                    </div>


                    <div class="data-cell">
                    <span class="cell-label">کد ملی:</span>
                    <span class="cell-value">${student.Stu_nationalCode}</span>
                    </div>


                    <div class="data-cell">
                    <span class="cell-label">شماره تلفن:</span>
                    <span class="cell-value">${student.Stu_phone}</span>
                    </div>


                    <div class="data-cell">
                    <span class="cell-label">تلفن پدر:</span>
                    <span class="cell-value">${fatherPhone}</span>
                    </div>


                </div>


                <div class="student-action-cell">

                <a href="edit_student.php?id=${student.Stu_ID}" class="btn-edit-student">

                ویرایش

                </a>

                </div>


            </div>

            `);


          });



          updatePager();


        });



    }



    function updatePager() {

      function addPage(page) {

        var active = "";

        if (page == currentPage) {
          active = "active";
        }

        $("#pageNumbers").append(`
        <button class="page-number ${active}">${page}</button>
    `);


        // کلیک شماره صفحات

        $(document).on('click', '.page-number', function () {


          var page = parseInt($(this).text());


          loadPage(page);


        });

        function updatePager() {

          $("#pageNumbers").html("");

          // دکمه قبلی و بعدی
          $("#prevPage").prop("disabled", currentPage == 1);
          $("#nextPage").prop("disabled", currentPage == totalPages);

          // اگر تعداد صفحات کم بود همه را نشان بده
          if (totalPages <= 7) {

            for (let i = 1; i <= totalPages; i++) {
              addPage(i);
            }

            return;
          }

          // صفحه اول
          addPage(1);

          // سه نقطه اول
          if (currentPage > 3) {
            $("#pageNumbers").append(`<span class="dots">...</span>`);
          }

          // صفحات اطراف صفحه فعلی
          let start = Math.max(2, currentPage - 1);
          let end = Math.min(totalPages - 1, currentPage + 1);

          for (let i = start; i <= end; i++) {
            addPage(i);
          }

          // سه نقطه آخر
          if (currentPage < totalPages - 2) {
            $("#pageNumbers").append(`<span class="dots">...</span>`);
          }

          // صفحه آخر
          addPage(totalPages);

        }


        // دکمه قبلی

        $("#prevPage").click(function () {


          if (currentPage > 1) {

            loadPage(currentPage - 1);

          }


        });



        // دکمه بعدی

        $("#nextPage").click(function () {


          if (currentPage < totalPages) {

            loadPage(currentPage + 1);

          }


        });


  </script>


  <script type="text/javascript" src="js/theme.js"></script>
  <script src="js/sweetalert2.min.js"></script>

  <script>
        $(document).on("click", ".btn-delete-student", function (e) {

          e.preventDefault();
          e.stopImmediatePropagation();

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

          return false;
        });
  </script>
</body>

</html>