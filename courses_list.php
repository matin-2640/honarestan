<?php
session_start();
if (!(isset($_SESSION["state_login"]) && $_SESSION["type"] <= 2)) {
    header("location:login.php");
    exit();
}

include("connect.php");

$sql_courses = " select * from courses";
$stmt_courses = $connect->prepare($sql_courses);
$stmt_courses->execute();
?>

<!doctype html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>مدیریت و لیست کتاب ها | پورتال هنرستان</title>

    <link rel="stylesheet" href="styles/panel_style.css" />
    <link rel="stylesheet" href="styles/students_list_style.css" />

    <link rel="icon" href="images/icons/rahdanesh.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/vazirmatn@33.0.3/Vazirmatn-font-face.css" />
</head>

<body>

    <main class="panel-container list-layout">
        <section class="list-section-card">
            <div class="list-card-header">
                <h2 class="list-main-title">لیست اطلاعات کتاب ها</h2>
            </div>

            <div class="students-linear-list">
                <?php
                while ($courses = $stmt_courses->fetch(PDO::FETCH_ASSOC)) {
                    ?>
                    <div class="student-linear-row">
                        <div class="student-info-data-grid">


                            <div class="data-cell">
                                <span class="cell-label">نام درس</span>
                                <span class="cell-value"><?php echo $courses["Co_name"] ?></span>
                            </div>

                            <div class="data-cell">
                                <span class="cell-label">تعداد واحد درسی</span>
                                <span class="cell-value">
                                    <?php echo $courses["Co_num"] ?>
                                </span>
                            </div>

                            <div class="data-cell">
                                <span class="cell-label">کلاس درس</span>
                                <?php
                                $classid = $courses["Co_classID"];
                                $sql_class = " select * from classes where C_ID = $classid";
                                $stmt_class = $connect->prepare($sql_class);
                                $stmt_class->execute();
                                while ($class = $stmt_class->fetch(PDO::FETCH_ASSOC)) {
                                    ?>
                                    <span class="cell-value">
                                        <?php if ($class["C_grade"] == 10)
                                            echo "دهم";
                                        else if ($class["C_grade"] == 11)
                                            echo "یازدهم";
                                        else if ($class["C_grade"] == 12)
                                            echo "دوازدهم";
                                        else
                                            echo $class["C_grade"];
                                        echo "  ";
                                        echo $class["C_major"] ?>
                                    </span>
                                    <?php
                                }
                                ?>
                            </div>

                            <div class="data-cell">
                                <span class="cell-label">معلم درس</span>
                                <?php
                                $teacherid = $courses["Co_teacherID"];
                                $sql_teacher = " select * from teachers where T_ID = $teacherid";
                                $stmt_teacher = $connect->prepare($sql_teacher);
                                $stmt_teacher->execute();
                                while ($teacher = $stmt_teacher->fetch(PDO::FETCH_ASSOC)) {
                                    ?>
                                    <span class="cell-value">
                                        <?php echo $teacher["T_fullName"] ?>
                                    </span>
                                    <?php
                                }
                                ?>
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

            <div class="list-footer-actions">
                <a href="panel.php" class="btn-back-panel">
                    بازگشت به پنل اصلی
                </a>
                <a href="add_course.php" class="btn-back-panel" style="margin-right: 10px;">افزودن درس جدید</a>
            </div>
        </section>
    </main>

    <script src="https://unpkg.com/lenis@1.3.11/dist/lenis.min.js"></script>
    <script type="text/javascript" src="js/theme.js"></script>
</body>

</html>