<?php
session_start();
include("../connect.php");

if (!(isset($_SESSION["state_login"]) && $_SESSION["type"] == 1)) {
    header("location:../login.php");
    exit();
}
$class_id = intval($_POST['class_id'] ?? 0);
$course_id = intval($_POST['course_id'] ?? 0);
$date = $_POST['date'] ?? '';
$at_type = intval($_POST['type'] ?? 1);

if ($class_id > 0 && $course_id > 0 && !empty($date)) {

    try {
        $stmt_stu = $connect->prepare("
            SELECT Stu_ID, Stu_fullName, Stu_nationalCode 
            FROM students 
            WHERE Stu_classID = ? 
            ORDER BY Stu_fullName ASC
        ");
        $stmt_stu->execute([$class_id]);
        $students = $stmt_stu->fetchAll(PDO::FETCH_ASSOC);

        $stmt_att = $connect->prepare("
            SELECT AT_studentID 
            FROM teacher_attendance 
            WHERE AT_courseID = ?
            AND AT_date = ?
            AND AT_type = ?
        ");

        $stmt_att->execute([
            $course_id,
            $date,
            $at_type
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
                            $is_absent = in_array($student_id, $absent_list);
                            ?>
                            <tr class="student-row">
                                <td class="col-center"><?php echo $counter++; ?></td>
                                <td class="col-center"><?php echo htmlspecialchars($stu['Stu_nationalCode']); ?></td>
                                <td><?php echo htmlspecialchars($stu['Stu_fullName']); ?></td>
                                <style>
                                    .attendance-options {
                                        display: flex;
                                        gap: 8px;
                                        align-items: center;
                                    }

                                    .attendance-options input[type="radio"] {
                                        display: none !important;
                                    }

                                    .opt-label {
                                        padding: 8px 16px;
                                        border-radius: 10px;
                                        font-size: 13px;
                                        font-weight: bold;
                                        cursor: pointer;
                                        display: flex;
                                        align-items: center;
                                        gap: 6px;
                                        border: 1px solid #546a89;
                                        background: #acb38c;
                                        color: #354253;
                                        transition: all 0.2s ease;
                                        user-select: none;
                                    }

                                    .opt-label:hover {
                                        border-color: #64748b;
                                    }

                                    .opt-label.btn-present:has(input[type="radio"]:checked) {
                                        background: #1b866a !important;
                                        color: #4ade80 !important;
                                        border-color: #059669 !important;
                                        box-shadow: 0 2px 8px rgba(34, 197, 94, 0.15);
                                    }

                                      .opt-label.btn-absent:has(input[type="radio"]:checked) {
                                        background: #b04242 !important;
                                        color: #fca5a5 !important;
                                        border-color: #dc2626 !important;
                                        box-shadow: 0 2px 8px rgba(239, 68, 68, 0.15);
                                    }

                                    @media (max-width: 500px) {
                                        .attendance-options {
                                            width: 100%;
                                        }

                                        .opt-label {
                                            flex: 1;
                                            justify-content: center;
                                        }
                                    }
                                </style>
                                <td class="col-center">
                                    <div class="attendance-options">
                                        <label class="opt-label btn-present">
                                            <input type="radio" class="opt-btn" name="attendance[<?php echo $student_id; ?>]" value="1"
                                                <?php echo !$is_absent ? 'checked' : ''; ?>> ✔ حاضر </label>

                                        <label class="opt-label btn-absent">
                                            <input type="radio" class="opt-btn" name="attendance[<?php echo $student_id; ?>]" value="0"
                                                <?php echo $is_absent ? 'checked' : ''; ?>> ✖ غایب </label>
                                    </div>
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