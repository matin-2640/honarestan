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
            foreach ($students as $stu) {
                ?>
                <div class="student-linear-row">
                    <div class="student-info-data-grid">
                        <div class="data-cell">
                            <span class="cell-label">نام هنرجو</span>
                            <span class="cell-value"><?php echo htmlspecialchars($stu['Stu_fullName']); ?></span>
                        </div>

                        <div class="data-cell ">
                            <span class="cell-label">کد ملی</span>
                            <span class="cell-value"><?php echo htmlspecialchars($stu['Stu_nationalCode']); ?></span>
                        </div>
                    </div>

                    <div class="student-action-cell">
                        <div class="info-item">
                            <label>نمره از 20<span class="required-star">*</span></label>
                            <input type="text" name="G_num[<?php echo $stu['Stu_ID']; ?>]" class="info-value-box input-field"
                                placeholder="16.5" required />
                        </div>
                    </div>
                </div>
                <?php
            }
        } else {
            echo '<p style="text-align: center; color: #d9534f; padding: 15px;">هیچ دانش‌آموزی برای این کلاس ثبت نشده است.</p>';
        }

    } catch (PDOException $e) {
        echo '<p style="text-align: center; color: red;">خطا در دریافت اطلاعات دیتابیس</p>';
    }
    exit();
}
