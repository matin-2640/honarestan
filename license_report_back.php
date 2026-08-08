<?php
session_start();
if (!(isset($_SESSION["state_login"]) && $_SESSION["type"] == 2 || $_SESSION["type"] == 3) || $_SESSION["type"] == 4) {
    header("location:login.php");
    exit();
}
header('Content-Type: application/json; charset=utf-8');
require_once 'connect.php';

$terms_list = [
    1 => 'مهر و آبان',
    2 => 'آذر',
    3 => 'نوبت اول',
    4 => 'اسفند',
    5 => 'فروردین و اردیبهشت',
    6 => 'نوبت دوم'
];

$action = $_POST['action'] ?? '';
$term = isset($_POST['term']) ? (int) $_POST['term'] : 0;

if ($term < 1 || $term > 6) {
    echo json_encode(['status' => 'error', 'message' => 'ترم نامعتبر است.']);
    exit;
}

$termName = $terms_list[$term];

try {
    if ($action === 'grant') {
        // بررسی وجود رکورد از قبل
        $check_stmt = $connect->prepare("SELECT ID FROM report_license WHERE term = :term");
        $check_stmt->execute([':term' => $term]);

        if ($check_stmt->rowCount() > 0) {
            echo json_encode([
                'status' => 'warning',
                'message' => "مجوز نمایش کارنامه دوره {$termName} از قبل به هنرجویان داده شده است."
            ]);
        } else {
            // ثبت جدید با term انتخابی و publish = 1
            $insert_stmt = $connect->prepare("INSERT INTO report_license (term, publish) VALUES (:term, 1)");
            $insert_stmt->execute([':term' => $term]);

            echo json_encode([
                'status' => 'success',
                'message' => "مجوز نمایش این کارنامه ({$termName}) به هنرجویان داده شد."
            ]);
        }
    } elseif ($action === 'revoke') {
        // حذف رکورد دوره جهت لغو دسترسی
        $delete_stmt = $connect->prepare("DELETE FROM report_license WHERE term = :term");
        $delete_stmt->execute([':term' => $term]);

        echo json_encode(['status' => 'success']);
    }
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'خطای دیتابیس: ' . $e->getMessage()
    ]);
}
