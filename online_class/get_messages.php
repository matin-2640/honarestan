<?php
session_start();
require_once "../connect.php";

header("Content-Type: application/json; charset=utf-8");

if (!isset($_SESSION["ID"], $_SESSION["type"])) {
    echo json_encode([
        "success" => false,
        "message" => "دسترسی غیرمجاز"
    ]);
    exit;
}

$course_id = isset($_GET["course_id"]) ? (int)$_GET["course_id"] : 0;
$after_id = isset($_GET["after_id"]) ? (int)$_GET["after_id"] : 0;

if ($course_id <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "درس نامعتبر است"
    ]);
    exit;
}

$type = (int)$_SESSION["type"];
$user_id = (int)$_SESSION["ID"];

$stmt = $connect->prepare("
    SELECT Co_ID, Co_classID, Co_teacherID
    FROM courses
    WHERE Co_ID = ?
    LIMIT 1
");
$stmt->execute([$course_id]);
$course = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$course) {
    echo json_encode([
        "success" => false,
        "message" => "درس پیدا نشد"
    ]);
    exit;
}

if ($type === 0) {

    $stmt = $connect->prepare("
        SELECT Stu_ID
        FROM students
        WHERE Stu_ID = ? AND Stu_classID = ?
        LIMIT 1
    ");

    $stmt->execute([
        $user_id,
        $course["Co_classID"]
    ]);

    if (!$stmt->fetch()) {
        echo json_encode([
            "success" => false,
            "message" => "دسترسی غیرمجاز"
        ]);
        exit;
    }

} elseif ($type === 1) {

    if ((int)$course["Co_teacherID"] !== $user_id) {
        echo json_encode([
            "success" => false,
            "message" => "دسترسی غیرمجاز"
        ]);
        exit;
    }
}

$sql = "
SELECT
    m.id,
    m.course_id,
    m.sender_type,
    m.sender_id,
    m.message,
    m.reply_to_id,
    m.created_at
FROM messages m
WHERE m.course_id = ?
AND m.id > ?
ORDER BY m.id ASC
LIMIT 100
";

$stmt = $connect->prepare($sql);
$stmt->execute([$course_id,$after_id]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

$result = [];

foreach ($messages as $m) {

    if ($m["sender_type"] === "student") {

        $stmtName = $connect->prepare("
            SELECT Stu_fullName
            FROM students
            WHERE Stu_ID = ?
            LIMIT 1
        ");

        $stmtName->execute([$m["sender_id"]]);
        $row = $stmtName->fetch(PDO::FETCH_ASSOC);

        $sender_name = $row["Stu_fullName"] ?? "دانش آموز";

    } else {

        $stmtName = $connect->prepare("
            SELECT T_fullName
            FROM teachers
            WHERE T_ID = ?
            LIMIT 1
        ");

        $stmtName->execute([$m["sender_id"]]);
        $row = $stmtName->fetch(PDO::FETCH_ASSOC);

        $sender_name = $row["T_fullName"] ?? "معلم";
    }

    $audio = null;
    $file = null;
    $content_type = "text";
    $preview = trim($m["message"]);

    if ($m["message"] === "[audio]") {

        $stmtAudio = $connect->prepare("
            SELECT audio_path,audio_name,duration
            FROM message_audios
            WHERE message_id = ?
            ORDER BY id DESC
            LIMIT 1
        ");

        $stmtAudio->execute([$m["id"]]);
        $audio = $stmtAudio->fetch(PDO::FETCH_ASSOC);

        $content_type = "audio";
        $preview = "🎤 پیام صوتی";

    } elseif ($m["message"] === "[file]") {

        $stmtFile = $connect->prepare("
            SELECT file_path,file_name,file_size,mime_type
            FROM message_files
            WHERE message_id = ?
            ORDER BY id DESC
            LIMIT 1
        ");

        $stmtFile->execute([$m["id"]]);
        $file = $stmtFile->fetch(PDO::FETCH_ASSOC);

        $content_type = "file";
        $preview = "📎 ".$file["file_name"];
    }

    $reply = null;

    if (!empty($m["reply_to_id"])) {

        $stmtReply = $connect->prepare("
            SELECT
                m.id,
                m.sender_type,
                m.sender_id,
                m.message
            FROM messages m
            WHERE m.id = ?
            LIMIT 1
        ");

        $stmtReply->execute([$m["reply_to_id"]]);
        $r = $stmtReply->fetch(PDO::FETCH_ASSOC);

        if ($r) {

            if ($r["sender_type"] === "student") {

                $stmtRN = $connect->prepare("
                    SELECT Stu_fullName
                    FROM students
                    WHERE Stu_ID = ?
                    LIMIT 1
                ");

                $stmtRN->execute([$r["sender_id"]]);
                $rn = $stmtRN->fetch(PDO::FETCH_ASSOC);

                $reply_name = $rn["Stu_fullName"] ?? "دانش آموز";

            } else {

                $stmtRN = $connect->prepare("
                    SELECT T_fullName
                    FROM teachers
                    WHERE T_ID = ?
                    LIMIT 1
                ");

                $stmtRN->execute([$r["sender_id"]]);
                $rn = $stmtRN->fetch(PDO::FETCH_ASSOC);

                $reply_name = $rn["T_fullName"] ?? "معلم";
            }

            $reply_preview = trim($r["message"]);

            if ($reply_preview === "[audio]") {
                $reply_preview = "🎤 پیام صوتی";
            }

            if ($reply_preview === "[file]") {
                $reply_preview = "📎 فایل";
            }

            $reply = [
                "id" => $r["id"],
                "sender_name" => $reply_name,
                "preview" => $reply_preview
            ];
        }
    }

    $result[] = [
        "id" => (int)$m["id"],
        "sender_type" => $m["sender_type"],
        "sender_id" => (int)$m["sender_id"],
        "sender_name" => $sender_name,
        "message" => $m["message"],
        "content_type" => $content_type,
        "audio_path" => $audio["audio_path"] ?? null,
        "audio_name" => $audio["audio_name"] ?? null,
        "duration" => $audio["duration"] ?? 0,
        "file_path" => $file["file_path"] ?? null,
        "file_name" => $file["file_name"] ?? null,
        "file_size" => $file["file_size"] ?? 0,
        "reply" => $reply,
        "preview" => $preview,
        "time" => date("H:i",strtotime($m["created_at"]))
    ];
}

echo json_encode([
    "success" => true,
    "messages" => $result
], JSON_UNESCAPED_UNICODE);