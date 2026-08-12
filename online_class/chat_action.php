<?php
session_start();

require_once "../connect.php";

header("Content-Type: application/json; charset=utf-8");

function response($success, $message = "", $data = [])
{
    echo json_encode(
        array_merge(
            [
                "success" => $success,
                "message" => $message
            ],
            $data
        ),
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

if (!isset($_SESSION["ID"], $_SESSION["type"])) {
    response(false, "دسترسی غیرمجاز");
}

$user_id = (int)$_SESSION["ID"];
$user_type = (int)$_SESSION["type"];

if (!in_array($user_type, [0, 1])) {
    response(false, "دسترسی غیرمجاز");
}

$action = $_POST["action"] ?? "";
$course_id = (int)($_POST["course_id"] ?? 0);

if ($course_id <= 0) {
    response(false, "درس نامعتبر است");
}

$stmt = $connect->prepare("
    SELECT Co_ID, Co_classID, Co_teacherID
    FROM courses
    WHERE Co_ID = ?
    LIMIT 1
");

$stmt->execute([$course_id]);

$course = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$course) {
    response(false, "درس پیدا نشد");
}

if ($user_type === 0) {

    $stmt = $connect->prepare("
        SELECT Stu_ID
        FROM students
        WHERE Stu_ID = ?
        AND Stu_classID = ?
        LIMIT 1
    ");

    $stmt->execute([
        $user_id,
        $course["Co_classID"]
    ]);

    if (!$stmt->fetch()) {
        response(false, "شما به این درس دسترسی ندارید");
    }

    $sender_type = "student";

} else {

    if ((int)$course["Co_teacherID"] !== $user_id) {
        response(false, "شما به این درس دسترسی ندارید");
    }

    $sender_type = "teacher";
}

function getSenderName($connect, $type, $id)
{
    if ($type === "student") {

        $stmt = $connect->prepare("
            SELECT Stu_fullName
            FROM students
            WHERE Stu_ID = ?
            LIMIT 1
        ");

    } else {

        $stmt = $connect->prepare("
            SELECT T_fullName
            FROM teachers
            WHERE T_ID = ?
            LIMIT 1
        ");
    }

    $stmt->execute([$id]);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row["Stu_fullName"]
        ?? $row["T_fullName"]
        ?? "کاربر";
}

function buildMessage($connect, $id)
{
    $stmt = $connect->prepare("
        SELECT *
        FROM messages
        WHERE id = ?
        LIMIT 1
    ");

    $stmt->execute([$id]);

    $m = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$m) {
        return null;
    }

    $sender_name =
        getSenderName(
            $connect,
            $m["sender_type"],
            $m["sender_id"]
        );

    $content_type = "text";

    $audio = null;
    $file = null;

    $preview = trim($m["message"]);

    if ($m["message"] === "[audio]") {

        $stmt = $connect->prepare("
            SELECT *
            FROM message_audios
            WHERE message_id = ?
            ORDER BY id DESC
            LIMIT 1
        ");

        $stmt->execute([$id]);

        $audio = $stmt->fetch(PDO::FETCH_ASSOC);

        $content_type = "audio";

        $preview = "🎤 پیام صوتی";

    } elseif ($m["message"] === "[file]") {

        $stmt = $connect->prepare("
            SELECT *
            FROM message_files
            WHERE message_id = ?
            ORDER BY id DESC
            LIMIT 1
        ");

        $stmt->execute([$id]);

        $file = $stmt->fetch(PDO::FETCH_ASSOC);

        $content_type = "file";

        $preview =
            "📎 " .
            ($file["file_name"] ?? "فایل");
    }

    $reply = null;

    if (!empty($m["reply_to_id"])) {

        $stmt = $connect->prepare("
            SELECT *
            FROM messages
            WHERE id = ?
            LIMIT 1
        ");

        $stmt->execute([
            $m["reply_to_id"]
        ]);

        $r = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($r) {

            $replyPreview =
                trim($r["message"]);

            if ($replyPreview === "[audio]") {
                $replyPreview = "🎤 پیام صوتی";
            }

            if ($replyPreview === "[file]") {
                $replyPreview = "📎 فایل";
            }

            $reply = [
                "id" => (int)$r["id"],
                "sender_name" =>
                    getSenderName(
                        $connect,
                        $r["sender_type"],
                        $r["sender_id"]
                    ),
                "preview" => $replyPreview
            ];
        }
    }

    return [
        "id" => (int)$m["id"],
        "sender_type" => $m["sender_type"],
        "sender_id" => (int)$m["sender_id"],
        "sender_name" => $sender_name,
        "message" => $m["message"],
        "content_type" => $content_type,

        "audio_path" =>
            $audio["audio_path"] ?? null,

        "audio_name" =>
            $audio["audio_name"] ?? null,

        "duration" =>
            $audio["duration"] ?? 0,

        "file_path" =>
            $file["file_path"] ?? null,

        "file_name" =>
            $file["file_name"] ?? null,

        "file_size" =>
            $file["file_size"] ?? 0,

        "reply" => $reply,

        "preview" => $preview,

        "time" =>
            date(
                "H:i",
                strtotime($m["created_at"])
            )
    ];
}

if ($action === "send_message") {

    $message =
        trim($_POST["message"] ?? "");

    $reply_to_id =
        (int)($_POST["reply_to_id"] ?? 0);

    if ($message === "") {
        response(false, "پیام خالی است");
    }

    if (mb_strlen($message) > 5000) {
        response(false, "پیام بیش از حد طولانی است");
    }

    if ($reply_to_id > 0) {

        $stmt = $connect->prepare("
            SELECT id
            FROM messages
            WHERE id = ?
            AND course_id = ?
            LIMIT 1
        ");

        $stmt->execute([
            $reply_to_id,
            $course_id
        ]);

        if (!$stmt->fetch()) {
            $reply_to_id = null;
        }

    } else {
        $reply_to_id = null;
    }

    $stmt = $connect->prepare("
        INSERT INTO messages
        (
            course_id,
            sender_type,
            sender_id,
            reply_to_id,
            message
        )
        VALUES (?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $course_id,
        $sender_type,
        $user_id,
        $reply_to_id,
        $message
    ]);

    $id =
        $connect->lastInsertId();

    response(
        true,
        "پیام ارسال شد",
        [
            "message" =>
                buildMessage(
                    $connect,
                    $id
                )
        ]
    );
}

if ($action === "send_audio") {

    if (!isset($_FILES["audio"])) {
        response(
            false,
            "فایل صوتی دریافت نشد"
        );
    }

    $audio =
        $_FILES["audio"];

    if ($audio["error"] !== UPLOAD_ERR_OK) {

        response(
            false,
            "خطای PHP در دریافت ویس: " .
            $audio["error"]
        );
    }

    if ($audio["size"] > 20 * 1024 * 1024) {

        response(
            false,
            "حجم ویس بیش از 20 مگابایت است"
        );
    }

    $originalName =
        strtolower(
            basename($audio["name"])
        );

    $extension =
        strtolower(
            pathinfo(
                $originalName,
                PATHINFO_EXTENSION
            )
        );

    $allowedExtensions = [
        "webm",
        "ogg",
        "mp4",
        "m4a",
        "wav",
        "mp3"
    ];

    if (!in_array(
        $extension,
        $allowedExtensions
    )) {

        response(
            false,
            "پسوند فایل صوتی مجاز نیست"
        );
    }

    $uploadDir =
        __DIR__ . "/audio/";

    if (!is_dir($uploadDir)) {

        if (!mkdir(
            $uploadDir,
            0755,
            true
        )) {

            response(
                false,
                "پوشه audio ساخته نشد"
            );
        }
    }

    if (!is_writable($uploadDir)) {

        response(
            false,
            "پوشه audio قابل نوشتن نیست"
        );
    }

    $fileName =
        uniqid(
            "voice_",
            true
        ) . "." . $extension;

    $fullPath =
        $uploadDir . $fileName;

    if (!move_uploaded_file(
        $audio["tmp_name"],
        $fullPath
    )) {

        response(
            false,
            "ذخیره فایل صوتی انجام نشد"
        );
    }

    $relativePath =
        "audio/" . $fileName;

    $reply_to_id =
        (int)($_POST["reply_to_id"] ?? 0);

    if ($reply_to_id > 0) {

        $stmt = $connect->prepare("
            SELECT id
            FROM messages
            WHERE id = ?
            AND course_id = ?
            LIMIT 1
        ");

        $stmt->execute([
            $reply_to_id,
            $course_id
        ]);

        if (!$stmt->fetch()) {
            $reply_to_id = null;
        }

    } else {
        $reply_to_id = null;
    }

    try {

        $connect->beginTransaction();

        $stmt = $connect->prepare("
            INSERT INTO messages
            (
                course_id,
                sender_type,
                sender_id,
                reply_to_id,
                message
            )
            VALUES (?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $course_id,
            $sender_type,
            $user_id,
            $reply_to_id,
            "[audio]"
        ]);

        $message_id =
            $connect->lastInsertId();

        $stmt = $connect->prepare("
            INSERT INTO message_audios
            (
                message_id,
                course_id,
                sender_type,
                sender_id,
                audio_path,
                audio_name,
                duration
            )
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $message_id,
            $course_id,
            $sender_type,
            $user_id,
            $relativePath,
            $fileName,
            0
        ]);

        $connect->commit();

        response(
            true,
            "ویس ارسال شد",
            [
                "message" =>
                    buildMessage(
                        $connect,
                        $message_id
                    )
            ]
        );

    } catch (Throwable $e) {

        if ($connect->inTransaction()) {
            $connect->rollBack();
        }

        if (file_exists($fullPath)) {
            unlink($fullPath);
        }

        response(
            false,
            "خطا در ذخیره ویس: " .
            $e->getMessage()
        );
    }
}

if ($action === "send_file") {

    if (!isset($_FILES["file"])) {

        response(
            false,
            "فایل دریافت نشد"
        );
    }

    $file =
        $_FILES["file"];

    if ($file["error"] !== UPLOAD_ERR_OK) {

        response(
            false,
            "خطای PHP در آپلود فایل: " .
            $file["error"]
        );
    }

    if ($file["size"] <= 0) {

        response(
            false,
            "فایل خالی است"
        );
    }

    if ($file["size"] > 100 * 1024 * 1024) {

        response(
            false,
            "حداکثر حجم فایل 100 مگابایت است"
        );
    }

    $uploadDir =
        __DIR__ . "/files/";

    if (!is_dir($uploadDir)) {

        if (!mkdir(
            $uploadDir,
            0755,
            true
        )) {

            response(
                false,
                "پوشه files ساخته نشد"
            );
        }
    }

    if (!is_writable($uploadDir)) {

        response(
            false,
            "پوشه files قابل نوشتن نیست"
        );
    }

    $originalName =
        basename($file["name"]);

    $extension =
        strtolower(
            pathinfo(
                $originalName,
                PATHINFO_EXTENSION
            )
        );

    $safeName =
        uniqid(
            "file_",
            true
        );

    if ($extension !== "") {
        $safeName .= "." . $extension;
    }

    $fullPath =
        $uploadDir . $safeName;

    if (!move_uploaded_file(
        $file["tmp_name"],
        $fullPath
    )) {

        response(
            false,
            "PHP نتوانست فایل را در پوشه files ذخیره کند."
        );
    }

    $relativePath =
        "files/" . $safeName;

    $mime = null;

    if (function_exists("finfo_open")) {

        $finfo =
            finfo_open(
                FILEINFO_MIME_TYPE
            );

        if ($finfo) {

            $mime =
                finfo_file(
                    $finfo,
                    $fullPath
                );

            finfo_close($finfo);
        }
    }

    if (!$mime) {
        $mime = $file["type"] ?? "application/octet-stream";
    }

    $reply_to_id =
        (int)($_POST["reply_to_id"] ?? 0);

    if ($reply_to_id > 0) {

        $stmt = $connect->prepare("
            SELECT id
            FROM messages
            WHERE id = ?
            AND course_id = ?
            LIMIT 1
        ");

        $stmt->execute([
            $reply_to_id,
            $course_id
        ]);

        if (!$stmt->fetch()) {
            $reply_to_id = null;
        }

    } else {
        $reply_to_id = null;
    }

    try {

        $connect->beginTransaction();

        $stmt = $connect->prepare("
            INSERT INTO messages
            (
                course_id,
                sender_type,
                sender_id,
                reply_to_id,
                message
            )
            VALUES (?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $course_id,
            $sender_type,
            $user_id,
            $reply_to_id,
            "[file]"
        ]);

        $message_id =
            $connect->lastInsertId();

        $stmt = $connect->prepare("
            INSERT INTO message_files
            (
                message_id,
                course_id,
                sender_type,
                sender_id,
                file_path,
                file_name,
                file_size,
                mime_type
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $message_id,
            $course_id,
            $sender_type,
            $user_id,
            $relativePath,
            $originalName,
            $file["size"],
            $mime
        ]);

        $connect->commit();

        response(
            true,
            "فایل ارسال شد",
            [
                "message" =>
                    buildMessage(
                        $connect,
                        $message_id
                    )
            ]
        );

    } catch (Throwable $e) {

        if ($connect->inTransaction()) {
            $connect->rollBack();
        }

        if (file_exists($fullPath)) {
            unlink($fullPath);
        }

        response(
            false,
            "خطا در ذخیره فایل: " .
            $e->getMessage()
        );
    }
}

if ($action === "edit_message") {

    $id =
        (int)($_POST["id"] ?? 0);

    $message =
        trim($_POST["message"] ?? "");

    if (
        $id <= 0 ||
        $message === ""
    ) {
        response(
            false,
            "اطلاعات نامعتبر است"
        );
    }

    $stmt = $connect->prepare("
        SELECT sender_id, sender_type, message
        FROM messages
        WHERE id = ?
        AND course_id = ?
        LIMIT 1
    ");

    $stmt->execute([
        $id,
        $course_id
    ]);

    $old =
        $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$old) {
        response(
            false,
            "پیام پیدا نشد"
        );
    }

    if (
        (int)$old["sender_id"] !== $user_id ||
        $old["sender_type"] !== $sender_type ||
        $old["message"] === "[audio]" ||
        $old["message"] === "[file]"
    ) {

        response(
            false,
            "شما اجازه ویرایش این پیام را ندارید"
        );
    }

    $stmt = $connect->prepare("
        UPDATE messages
        SET message = ?
        WHERE id = ?
        AND course_id = ?
    ");

    $stmt->execute([
        $message,
        $id,
        $course_id
    ]);

    response(
        true,
        "پیام ویرایش شد"
    );
}

if ($action === "delete_message") {

    $id =
        (int)($_POST["id"] ?? 0);

    if ($id <= 0) {
        response(
            false,
            "پیام نامعتبر است"
        );
    }

    $stmt = $connect->prepare("
        SELECT *
        FROM messages
        WHERE id = ?
        AND course_id = ?
        LIMIT 1
    ");

    $stmt->execute([
        $id,
        $course_id
    ]);

    $msg =
        $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$msg) {
        response(
            false,
            "پیام پیدا نشد"
        );
    }

    $isOwner =
        (int)$msg["sender_id"] === $user_id &&
        $msg["sender_type"] === $sender_type;

    $isTeacher =
        $user_type === 1;

    if (!$isOwner && !$isTeacher) {
        response(
            false,
            "شما اجازه حذف این پیام را ندارید"
        );
    }

    try {

        $connect->beginTransaction();

        if ($msg["message"] === "[audio]") {

            $stmt = $connect->prepare("
                SELECT audio_path
                FROM message_audios
                WHERE message_id = ?
            ");

            $stmt->execute([$id]);

            $audios =
                $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($audios as $audio) {

                $path =
                    __DIR__ . "/" .
                    $audio["audio_path"];

                if (file_exists($path)) {
                    unlink($path);
                }
            }

            $stmt = $connect->prepare("
                DELETE FROM message_audios
                WHERE message_id = ?
            ");

            $stmt->execute([$id]);
        }

        if ($msg["message"] === "[file]") {

            $stmt = $connect->prepare("
                SELECT file_path
                FROM message_files
                WHERE message_id = ?
            ");

            $stmt->execute([$id]);

            $files =
                $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($files as $file) {

                $path =
                    __DIR__ . "/" .
                    $file["file_path"];

                if (file_exists($path)) {
                    unlink($path);
                }
            }

            $stmt = $connect->prepare("
                DELETE FROM message_files
                WHERE message_id = ?
            ");

            $stmt->execute([$id]);
        }

        $stmt = $connect->prepare("
            DELETE FROM messages
            WHERE id = ?
            AND course_id = ?
        ");

        $stmt->execute([
            $id,
            $course_id
        ]);

        $connect->commit();

        response(
            true,
            "پیام حذف شد"
        );

    } catch (Throwable $e) {

        if ($connect->inTransaction()) {
            $connect->rollBack();
        }

        response(
            false,
            "خطا در حذف پیام: " .
            $e->getMessage()
        );
    }
}

response(
    false,
    "عملیات نامعتبر است"
);