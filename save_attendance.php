<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include("connect.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
    $a_date = isset($_POST['a_date']) ? trim($_POST['a_date']) : '';
    $attendanceData = isset($_POST['attendance']) ? $_POST['attendance'] : [];

    if ($course_id > 0 && !empty($a_date)) {

        try {
            $connect->beginTransaction();

            // ۱. پاک کردن غایبین قبلی این جلسه (برای امکان ویرایش مجدد)
            $stmtDel = $connect->prepare("DELETE FROM attendance WHERE A_courseID = :coid AND A_date = :adate");
            $stmtDel->execute([':coid' => $course_id, ':adate' => $a_date]);

            // ۲. آماده‌سازی کوئری درج
            $stmtInsert = $connect->prepare("INSERT INTO attendance (A_studentID, A_date, A_courseID, A_state) VALUES (:sid, :adate, :coid, 0)");

            $absentIds = [];

            // ۳. ذخیره «فقط» دانش‌آموزان غایب
            foreach ($attendanceData as $studentId => $stateValue) {

                $studentId = intval($studentId);
                $stateValue = intval($stateValue);

                // فقط اگر غایب بود (0) در دیتابیس ثبت می‌شود
                if ($stateValue === 0) {
                    $stmtInsert->execute([
                        ':sid' => $studentId,
                        ':adate' => $a_date,
                        ':coid' => $course_id
                    ]);
                    $absentIds[] = $studentId;
                }
            }

            $connect->commit();

            $idsParam = implode(',', $absentIds);
            $redirectUrl = "sms/Attendance_sms.php?ids=" . urlencode($idsParam);

            ?>
            <!DOCTYPE html>
            <html lang="fa" dir="rtl">
            <head>
                <meta charset="UTF-8">
                <title>ذخیره شد</title>
                <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/vazirmatn-font-face.css" />
                <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
                <style>body { font-family: 'Vazirmatn', sans-serif; background-color: #f8fafc; }</style>
            </head>
            <body>
                <script>
                    Swal.fire({
                        icon: 'success',
                        title: 'با موفقیت ثبت شد',
                        text: 'اطلاعات حضور و غیاب دانش‌آموزان ثبت گردید.',
                        confirmButtonText: 'انتقال به بخش پیامک',
                        confirmButtonColor: '#2563eb',
                        timer: 6000,
                        timerProgressBar: true
                    }).then(function() {
                        window.location.href = "<?php echo $redirectUrl; ?>";
                    });
                </script>
            </body>
            </html>
            <?php
            exit();

        } catch (Exception $e) {
            $connect->rollBack();
            echo "خطا در ثبت اطلاعات: " . $e->getMessage();
        }
    }
}
?>