<?php
session_start();
if (!(isset($_SESSION["state_login"]) && $_SESSION["type"] <= 2)) {
    exit("دسترسی غیرمجاز");
}

include("connect.php");

// تابع کمکی برای خواندن متغیرهای آرایه بدون حساسیت به حروف کوچک و بزرگ
function getValueInsensitive(array $array, string $key, $default = '') {
    foreach ($array as $k => $v) {
        if (strcasecmp($k, $key) === 0) {
            return $v;
        }
    }
    return $default;
}

$class_id = intval($_POST['class_id'] ?? 0);
$term_id  = intval($_POST['term_id'] ?? 0);

if ($class_id <= 0 || $term_id <= 0) {
    exit('<div class="error-msg">اطلاعات ورودی نامعتبر است.</div>');
}

try {
    // ۱. دریافت اطلاعات کلاس
    $stmtClass = $connect->prepare("SELECT * FROM Classes WHERE C_ID = :class_id LIMIT 1");
    $stmtClass->execute([':class_id' => $class_id]);
    $classInfo = $stmtClass->fetch(PDO::FETCH_ASSOC) ?: [];

    if (empty($classInfo)) {
        exit('<div class="error-msg">کلاس مورد نظر یافت نشد.</div>');
    }

    // ۲. دریافت دروس مربوط به کلاس
    $stmtCourses = $connect->prepare("
        SELECT c.*, t.T_fullName, t.T_phone 
        FROM courses c 
        LEFT JOIN Teachers t ON c.CO_TeacherID = t.T_ID 
        WHERE c.CO_ClassID = :class_id
    ");
    $stmtCourses->execute([':class_id' => $class_id]);
    $courses = $stmtCourses->fetchAll(PDO::FETCH_ASSOC);

    if (empty($courses)) {
        exit('<div class="empty-msg">هیچ درسی برای این کلاس تعریف نشده است.</div>');
    }

    $allCourseIDs = [];
    foreach ($courses as $crs) {
        $cID = getValueInsensitive($crs, 'Co_ID', 0);
        if ($cID > 0) {
            $allCourseIDs[] = $cID;
        }
    }

    // ۳. بررسی ثبت نمره دبیران
    $missingTeachers = [];
    foreach ($courses as $crs) {
        $coID = getValueInsensitive($crs, 'Co_ID', 0);
        if ($coID > 0) {
            $stmtCheck = $connect->prepare("SELECT COUNT(*) FROM grades WHERE G_CourseID = :course_id AND G_Term = :term_id");
            $stmtCheck->execute([':course_id' => $coID, ':term_id' => $term_id]);
            if ($stmtCheck->fetchColumn() == 0) {
                $teacherID    = getValueInsensitive($crs, 'CO_TeacherID', 'unknown');
                $teacherName  = getValueInsensitive($crs, 'T_fullName', 'تعیین‌نشده');
                $teacherPhone = getValueInsensitive($crs, 'T_phone', 'ثبت‌نشده');
                $courseName   = getValueInsensitive($crs, 'Co_Name', 'درس بدون نام');

                if (!isset($missingTeachers[$teacherID])) {
                    $missingTeachers[$teacherID] = [
                        'name'   => $teacherName,
                        'phone'  => $teacherPhone,
                        'course' => $courseName
                    ];
                }
            }
        }
    }

    if (!empty($missingTeachers)) {
        ?>
        <div class="pending-teachers-card">
            <h3 class="pending-title">لیست معلمانی که هنوز نمره این دوره را ثبت نکرده‌اند</h3>
            <div class="table-responsive-wrapper">
                <table class="report-table missing-table">
                    <thead>
                        <tr>
                            <th>ردیف</th>
                            <th>نام و نام خانوادگی مدرس</th>
                            <th>عنوان درس</th>
                            <th>شماره تلفن</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; foreach ($missingTeachers as $teacher): ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td><?php echo htmlspecialchars($teacher['name']); ?></td>
                            <td><?php echo htmlspecialchars($teacher['course']); ?></td>
                            <td><?php echo htmlspecialchars($teacher['phone']); ?></td>
                            <td><a href="#" class="btn-sms">ارسال پیامک</a></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
        exit();
    }

    // ۴. دریافت لیست دانش‌آموزان
    $stmtStudents = $connect->prepare("SELECT * FROM Students WHERE Stu_classID = :class_id ORDER BY Stu_fullName ASC");
    $stmtStudents->execute([':class_id' => $class_id]);
    $students = $stmtStudents->fetchAll(PDO::FETCH_ASSOC);

    if (empty($students)) {
        exit('<div class="empty-msg">هیچ دانش‌آموزی در این کلاس ثبت نشده است.</div>');
    }

    // ۵. دریافت نمرات
    $gradeMap = [];
    if (!empty($allCourseIDs)) {
        $inClause = implode(',', array_map('intval', $allCourseIDs));
        $stmtGrades = $connect->prepare("SELECT * FROM grades WHERE G_CourseID IN ($inClause)");
        $stmtGrades->execute();
        $allGrades = $stmtGrades->fetchAll(PDO::FETCH_ASSOC);

        foreach ($allGrades as $g) {
            $stuID  = getValueInsensitive($g, 'G_StudentID');
            $crsID  = getValueInsensitive($g, 'G_CourseID');
            $term   = getValueInsensitive($g, 'G_Term');
            $num    = getValueInsensitive($g, 'G_Num');

            if ($stuID && $crsID && $term) {
                $gradeMap[$stuID][$crsID][$term] = $num;
            }
        }
    }

    // ۶. تولید کارنامه‌ها
    $termsText = [1=>'مهر و آبان', 2=>'آذر', 3=>'نوبت اول (دی ماه)', 4=>'اسفند', 5=>'فروردین و اردیبهشت', 6=>'نوبت دوم (خرداد)'];
    $isMonthly = in_array($term_id, [1, 2, 4, 5]);

    foreach ($students as $stu):
        $stuID = getValueInsensitive($stu, 'Stu_ID');
        $fullName = getValueInsensitive($stu, 'Stu_fullName');
        $fatherName = getValueInsensitive($stu, 'Stu_fatherName', '-');
        $nationalCode = getValueInsensitive($stu, 'Stu_nationalCode', '-');
        $cGrade = getValueInsensitive($classInfo, 'C_Grade');
        $cMajor = getValueInsensitive($classInfo, 'C_Major');
    ?>
        <div class="mymediu-card">
            <!-- سربرگ کارنامه -->
            <div class="header-table-wrapper">
                <table class="official-header-table">
                    <tr>
                        <td class="header-right-box" style="width: 20%;"></td>
                        <td class="header-center-box" style="width: 50%;">
                            <h2>جمهوری اسلامی ایران</h2>
                            <h3>وزارت آموزش و پرورش</h3>
                            <h4>کارنامه عملکرد تحصیلی دانش‌آموز</h4>
                            <p>دوره ارزیابی: <strong><?php echo $termsText[$term_id] ?? ''; ?></strong></p>
                        </td>
                        <td class="header-left-box" style="width: 30%;">
                            <div class="info-row"><span>نام و نام خانوادگی:</span> <strong><?php echo htmlspecialchars($fullName); ?></strong></div>
                            <div class="info-row"><span>نام پدر:</span> <strong><?php echo htmlspecialchars($fatherName); ?></strong></div>
                            <div class="info-row"><span>کد ملی:</span> <strong><?php echo htmlspecialchars($nationalCode); ?></strong></div>
                            <div class="info-row"><span>پایه:</span> <strong><?php echo htmlspecialchars($cGrade); ?></strong> | <span>رشته:</span> <strong><?php echo htmlspecialchars($cMajor); ?></strong></div>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- جدول اصلی نمرات (داخل نگهدارنده ریسپانسیو) -->
            <div class="table-responsive-wrapper">
                <table class="report-table official-grid">
                    <thead>
                        <tr>
                            <th rowspan="2" style="width: 30px;">ردیف</th>
                            <th rowspan="2" style="width: 60px;">کد درس</th>
                            <th rowspan="2">نام درس</th>
                            <th rowspan="2" style="width: 40px;">واحد</th>
                            
                            <?php if ($isMonthly): ?>
                                <th rowspan="2">نمره ارزشیابی</th>
                            <?php else: ?>
                                <th colspan="3">نمرات نوبت اول</th>
                                <th colspan="2">نمرات نوبت دوم</th>
                                <th rowspan="2">نمره سالانه</th>
                            <?php endif; ?>

                            <th rowspan="2" style="width: 70px;">نتیجه</th>
                            <th rowspan="2" style="width: 120px;">ملاحظات</th>
                        </tr>
                        <?php if (!$isMonthly): ?>
                        <tr>
                            <th>مستمر</th>
                            <th>پایانی</th>
                            <th>کل</th>
                            <th>مستمر</th>
                            <th>پایانی</th>
                        </tr>
                        <?php endif; ?>
                    </thead>
                    <tbody>
                        <?php 
                        $rowNum = 1;
                        foreach ($courses as $crs): 
                            $cID = getValueInsensitive($crs, 'Co_ID');
                            $cCode = getValueInsensitive($crs, 'Co_Code', $cID);
                            $cName = getValueInsensitive($crs, 'Co_Name', 'نامشخص');
                            $cUnit = getValueInsensitive($crs, 'Co_Unit', '1');
                        ?>
                        <tr>
                            <td><?php echo $rowNum++; ?></td>
                            <td><?php echo htmlspecialchars($cCode); ?></td>
                            <td class="course-name"><?php echo htmlspecialchars($cName); ?></td>
                            <td><?php echo htmlspecialchars($cUnit); ?></td>

                            <?php if ($isMonthly): ?>
                                <td><?php echo $gradeMap[$stuID][$cID][$term_id] ?? '-'; ?></td>
                            <?php else: 
                                $m1 = $gradeMap[$stuID][$cID][1] ?? ($gradeMap[$stuID][$cID][2] ?? '-');
                                $p1 = $gradeMap[$stuID][$cID][3] ?? '-';
                                $tot1 = (is_numeric($m1) && is_numeric($p1)) ? round(($m1 + $p1) / 2, 2) : '-';
                                $m2 = $gradeMap[$stuID][$cID][4] ?? ($gradeMap[$stuID][$cID][5] ?? '-');
                                $p2 = $gradeMap[$stuID][$cID][6] ?? '-';
                                $annual = (is_numeric($m1) && is_numeric($p1) && is_numeric($m2) && is_numeric($p2)) 
                                          ? round(($m1 + ($p1 * 2) + $m2 + ($p2 * 2)) / 6, 2) : '-';
                            ?>
                                <td><?php echo $m1; ?></td>
                                <td><?php echo $p1; ?></td>
                                <td><strong><?php echo $tot1; ?></strong></td>
                                <td><?php echo $m2; ?></td>
                                <td><?php echo $p2; ?></td>
                                <td><strong><?php echo $annual; ?></strong></td>
                            <?php endif; ?>

                            <td>ناتمام</td>
                            <td>-</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- بخش رتبه‌ها مخصوص کارنامه‌های ماهانه -->
            <?php if ($isMonthly): ?>
            <div class="ranks-container">
                <div class="rank-box">
                    <span class="rank-title">رتبه در کلاس:</span>
                    <span class="rank-value">1</span>
                </div>
                <div class="rank-box">
                    <span class="rank-title">رتبه در پایه/رشته:</span>
                    <span class="rank-value">1</span>
                </div>
                <div class="rank-box">
                    <span class="rank-title">رتبه در مدرسه:</span>
                    <span class="rank-value">1</span>
                </div>
            </div>
            <?php endif; ?>

            <!-- فوتر کارنامه -->
            <div class="footer-signatures">
                <div class="sig-box">
                    <span>معدل: -</span>
                </div>
                <div class="sig-box">
                    <span>مسئول ثبت نمره</span>
                </div>
                <div class="sig-box">
                    <span>مدیر هنرستان</span>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <div class="print-action-bar">
        <a href="#" class="btn-print" onclick="window.print(); return false;">چاپ کارنامه‌ها</a>
    </div>

<?php
} catch (PDOException $e) {
    echo '<div class="error-msg">خطای پایگاه داده: ' . htmlspecialchars($e->getMessage()) . '</div>';
}
