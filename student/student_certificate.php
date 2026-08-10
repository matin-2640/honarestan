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

$stmt = $connect->prepare("
    SELECT c.ID,c.title,c.description,c.type,c.student_ID,s.Stu_fullName
    FROM certificate c
    INNER JOIN students s ON c.student_ID=s.Stu_ID
    WHERE c.student_ID=:student_id
    ORDER BY c.ID DESC
");
$stmt->execute([':student_id' => $studentId]);
$certificates = $stmt->fetchAll();

$studentName = $certificates[0]['Stu_fullName'] ?? '';

function getCertificateType($type)
{
    switch ((int) $type) {
        case 1:
            return ['title' => 'لوح تقدیر آموزشی', 'short' => 'آموزشی', 'class' => 'education'];
        case 2:
            return ['title' => 'لوح تقدیر ورزشی', 'short' => 'ورزشی', 'class' => 'sport'];
        case 3:
            return ['title' => 'لوح تقدیر فرهنگ و هنری', 'short' => 'فرهنگ و هنری', 'class' => 'art'];
        default:
            return ['title' => 'لوح تقدیر', 'short' => 'نامشخص', 'class' => 'education'];
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>لوح‌های تقدیر من</title>
    <link rel="stylesheet" href="../styles/show_certificate.css" />
    <link rel="stylesheet" href="../styles/font.css">
    <link rel="icon" href="../images/icons/rahdanesh.png" />
</head>

<body>
    <div class="page">
        <div class="topbar">
            <div class="welcome">
                <h1>لوح‌های تقدیر من</h1>
                <p><?= htmlspecialchars($studentName, ENT_QUOTES, 'UTF-8') ?> عزیز، افتخارات و تقدیرهای شما</p>
            </div>
            <a href="../panel.php" class="back-button">
                <img src="../images/icons/back.png" alt="بازگشت">
                بازگشت به پنل
            </a>
        </div>

        <div class="intro">
            <div class="intro-title">
                <div class="intro-icon">🏆</div>
                <h2>افتخارات و تقدیرنامه‌ها</h2>
            </div>
            <p>در این بخش می‌توانید تمام لوح‌های تقدیری که برای شما صادر شده است را مشاهده کنید. برای مشاهده نسخه کامل
                هر لوح، روی کارت آن کلیک کنید.</p>
        </div>

        <?php if (empty($certificates)): ?>
            <div class="empty">
                <h3>هنوز لوح تقدیری برای شما ثبت نشده است.</h3>
                <p>در صورت دریافت لوح تقدیر، آن را در این قسمت مشاهده خواهید کرد.</p>
            </div>
        <?php else: ?>
            <div class="certificates-grid">
                <?php foreach ($certificates as $certificate): ?>
                    <?php $typeInfo = getCertificateType($certificate['type']); ?>
                    <div class="certificate-card <?= $typeInfo['class'] ?>">
                        <span class="card-type"><?= htmlspecialchars($typeInfo['short'], ENT_QUOTES, 'UTF-8') ?></span>
                        <h3><?= htmlspecialchars($certificate['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                        <div class="student-name"><?= htmlspecialchars($certificate['Stu_fullName'], ENT_QUOTES, 'UTF-8') ?>
                        </div>
                        <div class="description"><?= htmlspecialchars($certificate['description'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                        </div>
                        <a href="view_certificate.php?id=<?= (int) $certificate['ID'] ?>" class="view-button">مشاهده لوح
                            تقدیر</a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>