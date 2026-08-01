<?php
session_start();
include("../connect.php");

$Stu_ID = intval($_GET["id"]);

$sql = "SELECT * FROM students WHERE Stu_ID = ?";
$stmt = $connect->prepare($sql);
$stmt->execute([$Stu_ID]);

$student = $stmt->fetch(PDO::FETCH_ASSOC);

$phone = $student['Stu_fatherPhone'];
$fatherName = $student['Stu_fatherName'];

$sqlTitle = "SELECT title 
             FROM disciplinary_records 
             WHERE student_id = ?
             ORDER BY id DESC
             LIMIT 1";

$stmtTitle = $connect->prepare($sqlTitle);
$stmtTitle->execute([$Stu_ID]);

$record = $stmtTitle->fetch(PDO::FETCH_ASSOC);

$title = $record ? $record['title'] : 'پرونده انضباطی';

$url = 'https://console.melipayamak.com/api/send/shared/08bebad81c6a4c1bab324b7f167cd87f';

$data = array(
    'bodyId' => 507121,
    'to' => $phone,
    'args' => [
        "
جناب $fatherName عزیز پرونده انضباطی فرزند شما با عنوان $title ثبت گردید
مدیریت هنرستان راه دانش 
"
    ]
);

$data_string = json_encode($data);

$ch = curl_init($url);

curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'Content-Length: ' . strlen($data_string)
));

$result = curl_exec($ch);

if (curl_errno($ch)) {
    echo "Curl Error: " . curl_error($ch);
} else {
    echo $result;
}

curl_close($ch);

header("location:../view_disciplinary.php");
exit();
?>s