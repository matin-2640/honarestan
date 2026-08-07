<?php
session_start();
include("../connect.php");

if (!(isset($_SESSION["state_login"]) && $_SESSION["type"] <= 2)) {
    exit("دسترسی غیرمجاز");
}

$class_id = intval($_POST['class_id'] ?? 0);
$course_id = intval($_POST['course_id'] ?? 0);
$date = $_POST['date'] ?? '';
$session_state = intval($_POST['type'] ?? 0); // 0 برای اول زنگ، 1 برای آخر زنگ


if ($class_id > 0 && $course_id > 0 && !empty($date)) {

    try {

        // دریافت لیست دانش‌آموزان کلاس
        $stmt_stu = $connect->prepare("
            SELECT Stu_ID, Stu_fullName, Stu_nationalCode 
            FROM students 
            WHERE Stu_classID = ? 
            ORDER BY Stu_fullName ASC
        ");

        $stmt_stu->execute([$class_id]);
        $students = $stmt_stu->fetchAll(PDO::FETCH_ASSOC);



        // دریافت غایبین ثبت شده برای همین درس، تاریخ و وضعیت زمان (A_state)
        $stmt_att = $connect->prepare("
            SELECT A_studentID 
            FROM attendance 
            WHERE A_courseID = ?
            AND A_date = ?
            AND A_state = ?
        ");

        $stmt_att->execute([
            $course_id,
            $date,
            $session_state
        ]);


        $absent_list = $stmt_att->fetchAll(PDO::FETCH_COLUMN);



        if (count($students) > 0) {
            ?>

            <div class="table-responsive">

                <table class="students-table">

                    <thead>
                        <tr>
                            <th class="col-center">ردیف</th>
                            <th class="col-center">کد ملی</th>
                            <th>نام و نام خانوادگی</th>
                            <th class="col-center">وضعیت حضور</th>
                        </tr>
                    </thead>


                    <tbody>

                    <?php

                    $counter = 1;


                    foreach ($students as $stu):

                        $student_id = $stu['Stu_ID'];


                        // اگر قبلاً غیبت ثبت شده باشد
                        $is_absent = in_array($student_id, $absent_list);

                    ?>

                        <tr class="student-row">

                            <td class="col-center">
                                <?php echo $counter++; ?>
                            </td>


                            <td class="col-center">
                                <?php echo htmlspecialchars($stu['Stu_nationalCode']); ?>
                            </td>


                            <td>
                                <?php echo htmlspecialchars($stu['Stu_fullName']); ?>
                            </td>


                            <td class="col-center">


                                <label style="margin-left:15px; cursor:pointer;">

                                    <input 
                                        type="radio"
                                        name="attendance[<?php echo $student_id; ?>]"
                                        value="1"

                                        <?php echo !$is_absent ? 'checked' : ''; ?>

                                    >

                                    حاضر

                                </label>



                                <label style="cursor:pointer; color:#d9534f;">

                                    <input 
                                        type="radio"
                                        name="attendance[<?php echo $student_id; ?>]"
                                        value="0"

                                        <?php echo $is_absent ? 'checked' : ''; ?>

                                    >

                                    غایب

                                </label>


                            </td>


                        </tr>


                    <?php endforeach; ?>


                    </tbody>


                </table>

            </div>


            <?php


        } else {

            echo '<p class="empty-msg">دانش‌آموزی در این کلاس یافت نشد.</p>';

        }



    } catch (PDOException $e) {

        echo '<p class="empty-msg">خطا در بارگذاری اطلاعات از دیتابیس</p>';

    }

}

?>
