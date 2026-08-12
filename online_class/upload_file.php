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

if (!$courseId) {
    response([
        'success' => false,
        'message' => 'کلاس مشخص نشده است.'
    ]);
}

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
    !isset($_FILES['file']) ||
    $_FILES['file']['error'] !== UPLOAD_ERR_OK
) {
    response([
        'success' => false,
        'message' => 'فایل دریافت نشد.'
    ]);
}

$file = $_FILES['file'];

$maxSize = 100 * 1024 * 1024;

if ($file['size'] > $maxSize) {
    response([
        'success' => false,
        'message' => 'حداکثر حجم فایل 100 مگابایت است.'
    ]);
}

$originalName =
    basename($file['name']);

$extension =
    strtolower(
        pathinfo(
            $originalName,
            PATHINFO_EXTENSION
        )
    );

$blocked = [
    'php',
    'php3',
    'php4',
    'php5',
    'php7',
    'php8',
    'phtml',
    'phar',
    'exe',
    'bat',
    'cmd',
    'com',
    'scr',
    'msi',
    'cgi',
    'pl',
    'py',
    'sh'
];

if (in_array($extension, $blocked, true)) {
    response([
        'success' => false,
        'message' => 'این نوع فایل مجاز نیست.'
    ]);
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

$uploadDir = __DIR__ . '/files/';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$safeName =
    bin2hex(random_bytes(16)) .
    ($extension ? '.' . $extension : '');

$target =
    $uploadDir . $safeName;

if (!move_uploaded_file(
    $file['tmp_name'],
    $target
)) {
    response([
        'success' => false,
        'message' => 'ذخیره فایل انجام نشد.'
    ]);
}

$relativePath =
    'files/' . $safeName;

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
        INSERT INTO message_files
        (
            message_id,
            course_id,
            sender_type,
            sender_id,
            file_path,
            file_name,
            file_size,
            file_type
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $messageId,
        $courseId,
        $senderType,
        $userId,
        $relativePath,
        $originalName,
        $file['size'],
        $file['type'] ?? null
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
        'message' => 'ذخیره اطلاعات فایل انجام نشد.'
    ]);
}