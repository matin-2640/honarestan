<?php
session_start();
include("connect.php");

$action   = $_POST['action'] ?? '';
$class_id = intval($_POST['class_id'] ?? 0);

if ($class_id <= 0) {
    exit();
}

// ۱. دریافت لیست دروس یک کلاس
if ($action === 'get_courses') {
    try {
        $stmt = $connect->prepare("SELECT Co_ID, Co_Name FROM courses WHERE CO_ClassID = :class_id");
        $stmt->bindParam(':class_id', $class_id, PDO::PARAM_INT);
        $stmt->execute();
        $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($courses);
    } catch (PDOException $e) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([]);
    }
    exit();
}

// ۲. دریافت دانش‌آموزان به همراه نمرات ثبت‌شده قبلی
if ($action === 'get_students') {
    $course_id = intval($_POST['course_id'] ?? 0);
    $term      = intval($_POST['term'] ?? 0);

    try {
        // دریافت دانش‌آموزان و LEFT JOIN با جدول نمرات براساس درس و دوره انتخاب‌شده
        $sql = "SELECT s.Stu_ID, s.Stu_fullName, s.Stu_nationalCode, g.G_Num 
                FROM Students s 
                LEFT JOIN grades g ON s.Stu_ID = g.G_StudentID AND g.G_CourseID = :course_id AND g.G_Term = :term 
                WHERE s.Stu_classID = :class_id 
                ORDER BY s.Stu_fullName ASC";

        $stmt = $connect->prepare($sql);
        $stmt->bindParam(':class_id', $class_id, PDO::PARAM_INT);
        $stmt->bindParam(':course_id', $course_id, PDO::PARAM_INT);
        $stmt->bindParam(':term', $term, PDO::PARAM_INT);
        $stmt->execute();
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($students) > 0) {
            ?>
            <div class="table-responsive">
                <table class="students-table">
                    <thead>
                        <tr>
                            <th class="col-center">ردیف</th>
                            <th class="col-center">کد ملی</th>
                            <th>نام و نام خانوادگی</th>
                            <th class="col-center">نمره (از 20)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $counter = 1;
                        foreach ($students as $stu): 
                            // اگر نمره‌ای از قبل وجود داشت آن را مقداردهی می‌کنیم
                            $existingScore = ($stu['G_Num'] !== null) ? htmlspecialchars($stu['G_Num']) : '';
                        ?>
                        <tr class="student-row">
                            <td class="col-center row-number"><?php echo $counter++; ?></td>
                            <td class="col-center national-code"><?php echo htmlspecialchars($stu['Stu_nationalCode']); ?></td>
                            <td class="student-name"><?php echo htmlspecialchars($stu['Stu_fullName']); ?></td>
                            <td class="col-center score-cell">
                                <input 
                                    type="number" 
                                    step="0.25" 
                                    min="0" 
                                    max="20" 
                                    name="G_num[<?php echo $stu['Stu_ID']; ?>]" 
                                    value="<?php echo $existingScore; ?>"
                                    class="score-input input-field" 
                                    placeholder="--" 
                                />
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php
        } else {
            echo '<p class="empty-msg error-msg">هیچ دانش‌آموزی برای این کلاس ثبت نشده است.</p>';
        }

    } catch (PDOException $e) {
        echo '<p class="empty-msg error-msg">خطا در دریافت اطلاعات دیتابیس</p>';
    }
    exit();
}
