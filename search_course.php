<?php
session_start();
if (!(isset($_SESSION["state_login"]) && $_SESSION["type"] <= 2)) {
    exit(json_encode([0, [], 0]));
}

include("connect.php");

header('Content-Type: application/json; charset=utf-8');

$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$keyword = isset($_POST['keyword']) ? trim($_POST['keyword']) : '';

$courses_per_page = 10;

if ($page < 1) {
    $page = 1;
}
$start = ($page - 1) * $courses_per_page;

$where_clauses = [];
$params = [];

if (!empty($keyword)) {
    $where_clauses[] = "(Courses.Co_name LIKE :kw OR Teachers.T_fullName LIKE :kw OR Classes.C_major LIKE :kw OR Classes.C_grade LIKE :kw)";
    $params[':kw'] = '%' . $keyword . '%';
}

$where_sql = "";
if (count($where_clauses) > 0) {
    $where_sql = " WHERE " . implode(" AND ", $where_clauses);
}

try {
    $sql_count = "SELECT COUNT(*) 
                  FROM Courses 
                  LEFT JOIN Classes ON Courses.Co_classID = Classes.C_ID 
                  LEFT JOIN Teachers ON Courses.Co_teacherID = Teachers.T_ID 
                  " . $where_sql;

    $stmt_count = $connect->prepare($sql_count);
    $stmt_count->execute($params);
    $total_courses = intval($stmt_count->fetchColumn());

    $total_pages = ceil($total_courses / $courses_per_page);

    $sql_courses = "SELECT Courses.*, Classes.C_grade, Classes.C_major, Teachers.T_fullName 
                    FROM Courses 
                    LEFT JOIN Classes ON Courses.Co_classID = Classes.C_ID 
                    LEFT JOIN Teachers ON Courses.Co_teacherID = Teachers.T_ID 
                    " . $where_sql . " 
                    ORDER BY Courses.Co_ID DESC 
                    LIMIT :start, :limit";

    $stmt_courses = $connect->prepare($sql_courses);

    foreach ($params as $key => $val) {
        $stmt_courses->bindValue($key, $val);
    }
    $stmt_courses->bindValue(':start', (int) $start, PDO::PARAM_INT);
    $stmt_courses->bindValue(':limit', (int) $courses_per_page, PDO::PARAM_INT);

    $stmt_courses->execute();
    $courses_list = $stmt_courses->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([$total_courses, $courses_list, (int) $total_pages]);

} catch (PDOException $e) {
    echo json_encode([0, [], 0]);
}
exit();
?>