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
    <title>مدیریت و لیست کلاس ها | پورتال هنرستان</title>

    <link rel="stylesheet" href="styles/panel_style.css" />
    <link rel="stylesheet" href="styles/students_list_style.css" />
    <link rel="stylesheet" href="styles/add_student.css" />
    <link rel="stylesheet" href="js/sweetalert2.min.css">
    <link rel="icon" href="images/icons/rahdanesh.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/vazirmatn@33.0.3/Vazirmatn-font-face.css" />
</head>

<body>

    <main class="panel-container list-layout">
        <section class="list-section-card">
            <div class="list-card-header">
                <h2 class="list-main-title">لیست اطلاعات کلاس ها</h2>
            </div>

            <div class="students-linear-list">
                <?php
                while ($classes = $stmt_class->fetch(PDO::FETCH_ASSOC)) {
                    ?>
                    <div class="student-linear-row">
                        <div class="student-info-data-grid">

                            <div class="data-cell">
                                <span class="cell-label">پایه تحصیلی</span>
                                <span class="cell-value"><?php
                                if ($classes["C_grade"] == 10)
                                    echo "دهم";
                                else if ($classes["C_grade"] == 11)
                                    echo "یازدهم";
                                else if ($classes["C_grade"] == 12)
                                    echo "دوازدهم";
                                else
                                    echo $classes["C_grade"];
                                ?></span>
                            </div>

                            <div class="data-cell">
                                <span class="cell-label">رشته تحصیلی</span>
                                <span class="cell-value"><?php echo $classes["C_major"] ?></span>
                            </div>

                        </div>

                        <div class="student-action-cell">
                            <a href="edit_class.php?id=<?php echo $classes["C_ID"]; ?>" class="btn-edit-student"
                                title="ویرایش اطلاعات">
                                <span>ویرایش</span>
                            </a>
                            </a>
                            <a href="delete_class.php?id=<?php echo $classes['C_ID']; ?>" class="btn-delete-student  "
                                data-name="<?php echo $classes['C_grade'];
                                echo " ";
                                echo $classes['C_major']; ?>">
                                حذف
                            </a>
                        </div>
                    </div>
                <?php } ?>
            </div>

            <div class="list-footer-actions">
                <a href="panel.php" class="btn-back-panel">
                    بازگشت به پنل اصلی
                </a>
                <a href="add_class.php" class="btn-back-panel" style="margin-right: 10px;">افزودن کلاس جدید</a>
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
            <span>کلاس با موفقیت حذف شد</span>
        </div>
        <?php
    }
    unset($_SESSION['success']);
    ?>

    <script src="https://unpkg.com/lenis@1.3.11/dist/lenis.min.js"></script>
    <script type="text/javascript" src="js/theme.js"></script>
    <script src="js/sweetalert2.min.js"></script>
    <script src="js/jquery-1.10.2.min.js"></script>
    <script>
        $(document).on("click", ".btn-delete-student", function (e) {

            e.preventDefault();
            e.stopImmediatePropagation();

            var url = $(this).attr("href");
            var name = $(this).data("name");

            Swal.fire({
                title: "حذف کلاس",
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