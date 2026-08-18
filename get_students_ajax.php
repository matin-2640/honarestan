<?php
include("connect.php");

$class_id = isset($_POST['class_id']) ? intval($_POST['class_id']) : 0;
$course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
$date = isset($_POST['date']) ? trim($_POST['date']) : '';

$stmt = $connect->prepare("SELECT Stu_ID, Stu_fullName, Stu_nationalCode FROM Students WHERE Stu_classID = :cid ORDER BY Stu_fullName ASC");
$stmt->execute([':cid' => $class_id]);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmtPrev = $connect->prepare("SELECT A_studentID, A_state FROM attendance WHERE A_courseID = :coid AND A_date = :adate");
$stmtPrev->execute([':coid' => $course_id, ':adate' => $date]);
$prevData = $stmtPrev->fetchAll(PDO::FETCH_KEY_PAIR);

$html = '';
$i = 1;

foreach ($students as $s) {
    $sID = $s['Stu_ID'];
    $fullName = $s['Stu_fullName'];
    $nationalCode = !empty($s['Stu_nationalCode']) ? $s['Stu_nationalCode'] : '---';

    $currentState = isset($prevData[$sID]) ? intval($prevData[$sID]) : 1;

    $checkedPresent = ($currentState === 1) ? 'checked' : '';
    $checkedAbsent = ($currentState === 0) ? 'checked' : '';

    $html .= "
    <div class='student-card'>
        <div class='student-info'>
            <div class='student-num'>{$i}</div>
            <div class='student-avatar'>
                <i class='fa-solid fa-user'></i>
            </div>
            <div class='student-details'>
                <h4>" . htmlspecialchars($fullName) . "</h4>
                <span>کد ملی / شماره دانش‌آموزی: " . htmlspecialchars($nationalCode) . "</span>
            </div>
        </div>
        <div class='attendance-options'>
            <label>
                <input type='radio' class='opt-btn' name='attendance[{$sID}]' value='1' {$checkedPresent}>
                <span class='opt-label btn-present'><i class='fa-solid fa-check'></i> حاضر</span>
            </label>
            <label>
                <input type='radio' class='opt-btn' name='attendance[{$sID}]' value='0' {$checkedAbsent}>
                <span class='opt-label btn-absent'><i class='fa-solid fa-xmark'></i> غایب</span>
            </label>
        </div>
    </div>";

    $i++;
}

echo json_encode([
    'html' => $html,
    'count' => count($students)
]);
?>