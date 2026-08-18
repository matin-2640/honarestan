<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!(isset($_SESSION["state_login"]) && $_SESSION["type"] == 0)) {
    header("location:../login.php");
    exit();
}

$session_student_id = 0;

if (isset($_SESSION["ID"])) {
    $session_student_id = intval($_SESSION["ID"]);
} elseif (isset($_SESSION["student_id"])) {
    $session_student_id = intval($_SESSION["student_id"]);
} elseif (isset($_SESSION["user_id"])) {
    $session_student_id = intval($_SESSION["user_id"]);
}

if ($session_student_id <= 0) {
    header("location:../login.php");
    exit();
}

require_once "../connect.php";
require_once "../teacher/jdf.php";

$class_id = 0;

try {
    $stmt = $connect->prepare("
        SELECT Stu_classID
        FROM students
        WHERE Stu_ID = :student_id
        LIMIT 1
    ");

    $stmt->execute([
        ':student_id' => $session_student_id
    ]);

    $studentData = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($studentData && isset($studentData['Stu_classID'])) {
        $class_id = intval($studentData['Stu_classID']);
    }
} catch (PDOException $e) {
    $class_id = 0;
}

if (isset($_GET['action']) && $_GET['action'] === 'search_assignments') {
    header('Content-Type: application/json; charset=utf-8');

    try {
        $search = trim($_GET['search'] ?? '');
        $showAll = isset($_GET['show_all']) && $_GET['show_all'] === '1';

        if ($class_id <= 0) {
            echo json_encode([
                'success' => true,
                'assignments' => []
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }

        $sql = "
            SELECT
                a.id,
                a.title,
                a.file_path,
                a.class_id,
                a.teacher_id,
                a.expiration_date,
                a.description,
                t.T_fullName,
                c.C_grade,
                c.C_major
            FROM assignments a
            LEFT JOIN teachers t
                ON a.teacher_id = t.T_ID
            LEFT JOIN classes c
                ON a.class_id = c.C_ID
            WHERE a.class_id = :class_id
        ";

        $params = [
            ':class_id' => $class_id
        ];

        if (!$showAll && $search !== '') {
            $sql .= "
                AND (
                    a.title LIKE :search
                    OR t.T_fullName LIKE :search
                )
            ";

            $params[':search'] = '%' . $search . '%';
        }

        $sql .= " ORDER BY a.id DESC";

        $stmt = $connect->prepare($sql);
        $stmt->execute($params);

        $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $todayYear = intval(jdate('Y'));
        $todayMonth = intval(jdate('m'));
        $todayDay = intval(jdate('d'));

        $todayTotalDays =
            ($todayYear * 365) +
            ($todayMonth * 30) +
            $todayDay;

        foreach ($assignments as &$item) {

            $expDate = trim($item['expiration_date'] ?? '');

            $isExpired = false;

            if (
                !empty($expDate) &&
                strtolower($expDate) !== 'null' &&
                strtolower($expDate) !== 'none'
            ) {
                $normalizedExp =
                    str_replace(['-', '.'], '/', $expDate);

                $expParts =
                    explode('/', $normalizedExp);

                if (count($expParts) === 3) {

                    $expYear = intval($expParts[0]);
                    $expMonth = intval($expParts[1]);
                    $expDay = intval($expParts[2]);

                    $expTotalDays =
                        ($expYear * 365) +
                        ($expMonth * 30) +
                        $expDay;

                    if ($expTotalDays < $todayTotalDays) {
                        $isExpired = true;
                    }
                }
            }

            $item['is_expired'] = $isExpired;
        }

        unset($item);

        echo json_encode([
            'success' => true,
            'assignments' => $assignments
        ], JSON_UNESCAPED_UNICODE);

    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'خطا در دریافت تمرین‌ها.'
        ], JSON_UNESCAPED_UNICODE);
    }

    exit();
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>تکالیف و تمرین‌ها</title>

    <link rel="icon" href="../images/icons/rahdanesh.png">

    <link rel="stylesheet" href="../styles/font.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="stylesheet" href="../styles/view_assignment.css">

    <script>
        (function () {
            const savedTheme =
                localStorage.getItem("theme") || "light";

            document.documentElement.setAttribute(
                "data-theme",
                savedTheme
            );
        })();
    </script>

    <style>
        .search-area {
            width: 100%;
            margin-bottom: 25px;
            padding: 18px;
            border-radius: 14px;
            background: #111827;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.12);
        }

        .search-wrapper {
            position: relative;
            width: 100%;
        }

        .search-wrapper i {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 17px;
            pointer-events: none;
        }

        #assignmentSearch {
            width: 100%;
            height: 48px;
            padding: 0 48px 0 15px;
            border: 1px solid #374151;
            border-radius: 10px;
            outline: none;
            background: #1f2937;
            color: #ffffff;
            font-family: inherit;
            font-size: 14px;
            transition: 0.2s;
        }

        #assignmentSearch::placeholder {
            color: #9ca3af;
        }

        #assignmentSearch:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        }

        .show-all-wrapper {
            display: flex;
            align-items: center;
            gap: 9px;
            margin-top: 14px;
            color: #e5e7eb;
            font-size: 14px;
            cursor: pointer;
            user-select: none;
        }

        #showAllAssignments {
            width: 17px;
            height: 17px;
            cursor: pointer;
            accent-color: #2563eb;
        }

        .search-status {
            margin-top: 10px;
            color: #9ca3af;
            font-size: 12px;
            min-height: 18px;
        }

        .loading-assignments {
            width: 100%;
            text-align: center;
            padding: 40px 10px;
            color: #64748b;
        }

        .loading-assignments i {
            font-size: 28px;
            margin-bottom: 10px;
        }

        .btn-view-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background-color: #2563eb;
            color: #ffffff;
            text-decoration: none;
            padding: 12px 20px;
            border-radius: 8px;
            font-weight: bold;
            font-size: 12px;
            margin-top: 10px;
            max-width: 160px;
        }
    </style>
