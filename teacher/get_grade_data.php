<?php
session_start();
include("../connect.php");

$action   = $_POST['action'] ?? '';
$class_id = intval($_POST['class_id'] ?? 0);

$teacher_id = 0;

if (isset($_SESSION["ID"])) {
    $teacher_id = intval($_SESSION["ID"]);
}

if ($class_id <= 0) {
    exit();
}

if ($action === 'get_teacher_courses' || $action === 'get_courses') {
    try {
        if ($action === 'get_teacher_courses' && $teacher_id > 0) {
            $stmt = $connect->prepare("SELECT Co_ID, Co_name FROM courses WHERE Co_classID = ? AND Co_teacherID = ?");
            $stmt->execute([$class_id, $teacher_id]);
        } else {
            $stmt = $connect->prepare("SELECT Co_ID, Co_name FROM courses WHERE Co_classID = ?");
            $stmt->execute([$class_id]);
        }
        
        $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($courses);
    } catch (PDOException $e) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([]);
    }
    exit();
}

if ($action === 'get_students') {
    $course_id = intval($_POST['course_id'] ?? 0);
    $term      = intval($_POST['term'] ?? 0);

    try {
        $sql = "SELECT s.Stu_ID, s.Stu_fullName, s.Stu_nationalCode, g.G_num 
                FROM students s 
                LEFT JOIN grades g ON s.Stu_ID = g.G_studentID AND g.G_courseID = :course_id AND g.G_term = :term 
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
                            $existingScore = ($stu['G_num'] !== null) ? htmlspecialchars($stu['G_num']) : '';
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
            echo '<p class="empty-msg error-msg">هیچ دانش‌آموزی ثبت نشده است.</p>';
        }
    } catch (PDOException $e) {
        echo '<p class="empty-msg error-msg">خطا در دریافت اطلاعات</p>';
    }
    exit();
}
?>
