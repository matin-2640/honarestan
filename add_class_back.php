<?php
session_start();
include("connect.php");

$C_grade = intval($_POST['C_grade']);
$C_major = $_POST['C_major'];

// check values : {
if (!isset($_POST["C_grade"], $_POST["C_major"])) {
    $_SESSION["send_error"] = true;
    exit();
}

if (!in_array($C_grade, [10, 11, 12])) {
    $_SESSION["grade_error"] = true;
    header("location:add_class.php");
    exit();
}

$sql = "SELECT * FROM Classes WHERE C_grade = :C_grade AND C_major = :C_major LIMIT 1";

$stmt = $connect->prepare($sql);
$stmt->bindParam(":C_grade", $C_grade);
$stmt->bindParam(":C_major", $C_major);
$stmt->execute();

if ($stmt->fetch(PDO::FETCH_ASSOC)) {
    $_SESSION["error_dup"] = true;
    header("Location: add_class.php");
    exit();
}
// }

$sql = "INSERT INTO Classes (C_grade  , C_major )
VALUES ( :C_grade , :C_major ) ";

$stmt = $connect->prepare($sql);

$stmt->bindParam(":C_grade", $C_grade);
$stmt->bindParam(":C_major", $C_major);

$stmt->execute();

$_SESSION["add_class"] = true;
header("location:add_class.php");
exit();
?>