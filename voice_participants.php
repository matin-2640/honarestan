<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["type"]) || $_SESSION["type"] != 2) {
    http_response_code(403);
    exit("دسترسی غیرمجاز");
}

date_default_timezone_set("Asia/Tehran");

include("connect.php");

function h($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
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
    $seconds = max(0, (int)$seconds);

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

$roomId = filter_input(INPUT_GET, "room_id", FILTER_VALIDATE_INT);

if (!$roomId) {
    echo '
    <div class="empty-state">
        <strong>ویسکال نامعتبر است</strong>
        <span>شناسه کلاس مجازی ارسال نشده است.</span>
    </div>';
    exit;
}

$roomStmt = $connect->prepare("
    SELECT
        vr.id,
        vr.status,
        vr.created_at,
        vr.ended_at,
        c.Co_name,
        t.T_fullName
    FROM voice_rooms vr
    LEFT JOIN courses c ON c.Co_ID = vr.course_id
    LEFT JOIN teachers t ON t.T_ID = vr.teacher_id
    WHERE vr.id = ?
    LIMIT 1
");

$roomStmt->execute([$roomId]);
$room = $roomStmt->fetch(PDO::FETCH_ASSOC);

if (!$room) {
    echo '
    <div class="empty-state">
        <strong>ویسکال پیدا نشد</strong>
        <span>اطلاعات این کلاس مجازی در دیتابیس وجود ندارد.</span>
    </div>';
    exit;
}

$stmt = $connect->prepare("
    SELECT
        vp.id,
        vp.user_type,
        vp.user_id,
        vp.mic_enabled,
        vp.joined_at,
        vp.left_at,
        vp.last_seen
    FROM voice_participants vp
    WHERE vp.room_id = ?
    ORDER BY vp.joined_at ASC
");

$stmt->execute([$roomId]);
$participants = $stmt->fetchAll(PDO::FETCH_ASSOC);

$roomCreated = strtotime($room["created_at"]);
$roomEnded = $room["ended_at"] ? strtotime($room["ended_at"]) : null;

$roomDuration = $roomEnded
    ? $roomEnded - $roomCreated
    : time() - $roomCreated;

$participantCount = count($participants);

$activeCount = 0;

foreach ($participants as $participant) {
    if ($participant["left_at"] === null) {
        $activeCount++;
    }
}
?>

<style>
.participant-summary {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
    margin-bottom: 18px;
}

.participant-stat {
    background: #f8fafc;
    border: 1px solid #e7ebf2;
    border-radius: 14px;
    padding: 14px;
}

.participant-stat span {
    display: block;
    color: #8993a5;
    font-size: 10px;
    margin-bottom: 7px;
}

.participant-stat strong {
    font-size: 15px;
}

.room-detail {
    background: #f8fafc;
    border: 1px solid #e7ebf2;
    border-radius: 15px;
    padding: 15px;
    margin-bottom: 18px;
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
}

.room-detail-item span {
    display: block;
    color: #8993a5;
    font-size: 10px;
    margin-bottom: 6px;
}

.room-detail-item strong {
    font-size: 12px;
}

.participants-table-wrap {
    overflow-x: auto;
    border: 1px solid #e7ebf2;
    border-radius: 16px;
}

.participants-table {
    width: 100%;
    min-width: 680px;
    border-collapse: collapse;
}

.participants-table th {
    background: #f8fafc;
    color: #7d8799;
    font-size: 10px;
    font-weight: normal;
    padding: 13px 12px;
    text-align: right;
    border-bottom: 1px solid #e7ebf2;
    white-space: nowrap;
}

.participants-table td {
    padding: 13px 12px;
    font-size: 11px;
    border-bottom: 1px solid #edf0f5;
    white-space: nowrap;
}

.participants-table tr:last-child td {
    border-bottom: 0;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 9px;
}

.user-avatar {
    width: 34px;
    height: 34px;
    border-radius: 10px;
    background: #eef3ff;
    color: #3569d4;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.user-avatar.teacher {
    background: #fff2e8;
    color: #df7628;
}

.user-avatar svg {
    width: 18px;
    height: 18px;
}

.user-name {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.user-name strong {
    font-size: 11px;
}

.user-name span {
    color: #9099aa;
    font-size: 9px;
}

.mic-state {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 6px 8px;
    border-radius: 20px;
    font-size: 9px;
}

.mic-on {
    background: #eafaf1;
    color: #15965a;
}

.mic-off {
    background: #f1f3f6;
    color: #737e91;
}

.mic-state svg {
    width: 13px;
    height: 13px;
}

.online-state {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: #15965a;
    font-size: 10px;
}

.online-state i {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #18b769;
    box-shadow: 0 0 0 3px rgba(24,183,105,.12);
}

.offline-state {
    color: #7f899b;
    font-size: 10px;
}

.no-participants {
    padding: 45px 20px;
    text-align: center;
    color: #7f899b;
}

.no-participants strong {
    display: block;
    color: #30394a;
    margin-bottom: 7px;
    font-size: 13px;
}

.no-participants span {
    font-size: 10px;
}

@media (max-width: 650px) {
    .participant-summary {
        grid-template-columns: 1fr;
    }

    .room-detail {
        grid-template-columns: 1fr 1fr;
    }
}
</style>

<div class="participant-summary">

    <div class="participant-stat">
        <span>کل شرکت‌کنندگان</span>
        <strong><?= $participantCount ?> نفر</strong>
    </div>

    <div class="participant-stat">
        <span>افراد آنلاین</span>
        <strong><?= $activeCount ?> نفر</strong>
    </div>

    <div class="participant-stat">
        <span>مدت ویسکال</span>
        <strong><?= durationText($roomDuration) ?></strong>
    </div>

</div>

<div class="room-detail">

    <div class="room-detail-item">
        <span>درس</span>
        <strong><?= h($room["Co_name"] ?? "نامشخص") ?></strong>
    </div>

    <div class="room-detail-item">
        <span>مدرس</span>
        <strong><?= h($room["T_fullName"] ?? "نامشخص") ?></strong>
    </div>

    <div class="room-detail-item">
        <span>شروع</span>
        <strong><?= jalaliDateTime($roomCreated) ?></strong>
    </div>

    <div class="room-detail-item">
        <span>پایان</span>
        <strong>
            <?= $roomEnded ? jalaliDateTime($roomEnded) : "در حال برگزاری" ?>
        </strong>
    </div>

</div>

<?php if (!$participants): ?>

    <div class="no-participants">
        <strong>هنوز کسی در این ویسکال ثبت نشده است</strong>
        <span>رکوردی در جدول voice_participants وجود ندارد.</span>
    </div>

<?php else: ?>

    <div class="participants-table-wrap">

        <table class="participants-table">

            <thead>
                <tr>
                    <th>کاربر</th>
                    <th>نوع</th>
                    <th>زمان ورود</th>
                    <th>زمان خروج</th>
                    <th>مدت حضور</th>
                    <th>میکروفون</th>
                    <th>وضعیت</th>
                </tr>
            </thead>

            <tbody>

            <?php foreach ($participants as $participant): ?>

                <?php
                $userName = "کاربر #" . $participant["user_id"];
                $userTypeText = $participant["user_type"] === "teacher" ? "مدرس" : "هنرجو";

                if ($participant["user_type"] === "teacher") {
                    $userStmt = $connect->prepare("
                        SELECT T_fullName
                        FROM teachers
                        WHERE T_ID = ?
                        LIMIT 1
                    ");
                } else {
                    $userStmt = $connect->prepare("
                        SELECT Stu_fullName
                        FROM students
                        WHERE Stu_ID = ?
                        LIMIT 1
                    ");
                }

                $userStmt->execute([(int)$participant["user_id"]]);
                $foundName = $userStmt->fetchColumn();

                if ($foundName) {
                    $userName = $foundName;
                }

                $joinedTimestamp = strtotime($participant["joined_at"]);
                $leftTimestamp = $participant["left_at"]
                    ? strtotime($participant["left_at"])
                    : null;

                if ($leftTimestamp) {
                    $presenceSeconds = $leftTimestamp - $joinedTimestamp;
                } else {
                    $presenceSeconds = time() - $joinedTimestamp;
                }

                $avatarClass = $participant["user_type"] === "teacher" ? "teacher" : "";
                ?>

                <tr>

                    <td>
                        <div class="user-info">

                            <div class="user-avatar <?= $avatarClass ?>">
                                <?php if ($participant["user_type"] === "teacher"): ?>
                                    <svg viewBox="0 0 24 24" fill="none">
                                        <path d="M15 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM16 3.1a4 4 0 0 1 0 7.8M17 15a4 4 0 0 1 4 4v2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                <?php else: ?>
                                    <svg viewBox="0 0 24 24" fill="none">
                                        <circle cx="12" cy="8" r="4" stroke="currentColor" stroke-width="1.7"/>
                                        <path d="M4 21a8 8 0 0 1 16 0" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                    </svg>
                                <?php endif; ?>
                            </div>

                            <div class="user-name">
                                <strong><?= h($userName) ?></strong>
                                <span>شناسه: <?= (int)$participant["user_id"] ?></span>
                            </div>

                        </div>
                    </td>

                    <td>
                        <?= $userTypeText ?>
                    </td>

                    <td>
                        <?= jalaliDateTime($joinedTimestamp) ?>
                    </td>

                    <td>
                        <?= $leftTimestamp ? jalaliDateTime($leftTimestamp) : "—" ?>
                    </td>

                    <td>
                        <?= durationText($presenceSeconds) ?>
                    </td>

                    <td>

                        <?php if ((int)$participant["mic_enabled"] === 1): ?>

                            <span class="mic-state mic-on">
                                <svg viewBox="0 0 24 24" fill="none">
                                    <rect x="8" y="3" width="8" height="12" rx="4" stroke="currentColor" stroke-width="1.7"/>
                                    <path d="M5 11a7 7 0 0 0 14 0M12 18v3M9 21h6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                </svg>
                                روشن
                            </span>

                        <?php else: ?>

                            <span class="mic-state mic-off">
                                <svg viewBox="0 0 24 24" fill="none">
                                    <path d="M8 3v9a4 4 0 0 0 7 2.65M16 9V3M5 11a7 7 0 0 0 11.5 5.4M12 18v3M9 21h6M3 3l18 18" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                خاموش
                            </span>

                        <?php endif; ?>

                    </td>

                    <td>

                        <?php if ($participant["left_at"] === null): ?>

                            <span class="online-state">
                                <i></i>
                                آنلاین
                            </span>

                        <?php else: ?>

                            <span class="offline-state">
                                خارج شده
                            </span>

                        <?php endif; ?>

                    </td>

                </tr>

            <?php endforeach; ?>

            </tbody>

        </table>

    </div>

<?php endif; ?>