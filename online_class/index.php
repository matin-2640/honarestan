<?php
session_start();
require_once '../connect.php';

if (!isset($_SESSION['ID'], $_SESSION['type'], $_SESSION['state_login'])) {
    header("Location: ../login.php");
    exit;
}

$id = (int)$_SESSION['ID'];
$type = (int)$_SESSION['type'];

if ($type === 0) {
    $sql = "SELECT c.Co_ID, c.Co_name
            FROM courses c
            INNER JOIN students s ON s.Stu_classID = c.Co_classID
            WHERE s.Stu_ID = :id
            ORDER BY c.Co_name";

    $stmt = $connect->prepare($sql);
    $stmt->execute([':id' => $id]);
} elseif ($type === 1) {
    $sql = "SELECT Co_ID, Co_name
            FROM courses
            WHERE Co_teacherID = :id
            ORDER BY Co_name";

    $stmt = $connect->prepare($sql);
    $stmt->execute([':id' => $id]);
} else {
    $sql = "SELECT Co_ID, Co_name
            FROM courses
            ORDER BY Co_name";

    $stmt = $connect->prepare($sql);
    $stmt->execute();
}

$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>کلاس مجازی</title>
    <link rel="stylesheet" href="../styles/font.css">
    <link rel="icon" href="../images/icons/rahdanesh.png">
    <link rel="stylesheet" href="styles/index.css">
</head>

<body>

<div class="app">
    <div class="messenger">

        <aside class="sidebar">

            <div class="sidebar-header">
                <div class="sidebar-title">کلاس مجازی</div>
            </div>

            <div class="search-box">
                <input
                    type="text"
                    class="search"
                    id="courseSearch"
                    placeholder="جستجوی گروه درسی..."
                >
            </div>

            <div class="course-list" id="courseList">

                <?php if (!empty($courses)): ?>

                    <?php foreach ($courses as $course): ?>

                        <a
                            href="chat.php?course_id=<?= (int)$course['Co_ID'] ?>"
                            class="course"
                            data-name="<?= htmlspecialchars($course['Co_name']) ?>"
                        >

                            <div class="avatar">
                                <?= htmlspecialchars(mb_substr($course['Co_name'], 0, 1, 'UTF-8')) ?>
                            </div>

                            <div class="course-info">
                                <div class="course-name">
                                    <?= htmlspecialchars($course['Co_name']) ?>
                                </div>

                                <div class="course-last">
                                    برای ورود به گفتگوی گروه کلیک کنید
                                </div>
                            </div>

                            <div class="course-arrow">
                                ‹
                            </div>

                        </a>

                    <?php endforeach; ?>

                <?php else: ?>

                    <div class="empty">
                        گروه درسی‌ای برای شما وجود ندارد.
                    </div>

                <?php endif; ?>

            </div>

        </aside>

        <main class="welcome">

            <div class="welcome-content">

                <div class="welcome-icon">
                    💬
                </div>

                <h2>
                    کلاس مجازی
                </h2>

                <p>
                    یک گروه درسی را از سمت راست انتخاب کنید
                    تا وارد گفتگوی آن شوید.
                </p>

            </div>

        </main>

    </div>
</div>

<script>
const searchInput = document.getElementById('courseSearch');
const courses = document.querySelectorAll('.course');

searchInput.addEventListener('input', function () {
    const value = this.value.trim().toLowerCase();

    courses.forEach(function (course) {
        const name = course.dataset.name.toLowerCase();

        if (name.includes(value)) {
            course.style.display = 'flex';
        } else {
            course.style.display = 'none';
        }
    });
});
</script>

</body>
</html>