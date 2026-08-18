<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["type"]) || $_SESSION["type"] != 2) {
    header("Location: login.php");
    exit;
}

date_default_timezone_set("Asia/Tehran");

include("connect.php");

function h($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function gregorianToJalali($gy, $gm, $gd, $separator = "/")
{
    $g_d_m = array(0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334);

    $gy2 = ($gm > 2) ? ($gy + 1) : $gy;

    $days = 355666
        + (365 * $gy)
        + floor(($gy2 + 3) / 4)
        - floor(($gy2 + 99) / 100)
        + floor(($gy2 + 399) / 400)
        + $gd
        + $g_d_m[$gm - 1];

    $jy = -1595 + (33 * floor($days / 12053));
    $days %= 12053;

    $jy += 4 * floor($days / 1461);
    $days %= 1461;

    if ($days > 365) {
        $jy += floor(($days - 1) / 365);
        $days = ($days - 1) % 365;
    }

    if ($days < 186) {
        $jm = 1 + floor($days / 31);
        $jd = 1 + ($days % 31);
    } else {
        $jm = 7 + floor(($days - 186) / 30);
        $jd = 1 + (($days - 186) % 30);
    }

    return sprintf("%04d%s%02d%s%02d", $jy, $separator, $jm, $separator, $jd);
}

function jalaliDateTime($timestamp)
{
    return gregorianToJalali(
        (int) date("Y", $timestamp),
        (int) date("n", $timestamp),
        (int) date("j", $timestamp)
    ) . " " . date("H:i:s", $timestamp);
}

function durationText($seconds)
{
    $seconds = max(0, (int) $seconds);

    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $secs = $seconds % 60;

    if ($hours > 0) {
        return $hours . " ساعت و " . $minutes . " دقیقه";
    }

    if ($minutes > 0) {
        return $minutes . " دقیقه و " . $secs . " ثانیه";
    }

    return $secs . " ثانیه";
}

if (isset($_GET["ajax"]) && $_GET["ajax"] == "1") {
    $search = trim($_GET["search"] ?? "");
    $filter = $_GET["filter"] ?? "all";
    $today = isset($_GET["today"]) && $_GET["today"] == "1";

    $where = [];
    $params = [];

    if ($filter === "active") {
        $where[] = "vr.status = 'active'";
    } elseif ($filter === "ended") {
        $where[] = "vr.status = 'ended'";
    } elseif (ctype_digit((string) $filter)) {
        $where[] = "vr.course_id = ?";
        $params[] = (int) $filter;
    }

    if ($today) {
        $where[] = "DATE(vr.created_at) = CURDATE()";
    }

    if ($search !== "") {
        $where[] = "(
            c.Co_name LIKE ?
            OR t.T_fullName LIKE ?
            OR CAST(vr.id AS CHAR) LIKE ?
        )";

        $like = "%" . $search . "%";
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
    }

    $sql = "
        SELECT
            vr.id,
            vr.course_id,
            vr.teacher_id,
            vr.status,
            vr.created_at,
            vr.ended_at,
            c.Co_name,
            t.T_fullName
        FROM voice_rooms vr
        LEFT JOIN courses c ON c.Co_ID = vr.course_id
        LEFT JOIN teachers t ON t.T_ID = vr.teacher_id
    ";

    if (!empty($where)) {
        $sql .= " WHERE " . implode(" AND ", $where);
    }

    $sql .= " ORDER BY vr.created_at DESC";

    $stmt = $connect->prepare($sql);
    $stmt->execute($params);
    $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$rooms) {
        echo '
        <div class="empty-state">
            <div class="empty-icon">
                <svg viewBox="0 0 24 24" fill="none">
                    <path d="M9 9h6M9 13h4M5 4h14a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-7l-4 3v-3H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <strong>ویسکالی پیدا نشد</strong>
            <span>با فیلتر یا عبارت جستجوی دیگری امتحان کنید.</span>
        </div>';
        exit;
    }

    foreach ($rooms as $room) {
        $participantStmt = $connect->prepare("
            SELECT COUNT(*)
            FROM voice_participants
            WHERE room_id = ?
        ");
        $participantStmt->execute([$room["id"]]);
        $participantCount = (int) $participantStmt->fetchColumn();

        $activeParticipantStmt = $connect->prepare("
            SELECT COUNT(*)
            FROM voice_participants
            WHERE room_id = ?
            AND left_at IS NULL
        ");
        $activeParticipantStmt->execute([$room["id"]]);
        $activeParticipantCount = (int) $activeParticipantStmt->fetchColumn();

        $createdTimestamp = strtotime($room["created_at"]);
        $endedTimestamp = $room["ended_at"] ? strtotime($room["ended_at"]) : null;

        if ($endedTimestamp) {
            $roomDuration = $endedTimestamp - $createdTimestamp;
        } else {
            $roomDuration = time() - $createdTimestamp;
        }

        $statusClass = $room["status"] === "active" ? "active" : "ended";
        $statusText = $room["status"] === "active" ? "فعال" : "پایان یافته";

        $createdDate = gregorianToJalali(
            (int) date("Y", $createdTimestamp),
            (int) date("n", $createdTimestamp),
            (int) date("j", $createdTimestamp)
        );

        $createdTime = date("H:i:s", $createdTimestamp);

        $endedDate = $endedTimestamp
            ? gregorianToJalali(
                (int) date("Y", $endedTimestamp),
                (int) date("n", $endedTimestamp),
                (int) date("j", $endedTimestamp)
            )
            : "—";

        $endedTime = $endedTimestamp
            ? date("H:i:s", $endedTimestamp)
            : "—";

        echo '
        <div class="room-card">
            <div class="room-top">
                <div class="course-icon">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M4 6.5A2.5 2.5 0 0 1 6.5 4H20v14H6.5A2.5 2.5 0 0 0 4 20.5V6.5Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                        <path d="M4 6.5V20.5M8 8h8M8 11h8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                </div>

                <div class="room-title">
                    <h3>' . h($room["Co_name"] ?? "درس نامشخص") . '</h3>
                    <span>ویسکال #' . h($room["id"]) . '</span>
                </div>

                <span class="status ' . $statusClass . '">
                    <i></i>
                    ' . $statusText . '
                </span>
            </div>

            <div class="room-info">
                <div class="info-item">
                    <span class="info-label">مدرس</span>
                    <strong>' . h($room["T_fullName"] ?? "نامشخص") . '</strong>
                </div>

                <div class="info-item">
                    <span class="info-label">تاریخ ایجاد</span>
                    <strong>' . $createdDate . '</strong>
                </div>

                <div class="info-item">
                    <span class="info-label">ساعت شروع</span>
                    <strong>' . $createdTime . '</strong>
                </div>

                <div class="info-item">
                    <span class="info-label">مدت کلاس</span>
                    <strong>' . durationText($roomDuration) . '</strong>
                </div>
            </div>

            <div class="room-footer">
                <div class="participants-summary">
                    <div class="users-icon">
                        <svg viewBox="0 0 24 24" fill="none">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2M9.5 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM21 21v-2a4 4 0 0 0-3-3.87M16.5 3.13a4 4 0 0 1 0 7.75" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>

                    <div>
                        <strong>' . $participantCount . '</strong>
                        <span>نفر شرکت‌کننده</span>
                    </div>';

        if ($room["status"] === "active") {
            echo '
                    <div class="live-count">
                        <span></span>
                        ' . $activeParticipantCount . ' نفر آنلاین
                    </div>';
        }

        echo '
                </div>

                <button class="details-btn" onclick="openParticipants(' . (int) $room["id"] . ', \'' . h($room["Co_name"] ?? "درس نامشخص") . '\')">
                    مشاهده جزئیات
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>

            <div class="room-times">
                <span>
                    ایجاد:
                    ' . $createdDate . ' - ' . $createdTime . '
                </span>

                <span>
                    پایان:
                    ' . $endedDate . ' - ' . $endedTime . '
                </span>
            </div>
        </div>';
    }

    exit;
}

$coursesStmt = $connect->query("
    SELECT Co_ID, Co_name
    FROM courses
    ORDER BY Co_name ASC
");

$courses = $coursesStmt->fetchAll(PDO::FETCH_ASSOC);

$totalRooms = (int) $connect->query("
    SELECT COUNT(*) FROM voice_rooms
")->fetchColumn();

$activeRooms = (int) $connect->query("
    SELECT COUNT(*)
    FROM voice_rooms
    WHERE status = 'active'
")->fetchColumn();

$endedRooms = (int) $connect->query("
    SELECT COUNT(*)
    FROM voice_rooms
    WHERE status = 'ended'
")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>گزارش کلاس‌های مجازی</title>
    <link rel="stylesheet" href="styles/font.css">
    <link rel="stylesheet" href="styles/onlineClass_report.css">
    <link rel="icon" href="images/icons/rahdanesh.png" />

</head>

<body>

    <div class="page">

        <div class="header">
            <div class="header-title">
                <div class="header-icon">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v14H6.5A2.5 2.5 0 0 0 4 19.5v-14Z" stroke="currentColor"
                            stroke-width="1.8" stroke-linejoin="round" />
                        <path d="M4 5.5v14M8 7h8M8 10h8M8 13h5" stroke="currentColor" stroke-width="1.8"
                            stroke-linecap="round" />
                    </svg>
                </div>

                <div>
                    <h1>گزارش کلاس‌های مجازی</h1>
                    <p>مدیریت و مشاهده سابقه برگزاری ویسکال‌ها و حضور کاربران</p>
                </div>
            </div>

            <a href="admin_panel.php" class="back-btn">
                <svg viewBox="0 0 24 24" fill="none">
                    <path d="M19 12H5M12 19l-7-7 7-7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                        stroke-linejoin="round" />
                </svg>
                بازگشت به پنل مدیر
            </a>
        </div>

        <div class="stats">

            <div class="stat-card">
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v14H6.5A2.5 2.5 0 0 0 4 19.5v-14Z" stroke="currentColor"
                            stroke-width="1.8" />
                        <path d="M4 5.5v14M8 7h8M8 10h8" stroke="currentColor" stroke-width="1.8"
                            stroke-linecap="round" />
                    </svg>
                </div>

                <div>
                    <span class="stat-number"><?= $totalRooms ?></span>
                    <span class="stat-title">کل کلاس‌های مجازی</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24" fill="none">
                        <circle cx="12" cy="12" r="8.5" stroke="currentColor" stroke-width="1.8" />
                        <path d="M12 7v5l3 2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                    </svg>
                </div>

                <div>
                    <span class="stat-number"><?= $activeRooms ?></span>
                    <span class="stat-title">کلاس‌های فعال</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M5 12h14M12 5v14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                    </svg>
                </div>

                <div>
                    <span class="stat-number"><?= $endedRooms ?></span>
                    <span class="stat-title">کلاس‌های پایان‌یافته</span>
                </div>
            </div>

        </div>

        <div class="filters">

            <div class="search-box">
                <svg viewBox="0 0 24 24" fill="none">
                    <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="1.8" />
                    <path d="m16.5 16.5 4 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                </svg>

                <input type="text" id="searchInput" placeholder="جستجوی نام درس، مدرس یا شماره ویسکال..."
                    autocomplete="off">
            </div>

            <div class="select-box">
                <svg viewBox="0 0 24 24" fill="none">
                    <path d="m6 9 6 6 6-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                        stroke-linejoin="round" />
                </svg>

                <select id="filterSelect">
                    <option value="all">همه کلاس‌ها</option>
                    <option value="active">کلاس‌های فعال</option>
                    <option value="ended">کلاس‌های پایان‌یافته</option>

                    <?php foreach ($courses as $course): ?>
                        <option value="<?= (int) $course["Co_ID"] ?>">
                            <?= h($course["Co_name"]) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <label class="today-filter">
                <input type="checkbox" id="todayCheck">
                فقط نمایش کلاس‌های امروز
            </label>

        </div>

        <div id="roomsContainer" class="rooms"></div>

    </div>

    <div class="modal-overlay" id="modalOverlay">
        <div class="modal">

            <div class="modal-header">
                <div class="modal-title">
                    <h2 id="modalTitle">شرکت‌کنندگان</h2>
                    <span id="modalSubtitle"></span>
                </div>

                <button class="close-modal" onclick="closeModal()">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="m7 7 10 10M17 7 7 17" stroke="currentColor" stroke-width="1.8"
                            stroke-linecap="round" />
                    </svg>
                </button>
            </div>

            <div class="modal-body" id="modalBody">
                <div class="loading">در حال دریافت اطلاعات...</div>
            </div>

        </div>
    </div>

    <script>
        let searchTimer = null;

        function loadRooms() {
            const search = document.getElementById("searchInput").value.trim();
            const filter = document.getElementById("filterSelect").value;
            const today = document.getElementById("todayCheck").checked ? "1" : "0";

            const container = document.getElementById("roomsContainer");

            container.innerHTML = '<div class="loading">در حال دریافت گزارش...</div>';

            const params = new URLSearchParams({
                ajax: "1",
                search: search,
                filter: filter,
                today: today
            });

            fetch("voice_rooms.php?" + params.toString(), {
                method: "GET",
                headers: {
                    "X-Requested-With": "XMLHttpRequest"
                }
            })
                .then(response => response.text())
                .then(html => {
                    container.innerHTML = html;
                })
                .catch(() => {
                    container.innerHTML = `
            <div class="empty-state">
                <div class="empty-icon">!</div>
                <strong>خطا در دریافت اطلاعات</strong>
                <span>لطفاً دوباره تلاش کنید.</span>
            </div>
        `;
                });
        }

        document.getElementById("searchInput").addEventListener("input", function () {
            clearTimeout(searchTimer);

            searchTimer = setTimeout(() => {
                loadRooms();
            }, 300);
        });

        document.getElementById("filterSelect").addEventListener("change", loadRooms);
        document.getElementById("todayCheck").addEventListener("change", loadRooms);

        function openParticipants(roomId, courseName) {
            const overlay = document.getElementById("modalOverlay");
            const body = document.getElementById("modalBody");

            document.getElementById("modalTitle").textContent = courseName;
            document.getElementById("modalSubtitle").textContent = "جزئیات حضور کاربران در ویسکال #" + roomId;

            overlay.classList.add("show");

            body.innerHTML = '<div class="loading">در حال دریافت اطلاعات شرکت‌کنندگان...</div>';

            fetch("voice_participants.php?room_id=" + encodeURIComponent(roomId), {
                method: "GET",
                headers: {
                    "X-Requested-With": "XMLHttpRequest"
                }
            })
                .then(response => response.text())
                .then(html => {
                    body.innerHTML = html;
                })
                .catch(() => {
                    body.innerHTML = `
            <div class="empty-state">
                <strong>خطا در دریافت اطلاعات</strong>
                <span>لطفاً دوباره تلاش کنید.</span>
            </div>
        `;
                });
        }

        function closeModal() {
            document.getElementById("modalOverlay").classList.remove("show");
        }

        document.getElementById("modalOverlay").addEventListener("click", function (e) {
            if (e.target === this) {
                closeModal();
            }
        });

        document.addEventListener("keydown", function (e) {
            if (e.key === "Escape") {
                closeModal();
            }
        });

        loadRooms();
    </script>

</body>

</html>