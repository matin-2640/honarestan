<?php
session_start();
include("connect.php");

$action = $_POST['action'] ?? '';
$class_id = intval($_POST['class_id'] ?? 0);

if ($class_id <= 0) {
    exit();
}

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

if ($action === 'get_students') {
    try {
        $stmt = $connect->prepare("SELECT Stu_ID, Stu_fullName, Stu_nationalCode FROM Students WHERE Stu_classID = :class_id ORDER BY Stu_fullName ASC");
        $stmt->bindParam(':class_id', $class_id, PDO::PARAM_INT);
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
