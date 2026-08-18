<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!(isset($_SESSION["state_login"]) && $_SESSION["type"] == 0)) {
    header("location:../login.php");
    exit();
}

include("../connect.php");

$session_student_id = 0;
if (isset($_SESSION["student_id"])) {
    $session_student_id = intval($_SESSION["student_id"]);
} elseif (isset($_SESSION["user_id"])) {
    $session_student_id = intval($_SESSION["user_id"]);
} elseif (isset($_SESSION["ID"])) {
    $session_student_id = intval($_SESSION["ID"]);
}

$term_id = isset($_POST['term_id']) ? intval($_POST['term_id']) : 0;

if ($session_student_id <= 0 || $term_id <= 0) {
    echo '<div class="error-msg">اطلاعات ورودی نامعتبر است.</div>';
    exit();
}

try {
    $stmtLicense = $connect->prepare("
        SELECT publish 
        FROM report_license 
        WHERE term = :term_id AND publish = 1 
        LIMIT 1
    ");
    $stmtLicense->execute([':term_id' => $term_id]);
    $license = $stmtLicense->fetch(PDO::FETCH_ASSOC);

    if (!$license) {
        echo '<div class="empty-msg">این کارنامه هنوز در دسترس هنرجویان قرار نگرفته است.</div>';
        exit();
    }

    $stmtStudent = $connect->prepare("
        SELECT s.*, c.C_Grade, c.C_Major, c.C_ID 
        FROM Students s 
        JOIN Classes c ON s.Stu_classID = c.C_ID 
        WHERE s.Stu_ID = :student_id LIMIT 1
    ");
    $stmtStudent->execute([':student_id' => $session_student_id]);
    $studentInfo = $stmtStudent->fetch(PDO::FETCH_ASSOC);

    if (!$studentInfo) {
        echo '<div class="error-msg">اطلاعات دانش‌آموز یافت نشد.</div>';
        exit();
    }

    $stuLower = array_change_key_case($studentInfo, CASE_LOWER);
    $class_id = intval($stuLower['c_id'] ?? 0);
    $cGrade = $stuLower['c_grade'] ?? '';
    $cMajor = $stuLower['c_major'] ?? '';
    $fullName = $stuLower['stu_fullname'] ?? '';
    $fatherName = $stuLower['stu_fathername'] ?? '-';
    $nationalCode = $stuLower['stu_nationalcode'] ?? '-';

    $stmtCourses = $connect->prepare("SELECT * FROM courses WHERE CO_ClassID = :class_id");
    $stmtCourses->execute([':class_id' => $class_id]);
    $courses = $stmtCourses->fetchAll(PDO::FETCH_ASSOC);

    if (empty($courses)) {
        echo '<div class="empty-msg">هیچ درسی برای کلاس شما تعریف نشده است.</div>';
        exit();
    }

    $stmtStudentGrades = $connect->prepare("SELECT * FROM grades WHERE G_StudentID = :student_id");
    $stmtStudentGrades->execute([':student_id' => $session_student_id]);
    $studentGradesRaw = $stmtStudentGrades->fetchAll(PDO::FETCH_ASSOC);

    $myGradeMap = [];
    foreach ($studentGradesRaw as $g) {
        $gLower = array_change_key_case($g, CASE_LOWER);
        $cID = $gLower['g_courseid'] ?? null;
        $tNum = $gLower['g_term'] ?? null;
        $gNum = $gLower['g_num'] ?? null;

        if ($cID !== null && $tNum !== null && $gNum !== null) {
            $myGradeMap[$cID][$tNum] = floatval($gNum);
        }
    }

    foreach ($courses as $crs) {
        $crsLower = array_change_key_case($crs, CASE_LOWER);
        $coID = intval($crsLower['co_id'] ?? 0);
        $coType = isset($crsLower['co_type']) ? intval($crsLower['co_type']) : 1;

        if ($coID > 0) {
            if (in_array($term_id, [1, 2, 4, 5])) {
                if (!isset($myGradeMap[$coID][$term_id])) {
                    echo '<div class="empty-msg">کارنامه این دوره در دسترس نیست.</div>';
                    exit();
                }
            } elseif ($term_id == 3) {
                if ($coType == 0) {
                    if (!isset($myGradeMap[$coID][1]) && !isset($myGradeMap[$coID][2])) {
                        echo '<div class="empty-msg">کارنامه این دوره در دسترس نیست.</div>';
                        exit();
                    }
                } else {
                    if (!isset($myGradeMap[$coID][3])) {
                        echo '<div class="empty-msg">کارنامه این دوره در دسترس نیست.</div>';
                        exit();
                    }
                }
            } elseif ($term_id == 6) {
                if ($coType == 0) {
                    if (!isset($myGradeMap[$coID][6])) {
                        echo '<div class="empty-msg">کارنامه این دوره در دسترس نیست.</div>';
                        exit();
                    }
                } else {
                    if (!isset($myGradeMap[$coID][3]) || !isset($myGradeMap[$coID][6])) {
                        echo '<div class="empty-msg">کارنامه این دوره در دسترس نیست.</div>';
                        exit();
                    }
                }
            }
        }
    }

    $standardCourses = [];
    $podmaniCourses = [];
    foreach ($courses as $crs) {
        $crsLower = array_change_key_case($crs, CASE_LOWER);
        $cID = intval($crsLower['co_id'] ?? 0);
        $coType = isset($crsLower['co_type']) ? intval($crsLower['co_type']) : 1;

        if ($cID > 0) {
            if ($coType == 0) {
                $podmaniCourses[] = $crs;
            } else {
                $standardCourses[] = $crs;
            }
        }
    }

    $stmtAllStudents = $connect->prepare("
        SELECT s.*, c.C_Grade, c.C_Major 
        FROM Students s 
        JOIN Classes c ON s.Stu_classID = c.C_ID
    ");
    $stmtAllStudents->execute();
    $allStudentsInSchool = $stmtAllStudents->fetchAll(PDO::FETCH_ASSOC);

    $stmtAllGrades = $connect->prepare("SELECT * FROM grades");
    $stmtAllGrades->execute();
    $rawGrades = $stmtAllGrades->fetchAll(PDO::FETCH_ASSOC);

    $globalGradeMap = [];
    foreach ($rawGrades as $g) {
        $gLower = array_change_key_case($g, CASE_LOWER);
        $sID = $gLower['g_studentid'] ?? null;
        $cID = $gLower['g_courseid'] ?? null;
        $tNum = $gLower['g_term'] ?? null;
        $gNum = $gLower['g_num'] ?? null;

        if ($sID !== null && $cID !== null && $tNum !== null && $gNum !== null) {
            $globalGradeMap[$sID][$cID][$tNum] = floatval($gNum);
        }
    }

    $stmtAllCourses = $connect->prepare("SELECT * FROM courses");
    $stmtAllCourses->execute();
    $rawCourses = $stmtAllCourses->fetchAll(PDO::FETCH_ASSOC);

    $courseInfoMap = [];
    foreach ($rawCourses as $co) {
        $coLower = array_change_key_case($co, CASE_LOWER);
        $coID = $coLower['co_id'] ?? null;

        if ($coID) {
            $courseInfoMap[$coID] = [
                'unit' => floatval($coLower['co_num'] ?? 1),
                'type' => intval($coLower['co_type'] ?? 1),
                'class_id' => $coLower['co_classid'] ?? null
            ];
        }
    }

    $studentAverages = [];
    foreach ($allStudentsInSchool as $s) {
        $sLower = array_change_key_case($s, CASE_LOWER);
        $sID = $sLower['stu_id'] ?? null;
        $sClassID = $sLower['stu_classid'] ?? null;

        $totalWeighted = 0;
        $totalUnits = 0;

        foreach ($courseInfoMap as $cID => $cInfo) {
            if ($cInfo['class_id'] == $sClassID) {
                $unit = $cInfo['unit'];
                $type = $cInfo['type'];
                $gVal = null;

                if (in_array($term_id, [1, 2, 4, 5])) {
                    if (isset($globalGradeMap[$sID][$cID][$term_id])) {
                        $gVal = $globalGradeMap[$sID][$cID][$term_id];
                    }
                } elseif ($term_id == 3) {
                    if ($type == 0) {
                        $p1 = $globalGradeMap[$sID][$cID][1] ?? null;
                        $p2 = $globalGradeMap[$sID][$cID][2] ?? null;

                        if ($p1 !== null && $p2 !== null) {
                            $gVal = ($p1 + $p2) / 2;
                        } elseif ($p1 !== null) {
                            $gVal = $p1;
                        } elseif ($p2 !== null) {
                            $gVal = $p2;
                        }
                    } else {
                        $m1 = $globalGradeMap[$sID][$cID][1] ?? ($globalGradeMap[$sID][$cID][2] ?? null);
                        $p1 = $globalGradeMap[$sID][$cID][3] ?? null;
                        if ($p1 !== null) {
                            $m1Val = ($m1 !== null) ? $m1 : $p1;
                            $gVal = (($p1 * 2) + $m1Val) / 3;
                        }
                    }
                } elseif ($term_id == 6) {
                    if ($type == 0) {
                        $pVals = [];
                        foreach ([1, 2, 3, 4, 6] as $tKey) {
                            if (isset($globalGradeMap[$sID][$cID][$tKey])) {
                                $pVals[] = $globalGradeMap[$sID][$cID][$tKey];
                            }
                        }
                        if (count($pVals) > 0) {
                            $gVal = array_sum($pVals) / count($pVals);
                        }
                    } else {
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

        if ($totalUnits > 0) {
            $studentAverages[$sID] = round($totalWeighted / $totalUnits, 2);
        } else {
            $studentAverages[$sID] = 0;
        }
    }

    $termsText = [
        1 => 'مهر و آبان',
        2 => 'آذر',
        3 => 'نوبت اول (دی ماه)',
        4 => 'اسفند',
        5 => 'فروردین و اردیبهشت',
        6 => 'نوبت دوم (خرداد)'
    ];

    $myAvg = $studentAverages[$session_student_id] ?? 0;

    $classTotal = 0;
    $classRank = 1;
    foreach ($allStudentsInSchool as $s) {
        $sLower = array_change_key_case($s, CASE_LOWER);
        $otherID = $sLower['stu_id'] ?? 0;
        $otherClassID = $sLower['stu_classid'] ?? 0;

        if ($otherClassID == $class_id) {
            $classTotal++;
            $otherAvg = $studentAverages[$otherID] ?? 0;
            if ($otherAvg > $myAvg) {
                $classRank++;
            }
        }
    }

    $gradeTotal = 0;
    $gradeRank = 1;
    foreach ($allStudentsInSchool as $s) {
        $sLower = array_change_key_case($s, CASE_LOWER);
        $otherID = $sLower['stu_id'] ?? 0;
        $otherGrade = $sLower['c_grade'] ?? '';

        if ($otherGrade == $cGrade) {
            $gradeTotal++;
            $otherAvg = $studentAverages[$otherID] ?? 0;
            if ($otherAvg > $myAvg) {
                $gradeRank++;
            }
        }
    }

    $schoolTotal = count($allStudentsInSchool);
    $schoolRank = 1;
    foreach ($allStudentsInSchool as $s) {
        $sLower = array_change_key_case($s, CASE_LOWER);
        $otherID = $sLower['stu_id'] ?? 0;
        $otherAvg = $studentAverages[$otherID] ?? 0;

        if ($otherAvg > $myAvg) {
            $schoolRank++;
        }
    }

    $stuTotalWeighted = 0;
    $stuTotalUnits = 0;
    ?>

    <div class="mymediu-card">
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
                            <strong><?php echo htmlspecialchars($fullName); ?></strong>
                        </div>
                        <div class="info-row"><span>نام پدر:</span>
                            <strong><?php echo htmlspecialchars($fatherName); ?></strong>
                        </div>
                        <div class="info-row"><span>کد ملی:</span>
                            <strong><?php echo htmlspecialchars($nationalCode); ?></strong>
                        </div>
                        <div class="info-row"><span>پایه:</span> <strong><?php echo htmlspecialchars($cGrade); ?></strong> |
                            <span>رشته:</span> <strong><?php echo htmlspecialchars($cMajor); ?></strong>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

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
                            $crsLower = array_change_key_case($crs, CASE_LOWER);
                            $cID = $crsLower['co_id'] ?? 0;
                            $cCode = $crsLower['co_code'] ?? $cID;
                            $cName = $crsLower['co_name'] ?? 'نامشخص';
                            $cUnit = floatval($crsLower['co_num'] ?? 1);
                            ?>
                            <tr>
                                <td><?php echo $rowNum++; ?></td>
                                <td><?php echo htmlspecialchars($cCode); ?></td>
                                <td class="course-name"><?php echo htmlspecialchars($cName); ?></td>
                                <td><?php echo $cUnit; ?></td>

                                <?php if (in_array($term_id, [1, 2, 4, 5])):
                                    $gVal = $globalGradeMap[$session_student_id][$cID][$term_id] ?? '-';
                                    if (is_numeric($gVal)) {
                                        $stuTotalWeighted += ($gVal * $cUnit);
                                        $stuTotalUnits += $cUnit;
                                    }
                                    ?>
                                    <td><?php echo $gVal; ?></td>

                                <?php elseif ($term_id == 3):
                                    $m1 = $globalGradeMap[$session_student_id][$cID][1] ?? ($globalGradeMap[$session_student_id][$cID][2] ?? null);
                                    $p1 = $globalGradeMap[$session_student_id][$cID][3] ?? null;
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

                                <?php else:
                                    $m1 = $globalGradeMap[$session_student_id][$cID][1] ?? ($globalGradeMap[$session_student_id][$cID][2] ?? null);
                                    $p1 = $globalGradeMap[$session_student_id][$cID][3] ?? null;

                                    $tot1 = '-';
                                    if ($p1 !== null) {
                                        $m1Val = ($m1 !== null) ? $m1 : $p1;
                                        $tot1 = round((($p1 * 2) + $m1Val) / 3, 2);
                                    }

                                    $m2 = $globalGradeMap[$session_student_id][$cID][4] ?? ($globalGradeMap[$session_student_id][$cID][5] ?? null);
                                    $p2 = $globalGradeMap[$session_student_id][$cID][6] ?? null;

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
                            $crsLower = array_change_key_case($crs, CASE_LOWER);
                            $cID = $crsLower['co_id'] ?? 0;
                            $cCode = $crsLower['co_code'] ?? $cID;
                            $cName = $crsLower['co_name'] ?? 'نامشخص';
                            $cUnit = floatval($crsLower['co_num'] ?? 1);

                            $p1 = $globalGradeMap[$session_student_id][$cID][1] ?? '-';
                            $p2 = $globalGradeMap[$session_student_id][$cID][2] ?? '-';
                            $p3 = $globalGradeMap[$session_student_id][$cID][4] ?? '-';
                            $p4 = $globalGradeMap[$session_student_id][$cID][5] ?? '-';
                            $p5 = $globalGradeMap[$session_student_id][$cID][6] ?? '-';

                            $pArray = [];
                            if ($term_id == 3) {
                                if (is_numeric($p1))
                                    $pArray[] = $p1;
                                if (is_numeric($p2))
                                    $pArray[] = $p2;
                            } elseif ($term_id == 6) {
                                $allP = [$p1, $p2, $p3, $p4, $p5];
                                foreach ($allP as $pv) {
                                    if (is_numeric($pv))
                                        $pArray[] = $pv;
                                }
                            } elseif (isset($globalGradeMap[$session_student_id][$cID][$term_id])) {
                                $currVal = $globalGradeMap[$session_student_id][$cID][$term_id];
                                if (is_numeric($currVal)) {
                                    $pArray[] = $currVal;
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

        <?php
        $finalGpa = '-';
        if ($stuTotalUnits > 0) {
            $finalGpa = round($stuTotalWeighted / $stuTotalUnits, 2);
        }
        ?>

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

    <div class="single-print-btn">
        <button type="button" class="btn-print-single" onclick="window.print();">
            🖨 چاپ / دانلود PDF کارنامه
        </button>
    </div>

    <?php
} catch (PDOException $e) {
    echo '<div class="error-msg">خطای پایگاه داده: ' . htmlspecialchars($e->getMessage()) . '</div>';
}
