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

if (isset($_GET['action']) && $_GET['action'] === 'search_notes') {
    header('Content-Type: application/json; charset=utf-8');

    try {
        $search = trim($_GET['search'] ?? '');
        $showAll = isset($_GET['show_all']) && $_GET['show_all'] === '1';

        if ($class_id <= 0) {
            echo json_encode([
                'success' => true,
                'notes' => []
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $sql = "
            SELECT
                n.id,
                n.title,
                n.file_path,
                n.class_id,
                n.teacher_id,
                t.T_fullName,
                c.C_grade,
                c.C_major
            FROM notes n
            LEFT JOIN teachers t
                ON n.teacher_id = t.T_ID
            LEFT JOIN classes c
                ON n.class_id = c.C_ID
            WHERE n.class_id = :class_id
        ";

        $params = [
            ':class_id' => $class_id
        ];

        if (!$showAll && $search !== '') {
            $sql .= "
                AND (
                    n.title LIKE :search
                    OR t.T_fullName LIKE :search
                )
            ";

            $params[':search'] = '%' . $search . '%';
        }

        $sql .= " ORDER BY n.id DESC";

        $stmt = $connect->prepare($sql);
        $stmt->execute($params);

        $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'notes' => $notes
        ], JSON_UNESCAPED_UNICODE);

    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'خطا در دریافت جزوه‌ها.'
        ], JSON_UNESCAPED_UNICODE);
    }

    exit;
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>جزوه‌های درسی</title>

    <link rel="icon" href="../images/icons/rahdanesh.png">

    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css"
        rel="stylesheet">

    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="stylesheet" href="../styles/view_note.css">

    <script>
        (function () {
            const savedTheme = localStorage.getItem("theme") || "light";
            document.documentElement.setAttribute("data-theme", savedTheme);
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

        #noteSearch {
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

        #noteSearch::placeholder {
            color: #9ca3af;
        }

        #noteSearch:focus {
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

        #showAllNotes {
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

        .loading-notes {
            width: 100%;
            text-align: center;
            padding: 40px 10px;
            color: #64748b;
        }

        .loading-notes i {
            font-size: 28px;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>

<div class="page-container">

    <header class="page-header">
        <div class="header-title-wrapper">
            <h1>
                <i class="fa-solid fa-file-lines"></i>
                جزوه‌های کلاس من
            </h1>

            <p>
                فایل‌ها و مستندات آموزشی بارگذاری‌شده
            </p>
        </div>

    </header>

    <main class="content-area">

        <div class="search-area">

            <div class="search-wrapper">
                <i class="fa-solid fa-magnifying-glass"></i>

                <input
                    type="text"
                    id="noteSearch"
                    placeholder="جستجو بر اساس عنوان جزوه یا نام استاد..."
                    autocomplete="off">
            </div>

            <label class="show-all-wrapper">
                <input
                    type="checkbox"
                    id="showAllNotes">

                <span>نمایش همه</span>
            </label>

            <div id="searchStatus" class="search-status"></div>

        </div>

        <div id="notesContainer">
            <div class="empty-state">
                <i class="fa-regular fa-folder-open"></i>
                <p>برای نمایش جزوه‌ها عبارت موردنظر را جستجو کنید.</p>
            </div>
        </div>

    </main>
</div>

<style>
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

<a href="../admin_panel.php"
   id="smsParentBtn"
   class="btn-view-link">
    بازگشت به پنل مدیریت
</a>

<script src="../js/theme.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {

    const searchInput = document.getElementById("noteSearch");
    const showAllCheckbox = document.getElementById("showAllNotes");
    const notesContainer = document.getElementById("notesContainer");
    const searchStatus = document.getElementById("searchStatus");

    let searchTimer = null;

    function escapeHtml(value) {
        const div = document.createElement("div");
        div.textContent = value ?? "";
        return div.innerHTML;
    }

    function renderNotes(notes) {

        if (!notes || notes.length === 0) {

            notesContainer.innerHTML = `
                <div class="empty-state">
                    <i class="fa-regular fa-folder-open"></i>
                    <p>هیچ جزوه‌ای پیدا نشد.</p>
                </div>
            `;

            return;
        }

        let html = '<div class="notes-grid">';

        notes.forEach(function (item) {

            const title =
                item.title?.trim() || "بدون عنوان";

            const teacherName =
                item.T_fullName?.trim() || "استاد مربوطه";

            let className =
                ((item.C_grade || "") + " " + (item.C_major || "")).trim();

            if (!className) {
                className = "کلاس شما";
            }

            const filePath =
                item.file_path?.trim() || "";

            const hasFile =
                filePath !== "" &&
                filePath.toLowerCase() !== "none";

            html += `
                <div class="note-card">

                    <div class="card-header">
                        <h3 class="note-title">
                            <i class="fa-regular fa-file-pdf"></i>
                            ${escapeHtml(title)}
                        </h3>
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

                    </div>

                    <div class="card-footer">

                        ${
                            hasFile
                            ? `
                                <a
                                    href="${escapeHtml(filePath)}"
                                    class="download-link"
                                    download>
                                    <i class="fa-solid fa-download"></i>
                                    دانلود جزوه
                                </a>
                            `
                            : `
                                <span class="no-file-text">
                                    این جزوه فایل پیوست ندارد.
                                </span>
                            `
                        }

                    </div>

                </div>
            `;
        });

        html += "</div>";

        notesContainer.innerHTML = html;
    }

    async function searchNotes() {

        const search =
            searchInput.value.trim();

        const showAll =
            showAllCheckbox.checked;

        if (!showAll && search === "") {

            notesContainer.innerHTML = `
                <div class="empty-state">
                    <i class="fa-regular fa-folder-open"></i>
                    <p>
                        برای نمایش جزوه‌ها عبارت موردنظر را جستجو کنید
                        یا گزینه «نمایش همه» را فعال کنید.
                    </p>
                </div>
            `;

            searchStatus.textContent = "";
            return;
        }

        notesContainer.innerHTML = `
            <div class="loading-notes">
                <i class="fa-solid fa-spinner fa-spin"></i>
                <p>در حال جستجو...</p>
            </div>
        `;

        try {

            const url =
                "?action=search_notes" +
                "&search=" +
                encodeURIComponent(search) +
                "&show_all=" +
                (showAll ? "1" : "0");

            const response =
                await fetch(url, {
                    method: "GET",
                    headers: {
                        "X-Requested-With": "XMLHttpRequest"
                    }
                });

            const data =
                await response.json();

            if (!data.success) {
                throw new Error(
                    data.message ||
                    "خطا در جستجو"
                );
            }

            renderNotes(data.notes);

            if (showAll) {
                searchStatus.textContent =
                    `${data.notes.length} جزوه نمایش داده شد.`;
            } else {
                searchStatus.textContent =
                    `${data.notes.length} نتیجه پیدا شد.`;
            }

        } catch (error) {

            console.error(error);

            notesContainer.innerHTML = `
                <div class="empty-state">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <p>خطا در دریافت اطلاعات.</p>
                </div>
            `;

            searchStatus.textContent = "";
        }
    }

    searchInput.addEventListener("input", function () {

        clearTimeout(searchTimer);

        if (showAllCheckbox.checked) {
            return;
        }

        searchTimer = setTimeout(function () {
            searchNotes();
        }, 300);
    });

    showAllCheckbox.addEventListener("change", function () {

        if (this.checked) {
            searchNotes();
        } else {

            searchInput.value = "";

            notesContainer.innerHTML = `
                <div class="empty-state">
                    <i class="fa-regular fa-folder-open"></i>
                    <p>
                        برای نمایش جزوه‌ها عبارت موردنظر را جستجو کنید
                        یا گزینه «نمایش همه» را فعال کنید.
                    </p>
                </div>
            `;

            searchStatus.textContent = "";
        }
    });

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

});
</script>

</body>
</html>