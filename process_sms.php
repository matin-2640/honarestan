<?php
include("connect.php");

header('Content-Type: application/json; charset=utf-8');

$recipient_type  = isset($_POST['recipient_type']) ? $_POST['recipient_type'] : '';
$text            = isset($_POST['sms_text']) ? trim($_POST['sms_text']) : '';
$send_to_parents = isset($_POST['send_to_parents']) ? true : false;

if (empty($recipient_type) || empty($text)) {
    echo json_encode(['status' => 'error', 'message' => 'اطلاعات ارسالی ناقص است.']);
    exit;
}

$receivers = array();

// ۱. همه هنرجویان
if ($recipient_type == 'all_students') {
    
    $stmt = $connect->prepare("SELECT Stu_fullName, Stu_phone, Stu_fatherName, Stu_fatherPhone FROM Students");
    $stmt->execute();
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($students as $row) {
        if (!empty($row['Stu_phone'])) {
            $receivers[] = [
                'name'  => $row['Stu_fullName'],
                'phone' => $row['Stu_phone']
            ];
        }
        if ($send_to_parents && !empty($row['Stu_fatherPhone'])) {
            $receivers[] = [
                'name'  => 'ولی ' . $row['Stu_fullName'],
                'phone' => $row['Stu_fatherPhone']
            ];
        }
    }

} 
// ۲. کلاس خاص
elseif (strpos($recipient_type, 'class_') === 0) {
    
    $class_id = str_replace('class_', '', $recipient_type);

    $stmt = $connect->prepare("SELECT Stu_fullName, Stu_phone, Stu_fatherName, Stu_fatherPhone FROM Students WHERE Stu_classID = :cid");
    $stmt->bindParam(':cid', $class_id);
    $stmt->execute();
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($students as $row) {
        if (!empty($row['Stu_phone'])) {
            $receivers[] = [
                'name'  => $row['Stu_fullName'],
                'phone' => $row['Stu_phone']
            ];
        }
        if ($send_to_parents && !empty($row['Stu_fatherPhone'])) {
            $receivers[] = [
                'name'  => 'ولی ' . $row['Stu_fullName'],
                'phone' => $row['Stu_fatherPhone']
            ];
        }
    }

} 
// ۳. هنرآموزان
elseif ($recipient_type == 'teachers') {
    
    $stmt = $connect->prepare("SELECT T_fullName, T_phone FROM Teachers");
    $stmt->execute();
    $teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($teachers as $row) {
        if (!empty($row['T_phone'])) {
            $receivers[] = [
                'name'  => $row['T_fullName'],
                'phone' => $row['T_phone']
            ];
        }
    }

}

if (count($receivers) == 0) {
    echo json_encode(['status' => 'error', 'message' => 'هیچ شماره تماسی برای گروه انتخاب شده پیدا نشد.']);
    exit;
}

// ارسال پیامک به تک تک افراد لیست با فراخوانی فایل adminsms.php
foreach ($receivers as $person) {
    $name  = $person['name'];
    $phone = $person['phone'];

    include("sms/adminsms.php");
}

echo json_encode([
    'status'  => 'success', 
    'message' => 'ارسال با موفقیت انجام شد.'
]);
exit;
?>