</head>

<body>

    <div class="page-container">

        <header class="page-header">

            <div class="header-title-wrapper">

                <h1>
                    <img src="../images/icons/user.png" alt="" id="stu">

                    تمرین‌های کلاس من
                </h1>

                <p>
                    لیست تکالیف و پروژه‌های بارگذاری شده
                </p>

            </div>

        </header>

        <main class="content-area">

            <div class="search-area">

                <div class="search-wrapper">

                    <i class="fa-solid fa-magnifying-glass"></i>

                    <input type="text" id="assignmentSearch" placeholder="جستجو بر اساس عنوان تمرین یا نام استاد..."
                        autocomplete="off">

                </div>

                <label class="show-all-wrapper">

                    <input type="checkbox" id="showAllAssignments">

                    <span>
                        نمایش همه
                    </span>

                </label>

                <div id="searchStatus" class="search-status">
                </div>

            </div>

            <div id="assignmentsContainer">

                <div class="empty-state">
                    <p>
                        برای نمایش تمرین‌ها عبارت موردنظر را جستجو کنید.
                    </p>

                </div>

            </div>

        </main>

    </div>

    <a href="../admin_panel.php" id="smsParentBtn" class="btn-view-link">

        بازگشت به پنل مدیریت

    </a>

    <div id="assignmentModal" class="modal-overlay" onclick="closeAssignmentModal(event)">

        <div class="modal-content" onclick="event.stopPropagation();">

            <div class="modal-header">

                <h2 id="modalTitle">
                    عنوان تمرین
                </h2>

                <button class="modal-close-btn" onclick="closeModalDirect()">

                    <img src="../images/icons/zarb.png" alt="" id="zarb">

                </button>

            </div>

            <div class="modal-body">

                <div class="modal-meta-grid">

                    <div class="meta-item">

                        <strong>
                            کلاس:
                        </strong>

                        <span id="modalClass">
                            -
                        </span>

                    </div>

                    <div class="meta-item">

                        <strong>
                            استاد:
                        </strong>

                        <span id="modalTeacher">
                            -
                        </span>

                    </div>

                    <div class="meta-item">

                        <strong>
                            مهلت تحویل:
                        </strong>

                        <span id="modalExpiration">
                            -
                        </span>

                    </div>

                    <div class="meta-item">

                        <strong>
                            وضعیت:
                        </strong>

                        <span id="modalStatusBadge" class="badge">
                            -
                        </span>

                    </div>

                </div>

                <div class="modal-description-section">

                    <h4>
                        توضیحات تمرین:
                    </h4>

                    <div id="modalDescription" class="description-text">

                        توضیحاتی برای این تمرین درج نشده است.

                    </div>

                </div>

                <div id="modalFileSection" class="modal-file-section" style="display:none;">

                    <a id="modalDownloadLink" href="#" class="modal-download-btn" download>

                        <i class="fa-solid fa-download"></i>

                        دانلود فایل تمرین

                    </a>

                </div>

            </div>

        </div>

    </div>

    <script src="../js/theme.js"></script>

    <script>

        document.addEventListener(
            "DOMContentLoaded",
            function () {

                const themeToggleBtn =
                    document.getElementById("themeToggle");

                if (themeToggleBtn) {

                    themeToggleBtn.addEventListener(
                        "click",
                        function () {

                            const currentTheme =
                                document.documentElement
                                    .getAttribute("data-theme");

                            const newTheme =
                                currentTheme === "dark"
                                    ? "light"
                                    : "dark";

                            document.documentElement
                                .setAttribute(
                                    "data-theme",
                                    newTheme
                                );

                            localStorage.setItem(
                                "theme",
                                newTheme
                            );
                        }
                    );
                }

            }
        );

        const assignmentSearch =
            document.getElementById(
                "assignmentSearch"
            );

        const showAllAssignments =
            document.getElementById(
                "showAllAssignments"
            );

        const assignmentsContainer =
            document.getElementById(
                "assignmentsContainer"
            );

        const searchStatus =
            document.getElementById(
                "searchStatus"
            );

        let searchTimer = null;

        function escapeHtml(value) {

            const div =
                document.createElement("div");

            div.textContent =
                value ?? "";

            return div.innerHTML;
        }

        function renderAssignments(assignments) {

            if (!assignments ||
                assignments.length === 0) {

                assignmentsContainer.innerHTML = `
            <div class="empty-state">
                <i class="fa-regular fa-folder-open"></i>
                <p>
                    هیچ تمرینی پیدا نشد.
                </p>
            </div>
        `;

                return;
            }

            let html =
                '<div class="assignments-grid">';

            assignments.forEach(function (item) {

                const title =
                    item.title?.trim() ||
                    "بدون عنوان";

                const teacherName =
                    item.T_fullName?.trim() ||
                    "استاد مربوطه";

                let className =
                    (
                        (item.C_grade || "") +
                        " " +
                        (item.C_major || "")
                    ).trim();

                if (!className) {
                    className = "کلاس شما";
                }

                const expDate =
                    item.expiration_date?.trim() ||
                    "";

                const isExpired =
                    item.is_expired === true ||
                    item.is_expired === 1 ||
                    item.is_expired === "1";

                const description =
                    item.description?.trim() ||
                    "";

                const filePath =
                    item.file_path?.trim() ||
                    "";

                const hasFile =
                    filePath !== "" &&
                    filePath.toLowerCase() !== "none";

                const modalData = {
                    title: title,
                    class: className,
                    teacher: teacherName,
                    expiration:
                        expDate !== ""
                            ? expDate
                            : "نامشخص",
                    status:
                        isExpired
                            ? "منقضی شده"
                            : "فعال",
                    description: description,
                    file:
                        hasFile
                            ? filePath
                            : ""
                };

                const encodedData =
                    JSON.stringify(modalData)
                        .replace(/'/g, "&#39;");

                html += `

            <div
                class="assignment-card ${isExpired ? 'expired' : 'active-status'}"
                onclick='openAssignmentModal(${encodedData})'>

                <div class="card-header">

                    <h3 class="assignment-title">

                        ${escapeHtml(title)}

                    </h3>

                    <span
                        class="badge ${isExpired ? 'badge-expired' : 'badge-active'}">

                        ${isExpired ? 'منقضی شده' : 'فعال'}

                    </span>

                </div>

                <div class="card-body">

                    <div class="info-row">

                        <i class="fa-solid fa-chalkboard"></i>

                        <span>
                            کلاس:
                            ${escapeHtml(className)}
                        </span>

                    </div>

                    <div class="info-row">

                        <i class="fa-solid fa-chalkboard-user"></i>

                        <span>
                            استاد:
                            ${escapeHtml(teacherName)}
                        </span>

                    </div>

                    <div class="info-row">

                        <i class="fa-regular fa-calendar-days"></i>

                        <span>
                            مهلت:
                            ${escapeHtml(
                    expDate !== ""
                        ? expDate
                        : "نامشخص"
                )}
                        </span>

                    </div>

                </div>

                <div class="card-footer">

                    ${hasFile
                        ? `
                            <a
                                href="${escapeHtml(filePath)}"
                                class="download-link"
                                download
                                onclick="event.stopPropagation();">

                                <i class="fa-solid fa-download"></i>

                                دانلود فایل

                            </a>
                        `
                        : `
                            <span class="no-file-text">
                                این تمرین فایل پیوست ندارد.
                            </span>
                        `
                    }

                </div>

            </div>
        `;
            });

            html += "</div>";

            assignmentsContainer.innerHTML =
                html;
        }

        async function searchAssignments() {

            const search =
                assignmentSearch.value.trim();

            const showAll =
                showAllAssignments.checked;

            if (!showAll && search === "") {

                assignmentsContainer.innerHTML = `
            <div class="empty-state">
                <i class="fa-regular fa-folder-open"></i>
                <p>
                    برای نمایش تمرین‌ها عبارت موردنظر را جستجو کنید
                    یا گزینه «نمایش همه» را فعال کنید.
                </p>
            </div>
        `;

                searchStatus.textContent = "";

                return;
            }

            assignmentsContainer.innerHTML = `
        <div class="loading-assignments">

            <i class="fa-solid fa-spinner fa-spin"></i>

            <p>
                در حال جستجو...
            </p>

        </div>
    `;

            try {

                const url =
                    "?action=search_assignments" +
                    "&search=" +
                    encodeURIComponent(search) +
                    "&show_all=" +
                    (
                        showAll
                            ? "1"
                            : "0"
                    );

                const response =
                    await fetch(
                        url,
                        {
                            method: "GET",
                            headers: {
                                "X-Requested-With":
                                    "XMLHttpRequest"
                            }
                        }
                    );

                const data =
                    await response.json();

                if (!data.success) {

                    throw new Error(
                        data.message ||
                        "خطا در جستجو"
                    );
                }

                renderAssignments(
                    data.assignments
                );

                searchStatus.textContent =
                    `${data.assignments.length} نتیجه پیدا شد.`;

            } catch (error) {

                console.error(error);

                assignmentsContainer.innerHTML = `
            <div class="empty-state">

                <i class="fa-solid fa-triangle-exclamation"></i>

                <p>
                    خطا در دریافت اطلاعات.
                </p>

            </div>
        `;

                searchStatus.textContent = "";
            }
        }

        assignmentSearch.addEventListener(
            "input",
            function () {

                clearTimeout(searchTimer);

                if (showAllAssignments.checked) {
                    return;
                }

                searchTimer =
                    setTimeout(
                        function () {
                            searchAssignments();
                        },
                        300
                    );
            }
        );

        showAllAssignments.addEventListener(
            "change",
            function () {

                if (this.checked) {

                    searchAssignments();

                } else {

                    assignmentSearch.value = "";

                    assignmentsContainer.innerHTML = `
                <div class="empty-state">

                    <i class="fa-regular fa-folder-open"></i>

                    <p>
                        برای نمایش تمرین‌ها عبارت موردنظر را جستجو کنید
                        یا گزینه «نمایش همه» را فعال کنید.
                    </p>

                </div>
            `;

                    searchStatus.textContent = "";
                }
            }
        );

        function openAssignmentModal(data) {

            document.getElementById(
                'modalTitle'
            ).textContent =
                data.title;

            document.getElementById(
                'modalClass'
            ).textContent =
                data.class;

            document.getElementById(
                'modalTeacher'
            ).textContent =
                data.teacher;

            document.getElementById(
                'modalExpiration'
            ).textContent =
                data.expiration;

            const statusBadge =
                document.getElementById(
                    'modalStatusBadge'
                );

            statusBadge.textContent =
                data.status;

            if (
                data.status ===
                'منقضی شده'
            ) {

                statusBadge.className =
                    'badge badge-expired';

            } else {

                statusBadge.className =
                    'badge badge-active';
            }

            const descContainer =
                document.getElementById(
                    'modalDescription'
                );

            if (
                data.description &&
                data.description.trim() !== ""
            ) {

                descContainer.textContent =
                    data.description;

            } else {

                descContainer.textContent =
                    'توضیحاتی برای این تمرین درج نشده است.';
            }

            const fileSection =
                document.getElementById(
                    'modalFileSection'
                );

            const downloadLink =
                document.getElementById(
                    'modalDownloadLink'
                );

            if (
                data.file &&
                data.file.trim() !== ""
            ) {

                downloadLink.href =
                    data.file;

                fileSection.style.display =
                    'block';

            } else {

                fileSection.style.display =
                    'none';
            }

            document.getElementById(
                'assignmentModal'
            ).classList.add('show');

            document.body.style.overflow =
                'hidden';
        }

        function closeModalDirect() {

            document.getElementById(
                'assignmentModal'
            ).classList.remove('show');

            document.body.style.overflow =
                'auto';
        }

        function closeAssignmentModal(event) {

            if (
                event.target.id ===
                'assignmentModal'
            ) {
                closeModalDirect();
            }
        }

        document.addEventListener(
            'keydown',
            function (e) {

                if (e.key === 'Escape') {
                    closeModalDirect();
                }

            }
        );

    </script>

</body>

</html>