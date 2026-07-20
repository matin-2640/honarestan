<?php
session_start();
include("connect.php");

$id = $_POST["C_ID"];
$C_grade = intval($_POST['C_grade']);
$C_major = $_POST['C_major'];

// check values : {
if (!isset($_POST["C_ID"], $_POST["C_grade"], $_POST["C_major"])) {
    $_SESSION["send_error"] = true;
    header("Location: edit_class.php?id=" . $id);
    exit();
}

if (!in_array($C_grade, [10, 11, 12])) {
    $_SESSION["grade_error"] = true;
    header("Location: edit_class.php?id=" . $id);
    exit();
}


$sql = "SELECT * FROM Classes WHERE C_grade = :C_grade AND C_major = :C_major LIMIT 1";

$stmt = $connect->prepare($sql);
$stmt->bindParam(":C_grade", $C_grade);
$stmt->bindParam(":C_major", $C_major);
$stmt->execute();

if ($stmt->fetch(PDO::FETCH_ASSOC)) {
    $_SESSION["error_dup"] = true;
    header("Location: edit_class.php?id=" . $id);
    exit();
}
// }

$sql = "UPDATE classes SET
C_grade = :C_grade,
C_major = :C_major
WHERE C_ID = :C_ID";

$stmt = $connect->prepare($sql);

$stmt->bindParam(":C_grade", $C_grade);
$stmt->bindParam(":C_major", $C_major);
$stmt->bindParam(":C_ID", $id);

$stmt->execute();

$_SESSION["edit_class"] = true;
header("Location: edit_class.php?id=" . $id);
exit();
?>