<?php
include("connect.php");

$class_id = isset($_POST['class_id']) ? intval($_POST['class_id']) : 0;

if ($class_id > 0) {
    $stmt = $connect->prepare("SELECT Co_ID, Co_name FROM courses WHERE Co_classID = :cid");
    $stmt->execute([':cid' => $class_id]);
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($courses)) {
        echo '<option value="">-- انتخاب درس --</option>';
        foreach ($courses as $c) {
            echo "<option value='" . $c['Co_ID'] . "'>" . htmlspecialchars($c['Co_name']) . "</option>";
        }
    } else {
        echo '<option value="">هیچ درسی برای این کلاس ثبت نشده است</option>';
    }
} else {
    echo '<option value="">ابتدا کلاس را انتخاب کنید</option>';
}
?>