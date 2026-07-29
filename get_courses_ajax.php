<?php
include("connect.php");

$class_id = isset($_POST['class_id']) ? intval($_POST['class_id']) : 0;

if ($class_id > 0) {
    try {
        // کوئری دریافت دروس بر اساس آی‌دی کلاس
        $stmt = $connect->prepare("SELECT * FROM courses WHERE Co_classID = :cid OR CO_ClassID = :cid");
        $stmt->execute([':cid' => $class_id]);
        $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($courses)) {
            echo '<option value="">-- انتخاب درس --</option>';
            foreach ($courses as $c) {
                // استخراج آیدی و نام درس فارغ از بزرگ و کوچک بودن حروف ستون‌ها
                $coID = $c['Co_ID'] ?? $c['co_id'] ?? $c['CO_ID'] ?? '';
                $coName = $c['Co_name'] ?? $c['co_name'] ?? $c['Co_Name'] ?? $c['CO_Name'] ?? '';

                echo "<option value='" . htmlspecialchars($coID) . "'>" . htmlspecialchars($coName) . "</option>";
            }
        } else {
            echo '<option value="">هیچ درسی برای این کلاس ثبت نشده است</option>';
        }
    } catch (PDOException $e) {
        echo '<option value="">خطا در دریافت دروس</option>';
    }
} else {
    echo '<option value="">ابتدا کلاس را انتخاب کنید</option>';
}