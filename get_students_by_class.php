<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'connect.php';

header('Content-Type: application/json; charset=utf-8');

$staffTypes = [1, 2, 3, 4];
$userType = intval($_SESSION['type'] ?? -1);

if (!in_array($userType,$staffTypes)) {
    echo json_encode([]);
    exit;
}

$classID = intval($_GET['class_id'] ?? 0);

if ($classID <= 0) {
    echo json_encode([]);
    exit;
}

try {
    $stmt =$pdo->prepare("SELECT Stu_ID, Stu_fullName, Stu_name, Stu_family FROM students WHERE Stu_classID = ? ORDER BY Stu_fullName ASC");
    $stmt->execute([$classID]);
    $students =$stmt->fetchAll(PDO::FETCH_ASSOC);

    $result = [];
    foreach ($students as$stu) {
        $fullName = !empty($stu['Stu_fullName']) ? $stu['Stu_fullName'] : trim($stu['Stu_name'] . ' ' . $stu['Stu_family']);$result[] = [
            'id' => $stu['Stu_ID'],
            'name' => $fullName
        ];
    }

    echo json_encode($result, JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    echo json_encode([]);
}