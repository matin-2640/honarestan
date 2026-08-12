<?php
session_start();
require_once '../connect.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['ID'], $_SESSION['type'])) {
    echo json_encode([
        'success' => false,
        'message' => 'دسترسی غیرمجاز'
    ]);
    exit;
}

$userId = (int)$_SESSION['ID'];
$userType = (int)$_SESSION['type'];

function response($data)
{
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function canAccess($connect, $courseId, $userId, $type)
{
    if (in_array($type, [2, 3, 4], true)) {
        return true;
    }

    if ($type === 1) {

        $stmt = $connect->prepare("
            SELECT COUNT(*)
            FROM courses
            WHERE Co_ID = ?
            AND Co_teacherID = ?
        ");

        $stmt->execute([
            $courseId,
            $userId
        ]);

        return (bool)$stmt->fetchColumn();
    }

    if ($type === 0) {

        $stmt = $connect->prepare("
            SELECT COUNT(*)
            FROM students s
            JOIN courses c
                ON c.Co_classID = s.Stu_classID
            WHERE s.Stu_ID = ?
            AND c.Co_ID = ?
        ");

        $stmt->execute([
            $userId,
            $courseId
        ]);

        return (bool)$stmt->fetchColumn();
    }

    return false;
}

$courseId =
    (int)($_POST['course_id'] ?? 0);

$replyTo =
    !empty($_POST['reply_to_id'])
        ? (int)$_POST['reply_to_id']
        : null;

if (!canAccess(
    $connect,
    $courseId,
    $userId,
    $userType
)) {
    response([
        'success' => false,
        'message' => 'دسترسی غیرمجاز.'
    ]);
}

if (
    !isset($_FILES['audio']) ||
    $_FILES['audio']['error'] !== UPLOAD_ERR_OK
) {
    response([
        'success' => false,
        'message' => 'فایل صوتی دریافت نشد.'
    ]);
}

$audio = $_FILES['audio'];

if ($audio['size'] > 25 * 1024 * 1024) {
    response([
        'success' => false,
        'message' => 'حجم ویس بیش از حد مجاز است.'
    ]);
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);

$mime =
    finfo_file(
        $finfo,
        $audio['tmp_name']
    );

finfo_close($finfo);

$allowed = [
    'audio/webm' => 'webm',
    'video/webm' => 'webm',
    'audio/ogg' => 'ogg',
    'application/ogg' => 'ogg',
    'audio/wav' => 'wav',
    'audio/x-wav' => 'wav',
    'audio/mpeg' => 'mp3',
    'audio/mp3' => 'mp3',
    'audio/mp4' => 'm4a',
    'audio/x-m4a' => 'm4a'
];

$extension =
    $allowed[$mime] ?? null;

if (!$extension) {

    $clientExtension =
        strtolower(
            pathinfo(
                $audio['name'],
                PATHINFO_EXTENSION
            )
        );

    if (in_array(
        $clientExtension,
        ['webm', 'ogg', 'wav', 'mp3', 'm4a'],
        true
    )) {
        $extension = $clientExtension;
    }
}

if (!$extension) {
    $extension = 'webm';
}

if ($replyTo) {

    $stmt = $connect->prepare("
        SELECT id
        FROM messages
        WHERE id = ?
        AND course_id = ?
        LIMIT 1
    ");

    $stmt->execute([
        $replyTo,
        $courseId
    ]);

    if (!$stmt->fetchColumn()) {
        $replyTo = null;
    }
}

$uploadDir =
    __DIR__ . '/audio/';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$fileName =
    bin2hex(random_bytes(16)) .
    '.' .
    $extension;

$target =
    $uploadDir . $fileName;

if (!move_uploaded_file(
    $audio['tmp_name'],
    $target
)) {
    response([
        'success' => false,
        'message' => 'ذخیره ویس انجام نشد.'
    ]);
}

$relativePath =
    'audio/' . $fileName;

$senderType =
    $userType === 1
        ? 'teacher'
        : 'student';

$connect->beginTransaction();

try {

    $stmt = $connect->prepare("
        INSERT INTO messages
        (
            course_id,
            sender_type,
            sender_id,
            message,
            reply_to_id
        )
        VALUES (?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $courseId,
        $senderType,
        $userId,
        '',
        $replyTo
    ]);

    $messageId =
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
        $messageId,
        $courseId,
        $senderType,
        $userId,
        $relativePath,
        'voice.' . $extension,
        0
    ]);

    $connect->commit();

    response([
        'success' => true
    ]);

} catch (Exception $e) {

    $connect->rollBack();

    @unlink($target);

    response([
        'success' => false,
        'message' => 'ذخیره ویس انجام نشد.'
    ]);
}