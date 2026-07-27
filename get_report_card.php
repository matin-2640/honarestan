<?php
session_start();
if (!(isset($_SESSION["state_login"]) && $_SESSION["type"] <= 2)) {
    exit("دسترسی غیرمجاز");
}

include("connect.php");

// تابع کمکی برای خواندن کلیدهای آرایه بدون حساسیت به حروف کوچک و بزرگ
function getValueInsensitive(array $array, string $key, $default = '')
{
    foreach ($array as $k => $v) {
        if (strcasecmp($k, $key) === 0) {
            return $v;
        }
    }
    return $default;
}

$class_id = intval($_POST['class_id'] ?? 0);
$term_id = intval($_POST['term_id'] ?? 0);

if ($class_id <= 0 || $term_id <= 0) {
    exit('<div class="error-msg">اطلاعات ورودی نامعتبر است.</div>');
}

try {
    // ۱. اطلاعات کلاس فعلی
    $stmtClass = $connect->prepare("SELECT * FROM Classes WHERE C_ID = :class_id LIMIT 1");
    $stmtClass->execute([':class_id' => $class_id]);
    $classInfo = $stmtClass->fetch(PDO::FETCH_ASSOC) ?: [];

    if (empty($classInfo)) {
        exit('<div class="error-msg">کلاس مورد نظر یافت نشد.</div>');
    }

    $cGrade = getValueInsensitive($classInfo, 'C_Grade');
    $cMajor = getValueInsensitive($classInfo, 'C_Major');

    // ۲. دریافت کلیه دروس کلاس فعلی
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

    // تفکیک دروس عمومی/نظری (نوع ۱) و دروس پودمانی (نوع 0)
    $standardCourses = [];
    $podmaniCourses = [];
    $allCourseIDs = [];

    foreach ($courses as $crs) {
        $cID = getValueInsensitive($crs, 'Co_ID', 0);
        $coType = intval(getValueInsensitive($crs, 'Co_type', 1));

        if ($cID > 0) {
            $allCourseIDs[] = $cID;
            if ($coType === 0) {
                $podmaniCourses[] = $crs;
            } else {
                $standardCourses[] = $crs;
            }
        }
    }

    // ۳. بررسی ثبت نمرات توسط دبیران
    $missingTeachers = [];
    foreach ($courses as $crs) {
        $coID = getValueInsensitive($crs, 'Co_ID', 0);
        if ($coID > 0) {
            $stmtCheck = $connect->prepare("SELECT COUNT(*) FROM grades WHERE G_CourseID = :course_id AND G_Term = :term_id");
            $stmtCheck->execute([':course_id' => $coID, ':term_id' => $term_id]);
            if ($stmtCheck->fetchColumn() == 0) {
                $teacherID = getValueInsensitive($crs, 'CO_TeacherID', 'unknown');
                $teacherName = getValueInsensitive($crs, 'T_fullName', 'تعیین‌نشده');
                $teacherPhone = getValueInsensitive($crs, 'T_phone', 'ثبت‌نشده');
                $courseName = getValueInsensitive($crs, 'Co_Name', 'درس بدون نام');

                if (!isset($missingTeachers[$teacherID])) {
                    $missingTeachers[$teacherID] = [
                        'name' => $teacherName,
                        'phone' => $teacherPhone,
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
                        <?php $i = 1;
                        foreach ($missingTeachers as $teacher): ?>
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

    // ۴. دریافت تمام دانش‌آموزان مدرسه برای آمار رتبه‌بندی
    $stmtAllStudents = $connect->prepare("
        SELECT s.*, c.C_Grade, c.C_Major 
        FROM Students s 
        JOIN Classes c ON s.Stu_classID = c.C_ID
    ");
    $stmtAllStudents->execute();
    $allStudentsInSchool = $stmtAllStudents->fetchAll(PDO::FETCH_ASSOC);

    // دریافت نمرات تمام مدرسه برای محاسبه آمار
    $stmtAllGrades = $connect->prepare("SELECT * FROM grades");
    $stmtAllGrades->execute();
    $rawGrades = $stmtAllGrades->fetchAll(PDO::FETCH_ASSOC);

    $globalGradeMap = [];
    foreach ($rawGrades as $g) {
        $sID = getValueInsensitive($g, 'G_StudentID');
        $cID = getValueInsensitive($g, 'G_CourseID');
        $tNum = getValueInsensitive($g, 'G_Term');
        $gNum = getValueInsensitive($g, 'G_Num');
        if ($sID && $cID && $tNum) {
            $globalGradeMap[$sID][$cID][$tNum] = floatval($gNum);
        }
    }

    // دریافت دروس کل سیستم با واحدها (اصلاح‌شده: استفاده از Co_num)
    $stmtAllCourses = $connect->prepare("SELECT * FROM courses");
    $stmtAllCourses->execute();
    $rawCourses = $stmtAllCourses->fetchAll(PDO::FETCH_ASSOC);
    $courseInfoMap = [];
    foreach ($rawCourses as $co) {
        $coID = getValueInsensitive($co, 'Co_ID');
        $courseInfoMap[$coID] = [
            'unit' => floatval(getValueInsensitive($co, 'Co_num', 1)), // اصلاح ستون واحد
            'type' => intval(getValueInsensitive($co, 'Co_type', 1)),
            'class_id' => getValueInsensitive($co, 'CO_ClassID')
        ];
    }

    // ۵. محاسبه معدل دقیق برای کلیه دانش‌آموزان جهت رتبه‌بندی
    $studentAverages = []; // [stu_id => avg]
    foreach ($allStudentsInSchool as $s) {
        $sID = getValueInsensitive($s, 'Stu_ID');
        $sClassID = getValueInsensitive($s, 'Stu_classID');

        $totalWeighted = 0;
        $totalUnits = 0;

        foreach ($courseInfoMap as $cID => $cInfo) {
            if ($cInfo['class_id'] == $sClassID) {
                $unit = $cInfo['unit'];
                $type = $cInfo['type'];
                $gVal = null;

                if (in_array($term_id, [1, 2, 4, 5])) {
                    // ترم‌های ماهانه
                    if (isset($globalGradeMap[$sID][$cID][$term_id])) {
                        $gVal = $globalGradeMap[$sID][$cID][$term_id];
                    }
                } elseif ($term_id == 3) {
                    // نوبت اول
                    if ($type === 0) { // پودمانی (میانگین پودمان ۱ و ۲)
                        $p1 = $globalGradeMap[$sID][$cID][1] ?? null;
                        $p2 = $globalGradeMap[$sID][$cID][2] ?? null;
                        if ($p1 !== null && $p2 !== null)
                            $gVal = ($p1 + $p2) / 2;
                        elseif ($p1 !== null)
                            $gVal = $p1;
                        elseif ($p2 !== null)
                            $gVal = $p2;
                    } else { // غیرپودمانی
                        $m1 = $globalGradeMap[$sID][$cID][1] ?? ($globalGradeMap[$sID][$cID][2] ?? null);
                        $p1 = $globalGradeMap[$sID][$cID][3] ?? null;
                        if ($p1 !== null) {
                            $m1Val = ($m1 !== null) ? $m1 : $p1;
                            $gVal = (($p1 * 2) + $m1Val) / 3;
                        }
                    }
                } elseif ($term_id == 6) {
                    // نوبت دوم / سالانه
                    if ($type === 0) { // پودمانی (میانگین ۵ پودمان)
                        $pVals = [];
                        foreach ([1, 2, 3, 4, 6] as $tKey) { // ترم 6 به عنوان پودمان 5
                            if (isset($globalGradeMap[$sID][$cID][$tKey])) {
                                $pVals[] = $globalGradeMap[$sID][$cID][$tKey];
                            }
                        }
                        if (!empty($pVals)) {
                            $gVal = array_sum($pVals) / count($pVals);
                        }
                    } else { // غیرپودمانی
                        $m1 = $globalGradeMap[$sID][$cID][1] ?? ($globalGradeMap[$sID][$cID][2] ?? null);
                        $p1 = $globalGradeMap[$sID][$cID][3] ?? null;
                        $m2 = $globalGradeMap[$sID][$cID][4] ?? ($globalGradeMap[$sID][$cID][5] ?? null);
                        $p2 = $globalGradeMap[$sID][$cID][6] ?? null;

                        if ($p1 !== null && $p2 !== null) {
                            $m1Val = ($m1 !== null) ? $m1 : $p1;
                            $m2Val = ($m2 !== null) ? $m2 : $p2;
                            $gVal = (($p1 * 2) + ($p2 * 4) + $m1Val + $m2Val) / 8;
                        }
                    }
                }

                if ($gVal !== null) {
                    $totalWeighted += ($gVal * $unit);
                    $totalUnits += $unit;
                }
            }
        }

        $studentAverages[$sID] = ($totalUnits > 0) ? round($totalWeighted / $totalUnits, 2) : 0;
    }

    // ۶. لیست دانش‌آموزان کلاس درخواستی
    $stmtStudents = $connect->prepare("SELECT * FROM Students WHERE Stu_classID = :class_id ORDER BY Stu_fullName ASC");
    $stmtStudents->execute([':class_id' => $class_id]);
    $students = $stmtStudents->fetchAll(PDO::FETCH_ASSOC);

    if (empty($students)) {
        exit('<div class="empty-msg">هیچ دانش‌آموزی در این کلاس ثبت نشده است.</div>');
    }

    $termsText = [1 => 'مهر و آبان', 2 => 'آذر', 3 => 'نوبت اول (دی ماه)', 4 => 'اسفند', 5 => 'فروردین و اردیبهشت', 6 => 'نوبت دوم (خرداد)'];

    // ۷. رندر کارنامه تک‌تک دانش‌آموزان
    foreach ($students as $stu):
        $stuID = getValueInsensitive($stu, 'Stu_ID');
        $fullName = getValueInsensitive($stu, 'Stu_fullName');
        $fatherName = getValueInsensitive($stu, 'Stu_fatherName', '-');
        $nationalCode = getValueInsensitive($stu, 'Stu_nationalCode', '-');

        // استخراج رتبه‌ها
        $myAvg = $studentAverages[$stuID] ?? 0;

        // رتبه در کلاس
        $classTotal = 0;
        $classRank = 1;
        foreach ($allStudentsInSchool as $s) {
            if (getValueInsensitive($s, 'Stu_classID') == $class_id) {
                $classTotal++;
                if (($studentAverages[getValueInsensitive($s, 'Stu_ID')] ?? 0) > $myAvg) {
                    $classRank++;
                }
            }
        }

        // رتبه در پایه
        $gradeTotal = 0;
        $gradeRank = 1;
        foreach ($allStudentsInSchool as $s) {
            if (getValueInsensitive($s, 'C_Grade') == $cGrade) {
                $gradeTotal++;
                if (($studentAverages[getValueInsensitive($s, 'Stu_ID')] ?? 0) > $myAvg) {
                    $gradeRank++;
                }
            }
        }

        // رتبه در مدرسه
        $schoolTotal = count($allStudentsInSchool);
        $schoolRank = 1;
        foreach ($allStudentsInSchool as $s) {
            if (($studentAverages[getValueInsensitive($s, 'Stu_ID')] ?? 0) > $myAvg) {
                $schoolRank++;
            }
        }

        // محاسبه مجموع واحدها و نمرات کارنامه فردی
        $stuTotalWeighted = 0;
        $stuTotalUnits = 0;
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
                            <div class="info-row"><span>نام و نام خانوادگی:</span>
                                <strong><?php echo htmlspecialchars($fullName); ?></strong></div>
                            <div class="info-row"><span>نام پدر:</span>
                                <strong><?php echo htmlspecialchars($fatherName); ?></strong></div>
                            <div class="info-row"><span>کد ملی:</span>
                                <strong><?php echo htmlspecialchars($nationalCode); ?></strong></div>
                            <div class="info-row"><span>پایه:</span> <strong><?php echo htmlspecialchars($cGrade); ?></strong> |
                                <span>رشته:</span> <strong><?php echo htmlspecialchars($cMajor); ?></strong></div>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- جدول ۱: دروس عمومی و نظری (غیرپودمانی - نوع 1) -->
            <?php if (!empty($standardCourses)): ?>
                <div class="table-responsive-wrapper">
                    <table class="report-table official-grid">
                        <thead>
                            <tr>
                                <th rowspan="2" style="width: 30px;">ردیف</th>
                                <th rowspan="2" style="width: 60px;">کد درس</th>
                                <th rowspan="2">نام درس عمومی / نظری</th>
                                <th rowspan="2" style="width: 40px;">واحد</th>

                                <?php if (in_array($term_id, [1, 2, 4, 5])): ?>
                                    <th rowspan="2">نمره ارزشیابی</th>
                                <?php elseif ($term_id == 3): ?>
                                    <th colspan="3">نمرات نوبت اول</th>
                                <?php else: ?>
                                    <th colspan="3">نوبت اول</th>
                                    <th colspan="2">نوبت دوم</th>
                                    <th rowspan="2">نمره سالانه</th>
                                <?php endif; ?>

                                <th rowspan="2" style="width: 70px;">نتیجه</th>
                                <th rowspan="2" style="width: 100px;">ملاحظات</th>
                            </tr>
                            <?php if ($term_id == 3): ?>
                                <tr>
                                    <th>مستمر</th>
                                    <th>پایانی</th>
                                    <th>نمره‌نهایی</th>
                                </tr>
                            <?php elseif ($term_id == 6): ?>
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
                            foreach ($standardCourses as $crs):
                                $cID = getValueInsensitive($crs, 'Co_ID');
                                $cCode = getValueInsensitive($crs, 'Co_Code', $cID);
                                $cName = getValueInsensitive($crs, 'Co_Name', 'نامشخص');
                                $cUnit = floatval(getValueInsensitive($crs, 'Co_num', 1)); // اصلاح ستون واحد
                                ?>
                                <tr>
                                    <td><?php echo $rowNum++; ?></td>
                                    <td><?php echo htmlspecialchars($cCode); ?></td>
                                    <td class="course-name"><?php echo htmlspecialchars($cName); ?></td>
                                    <td><?php echo $cUnit; ?></td>

                                    <?php if (in_array($term_id, [1, 2, 4, 5])):
                                        $gVal = $globalGradeMap[$stuID][$cID][$term_id] ?? '-';
                                        if (is_numeric($gVal)) {
                                            $stuTotalWeighted += ($gVal * $cUnit);
                                            $stuTotalUnits += $cUnit;
                                        }
                                        ?>
                                        <td><?php echo $gVal; ?></td>

                                    <?php elseif ($term_id == 3):
                                        $m1 = $globalGradeMap[$stuID][$cID][1] ?? ($globalGradeMap[$stuID][$cID][2] ?? null);
                                        $p1 = $globalGradeMap[$stuID][$cID][3] ?? null;
                                        $final1 = '-';
                                        if ($p1 !== null) {
                                            $m1Val = ($m1 !== null) ? $m1 : $p1;
                                            $final1 = round((($p1 * 2) + $m1Val) / 3, 2);
                                            $stuTotalWeighted += ($final1 * $cUnit);
                                            $stuTotalUnits += $cUnit;
                                        }
                                        ?>
                                        <td><?php echo $m1 ?? '-'; ?></td>
                                        <td><?php echo $p1 ?? '-'; ?></td>
                                        <td><strong><?php echo $final1; ?></strong></td>

                                    <?php else: // نوبت دوم / سالانه (6)
                                        $m1 = $globalGradeMap[$stuID][$cID][1] ?? ($globalGradeMap[$stuID][$cID][2] ?? null);
                                        $p1 = $globalGradeMap[$stuID][$cID][3] ?? null;
                                        $tot1 = ($p1 !== null) ? round((($p1 * 2) + (($m1 !== null) ? $m1 : $p1)) / 3, 2) : '-';

                                        $m2 = $globalGradeMap[$stuID][$cID][4] ?? ($globalGradeMap[$stuID][$cID][5] ?? null);
                                        $p2 = $globalGradeMap[$stuID][$cID][6] ?? null;

                                        $annual = '-';
                                        if ($p1 !== null && $p2 !== null) {
                                            $m1Val = ($m1 !== null) ? $m1 : $p1;
                                            $m2Val = ($m2 !== null) ? $m2 : $p2;
                                            $annual = round((($p1 * 2) + ($p2 * 4) + $m1Val + $m2Val) / 8, 2);
                                            $stuTotalWeighted += ($annual * $cUnit);
                                            $stuTotalUnits += $cUnit;
                                        }
                                        ?>
                                        <td><?php echo $m1 ?? '-'; ?></td>
                                        <td><?php echo $p1 ?? '-'; ?></td>
                                        <td><?php echo $tot1; ?></td>
                                        <td><?php echo $m2 ?? '-'; ?></td>
                                        <td><?php echo $p2 ?? '-'; ?></td>
                                        <td><strong><?php echo $annual; ?></strong></td>
                                    <?php endif; ?>

                                    <td>ناتمام</td>
                                    <td>-</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <!-- جدول ۲: دروس شایستگی / پودمانی (نوع 0) -->
            <?php if (!empty($podmaniCourses)): ?>
                <div class="table-title">دروس شایستگی و پودمانی</div>
                <div class="table-responsive-wrapper">
                    <table class="report-table official-grid podmani-table">
                        <thead>
                            <tr>
                                <th style="width: 30px;">ردیف</th>
                                <th style="width: 60px;">کد درس</th>
                                <th>عنوان درس پودمانی</th>
                                <th style="width: 40px;">واحد</th>
                                <th>پودمان ۱<br><small>(مهر و آبان)</small></th>
                                <th>پودمان ۲<br><small>(آذر)</small></th>
                                <th>پودمان ۳<br><small>(اسفند)</small></th>
                                <th>پودمان ۴<br><small>(فروردین و اردیبهشت)</small></th>
                                <th>پودمان ۵<br><small>(خرداد)</small></th>
                                <th>میانگین / وضعیت</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $pRowNum = 1;
                            foreach ($podmaniCourses as $crs):
                                $cID = getValueInsensitive($crs, 'Co_ID');
                                $cCode = getValueInsensitive($crs, 'Co_Code', $cID);
                                $cName = getValueInsensitive($crs, 'Co_Name', 'نامشخص');
                                $cUnit = floatval(getValueInsensitive($crs, 'Co_num', 1)); // اصلاح ستون واحد
                
                                $p1 = $globalGradeMap[$stuID][$cID][1] ?? '-';
                                $p2 = $globalGradeMap[$stuID][$cID][2] ?? '-';
                                $p3 = $globalGradeMap[$stuID][$cID][4] ?? '-'; // ترم 4 اسفند
                                $p4 = $globalGradeMap[$stuID][$cID][5] ?? '-'; // ترم 5 فروردین/اردیبهشت
                                $p5 = $globalGradeMap[$stuID][$cID][6] ?? '-'; // ترم 6 خرداد
                
                                // محاسبه میانگین پودمانی‌ها برای معدل نهایی
                                $pArray = [];
                                if ($term_id == 3) { // نوبت اول
                                    if (is_numeric($p1))
                                        $pArray[] = $p1;
                                    if (is_numeric($p2))
                                        $pArray[] = $p2;
                                } elseif ($term_id == 6) { // سالانه
                                    foreach ([$p1, $p2, $p3, $p4, $p5] as $pv) {
                                        if (is_numeric($pv))
                                            $pArray[] = $pv;
                                    }
                                } elseif (isset($globalGradeMap[$stuID][$cID][$term_id])) {
                                    if (is_numeric($globalGradeMap[$stuID][$cID][$term_id])) {
                                        $pArray[] = $globalGradeMap[$stuID][$cID][$term_id];
                                    }
                                }

                                if (!empty($pArray)) {
                                    $pAvg = round(array_sum($pArray) / count($pArray), 2);
                                    $stuTotalWeighted += ($pAvg * $cUnit);
                                    $stuTotalUnits += $cUnit;
                                }
                                ?>
                                <tr>
                                    <td><?php echo $pRowNum++; ?></td>
                                    <td><?php echo htmlspecialchars($cCode); ?></td>
                                    <td class="course-name"><?php echo htmlspecialchars($cName); ?></td>
                                    <td><?php echo $cUnit; ?></td>
                                    <td><?php echo $p1; ?></td>
                                    <td><?php echo $p2; ?></td>
                                    <td><?php echo $p3; ?></td>
                                    <td><?php echo $p4; ?></td>
                                    <td><?php echo $p5; ?></td>
                                    <td><strong>احراز شایستگی</strong></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <!-- محاسبه معدل کل واقعی دانش‌آموز -->
            <?php $finalGpa = ($stuTotalUnits > 0) ? round($stuTotalWeighted / $stuTotalUnits, 2) : '-'; ?>

            <!-- باکس رتبه‌ها -->
            <div class="ranks-container">
                <div class="rank-box">
                    <span class="rank-title">رتبه در کلاس:</span>
                    <span class="rank-value"><?php echo $classRank . " از " . $classTotal; ?></span>
                </div>
                <div class="rank-box">
                    <span class="rank-title">رتبه در پایه:</span>
                    <span class="rank-value"><?php echo $gradeRank . " از " . $gradeTotal; ?></span>
                </div>
                <div class="rank-box">
                    <span class="rank-title">رتبه در مدرسه:</span>
                    <span class="rank-value"><?php echo $schoolRank . " از " . $schoolTotal; ?></span>
                </div>
            </div>

            <!-- فوتر کارنامه -->
            <div class="footer-signatures">
                <div class="sig-box">
                    <span>معدل کل: <strong><?php echo $finalGpa; ?></strong></span>
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
