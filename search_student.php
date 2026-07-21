ش<?php
session_start();
if (!(isset($_SESSION["state_login"]) && $_SESSION["type"] <= 2)) {  
  exit(json_encode([0, [], 0]));  
}

include("connect.php");

header('Content-Type: application/json; charset=utf-8');

$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$keyword = isset($_POST['keyword']) ? trim($_POST['keyword']) : '';

// تعداد هنرجویان در هر صفحه
$students_per_page = 10; 

if ($page < 1) { $page = 1; }
$start = ($page - 1) * $students_per_page;

$where_clauses = [];
$params = [];

if (!empty($keyword)) {
    $where_clauses[] = "(Students.Stu_fullName LIKE :kw OR Students.Stu_phone LIKE :kw OR Students.Stu_nationalCode LIKE :kw)";
    $params[':kw'] = '%' . $keyword . '%';
}

$where_sql = "";
if (count($where_clauses) > 0) {
    $where_sql = " WHERE " . implode(" AND ", $where_clauses);
}

try {
    // ۱. تعداد کل نتایج سرچ
    $sql_count = "SELECT COUNT(*) FROM Students " . $where_sql;
    $stmt_count = $connect->prepare($sql_count);
    $stmt_count->execute($params);
    $total_students = intval($stmt_count->fetchColumn());

    // ۲. محاسبه درست تعداد صفحات
    $total_pages = ceil($total_students / $students_per_page);

    // ۳. گرفتن داده‌های همان صفحه
    $sql_student = "SELECT Students.*, Classes.C_grade, Classes.C_major 
                    FROM Students 
                    LEFT JOIN Classes ON Students.Stu_classID = Classes.C_ID 
                    " . $where_sql . " 
                    ORDER BY Students.Stu_ID DESC 
                    LIMIT :start, :limit";

    $stmt_student = $connect->prepare($sql_student);

    foreach ($params as $key => $val) {
        $stmt_student->bindValue($key, $val);
    }
    $stmt_student->bindValue(':start', (int)$start, PDO::PARAM_INT);
    $stmt_student->bindValue(':limit', (int)$students_per_page, PDO::PARAM_INT);

    $stmt_student->execute();
    $students_list = $stmt_student->fetchAll(PDO::FETCH_ASSOC);

    // ارسال پاسخ دیتابیس
    echo json_encode([$total_students, $students_list, (int)$total_pages]);

} catch (PDOException $e) {
    echo json_encode([0, [], 0]);
}
exit();
?>
