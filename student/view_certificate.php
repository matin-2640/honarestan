<?php
session_start();
require_once '../connect.php';

if (!isset($connect) || !($connect instanceof PDO))
    die('خطا در اتصال به دیتابیس.');
$connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$connect->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

if (!isset($_SESSION['ID'])) {
    header('Location: login.php');
    exit;
}

$studentId = (int) $_SESSION['ID'];
$certificateId = (int) ($_GET['id'] ?? 0);

if ($certificateId <= 0) {
    header('Location: student_certificate.php');
    exit;
}

$stmt = $connect->prepare("
    SELECT c.ID,c.title,c.description,c.type,c.student_ID,s.Stu_fullName,s.Stu_classID,cl.C_grade,cl.C_major
    FROM certificate c
    INNER JOIN students s ON c.student_ID=s.Stu_ID
    LEFT JOIN classes cl ON s.Stu_classID=cl.C_ID
    WHERE c.ID=:certificate_id AND c.student_ID=:student_id
    LIMIT 1
");
$stmt->execute([':certificate_id' => $certificateId, ':student_id' => $studentId]);
$certificate = $stmt->fetch();

if (!$certificate) {
    http_response_code(404);
    die('این لوح تقدیر برای حساب کاربری شما قابل مشاهده نیست.');
}

$studentName = $certificate['Stu_fullName'];
$title = $certificate['title'];
$description = $certificate['description'] ?? '';
$type = (int) $certificate['type'];

switch ($type) {
    case 1:
        $typeName = 'آموزشی';
        $templateClass = 'template-education';
        break;
    case 2:
        $typeName = 'ورزشی';
        $templateClass = 'template-sport';
        break;
    case 3:
        $typeName = 'فرهنگ و هنری';
        $templateClass = 'template-art';
        break;
    default:
        $typeName = 'لوح تقدیر';
        $templateClass = 'template-education';
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="../styles/show_certificate_student.css" />
    <link rel="stylesheet" href="../styles/font.css">
    <link rel="icon" href="../images/icons/rahdanesh.png" />
</head>

<body>

    <div class="toolbar">
        <a href="../panel.php" class="back-button">
            <img src="../images/icons/back.png" alt="بازگشت">
            بازگشت به پنل
        </a>
        <button class="print-button" onclick="window.print()">چاپ لوح تقدیر</button>
    </div>

    <div class="certificate-wrapper">
        <div class="certificate <?= $templateClass ?>">

            <div class="certificate-title">
                <?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?>
            </div>

            <div class="certificate-type">
                نوع لوح:
                <?= htmlspecialchars($typeName, ENT_QUOTES, 'UTF-8') ?>
            </div>

            <div class="main-content">
                <p class="certificate-intro">بدین‌وسیله از دانش‌آموز گرامی</p>

                <div class="student-name">
                    <?= htmlspecialchars($studentName, ENT_QUOTES, 'UTF-8') ?>
                </div>

                <div class="description">
                    <?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8') ?>
                </div>

                <?php if (!empty($certificate['C_grade']) || !empty($certificate['C_major'])): ?>
                    <div class="class-info">
                        کلاس:
                        <?= htmlspecialchars($certificate['C_grade'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                        -
                        <?= htmlspecialchars($certificate['C_major'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="bottom-area">
                <div class="stamp-area">
                    <div class="stamp-title">مهر هنرستان</div>
                    <img src="../images/icons/rahdanesh.png" alt="مهر هنرستان" class="stamp-image">
                </div>

                <div class="signature-area">
                    <div class="signature-title">امضای مدیر هنرستان</div>
                    <div class="signature-line"></div>
                    <div class="signature-name">سید برهان حسینی</div>
                </div>
            </div>

        </div>
    </div>

</body>

</html>