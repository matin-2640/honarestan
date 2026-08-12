<?php
session_start();
require_once "../connect.php";
header("Content-Type: application/json; charset=utf-8");

function response($success,$message="",$data=[]){
    echo json_encode(array_merge([
        "success"=>$success,
        "message"=>$message
    ],$data),JSON_UNESCAPED_UNICODE);
    exit;
}

if(!isset($_SESSION["ID"],$_SESSION["type"]) || !in_array((int)$_SESSION["type"],[0,1],true)){
    response(false,"دسترسی غیرمجاز است.");
}

$user_id=(int)$_SESSION["ID"];
$user_type=(int)$_SESSION["type"]===1?"teacher":"student";
$action=$_POST["action"]??$_GET["action"]??"";
$course_id=(int)($_POST["course_id"]??$_GET["course_id"]??0);
$room_id=(int)($_POST["room_id"]??$_GET["room_id"]??0);

if($course_id<=0){
    response(false,"درس نامعتبر است.");
}

function getCourse($course_id){
    global $connect;
    $stmt=$connect->prepare("SELECT Co_ID,Co_name,Co_teacherID,Co_classID FROM courses WHERE Co_ID=? LIMIT 1");
    $stmt->execute([$course_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function checkCourseAccess($course){
    global $connect,$user_id,$user_type;

    if(!$course){
        response(false,"درس پیدا نشد.");
    }

    if($user_type==="teacher"){
        if((int)$course["Co_teacherID"]!==$user_id){
            response(false,"شما به این کلاس دسترسی ندارید.");
        }
        return;
    }

    $stmt=$connect->prepare("SELECT Stu_ID FROM students WHERE Stu_ID=? AND Stu_classID=? LIMIT 1");
    $stmt->execute([$user_id,$course["Co_classID"]]);

    if(!$stmt->fetch()){
        response(false,"شما به این کلاس دسترسی ندارید.");
    }
}

function getRoom($room_id,$course_id){
    global $connect;
    $stmt=$connect->prepare("SELECT * FROM voice_rooms WHERE id=? AND course_id=? LIMIT 1");
    $stmt->execute([$room_id,$course_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getUserName($type,$id){
    global $connect;

    if($type==="teacher"){
        $stmt=$connect->prepare("SELECT T_fullName FROM teachers WHERE T_ID=? LIMIT 1");
    }else{
        $stmt=$connect->prepare("SELECT Stu_fullName FROM students WHERE Stu_ID=? LIMIT 1");
    }

    $stmt->execute([$id]);
    $row=$stmt->fetch(PDO::FETCH_ASSOC);

    if(!$row){
        return "";
    }

    return $type==="teacher"
        ? ($row["T_fullName"]??"")
        : ($row["Stu_fullName"]??"");
}

$course=getCourse($course_id);
checkCourseAccess($course);

if($action==="get_room"){
    $stmt=$connect->prepare("
        SELECT id,course_id,teacher_id,status,created_at,ended_at
        FROM voice_rooms
        WHERE course_id=? AND status='active'
        ORDER BY id DESC
        LIMIT 1
    ");
    $stmt->execute([$course_id]);
    $room=$stmt->fetch(PDO::FETCH_ASSOC);

    response(true,"",[
        "active"=>$room?true:false,
        "room"=>$room?:null
    ]);
}

if($action==="start_room"){
    if($user_type!=="teacher"){
        response(false,"فقط معلم می‌تواند کلاس صوتی را شروع کند.");
    }

    if((int)$course["Co_teacherID"]!==$user_id){
        response(false,"شما معلم این درس نیستید.");
    }

    $connect->beginTransaction();

    try{
        $stmt=$connect->prepare("
            SELECT id
            FROM voice_rooms
            WHERE course_id=? AND status='active'
            ORDER BY id DESC
            LIMIT 1
            FOR UPDATE
        ");
        $stmt->execute([$course_id]);
        $existing=$stmt->fetch(PDO::FETCH_ASSOC);

        if($existing){
            $connect->commit();
            response(true,"کلاس صوتی از قبل فعال است.",[
                "room_id"=>(int)$existing["id"]
            ]);
        }

        $stmt=$connect->prepare("
            INSERT INTO voice_rooms
            (course_id,teacher_id,status)
            VALUES(?,?, 'active')
        ");
        $stmt->execute([$course_id,$user_id]);

        $newRoomId=(int)$connect->lastInsertId();

        $connect->commit();

        response(true,"کلاس صوتی شروع شد.",[
            "room_id"=>$newRoomId
        ]);
    }catch(Throwable $e){
        if($connect->inTransaction()){
            $connect->rollBack();
        }
        response(false,"خطا در شروع کلاس صوتی.");
    }
}

if($action==="join_room"){
    if($room_id<=0){
        response(false,"کلاس صوتی نامعتبر است.");
    }

    $room=getRoom($room_id,$course_id);

    if(!$room){
        response(false,"کلاس صوتی پیدا نشد.");
    }

    if($room["status"]!=="active"){
        response(false,"این کلاس صوتی پایان یافته است.");
    }

    if($user_type==="teacher" && (int)$room["teacher_id"]!==$user_id){
        response(false,"شما معلم این کلاس نیستید.");
    }

    $stmt=$connect->prepare("
        INSERT INTO voice_participants
        (room_id,user_type,user_id,mic_enabled,joined_at,left_at,last_seen)
        VALUES(?,?,?,1,NOW(),NULL,NOW())
        ON DUPLICATE KEY UPDATE
        left_at=NULL,
        last_seen=NOW()
    ");
    $stmt->execute([
        $room_id,
        $user_type,
        $user_id
    ]);

    response(true,"وارد کلاس صوتی شدید.",[
        "room_id"=>$room_id,
        "user_type"=>$user_type,
        "user_id"=>$user_id
    ]);
}

if($action==="heartbeat"){
    if($room_id<=0){
        response(false,"کلاس صوتی نامعتبر است.");
    }

    $room=getRoom($room_id,$course_id);

    if(!$room || $room["status"]!=="active"){
        response(false,"کلاس صوتی فعال نیست.");
    }

    $stmt=$connect->prepare("
        UPDATE voice_participants
        SET last_seen=NOW()
        WHERE room_id=?
        AND user_type=?
        AND user_id=?
        AND left_at IS NULL
    ");
    $stmt->execute([
        $room_id,
        $user_type,
        $user_id
    ]);

    response(true);
}

if($action==="participants"){
    if($room_id<=0){
        response(false,"کلاس صوتی نامعتبر است.");
    }

    $room=getRoom($room_id,$course_id);

    if(!$room){
        response(false,"کلاس صوتی پیدا نشد.");
    }

    $stmt=$connect->prepare("
        SELECT
            vp.id,
            vp.room_id,
            vp.user_type,
            vp.user_id,
            vp.mic_enabled,
            vp.joined_at,
            vp.last_seen
        FROM voice_participants vp
        WHERE vp.room_id=?
        AND vp.left_at IS NULL
        AND vp.last_seen>=DATE_SUB(NOW(),INTERVAL 15 SECOND)
        ORDER BY
            CASE WHEN vp.user_type='teacher' THEN 0 ELSE 1 END,
            vp.joined_at ASC
    ");
    $stmt->execute([$room_id]);
    $rows=$stmt->fetchAll(PDO::FETCH_ASSOC);

    $participants=[];

    foreach($rows as $row){
        $participants[]=[
            "id"=>(int)$row["id"],
            "room_id"=>(int)$row["room_id"],
            "user_type"=>$row["user_type"],
            "user_id"=>(int)$row["user_id"],
            "full_name"=>getUserName($row["user_type"],(int)$row["user_id"]),
            "mic_enabled"=>(int)$row["mic_enabled"],
            "joined_at"=>$row["joined_at"],
            "last_seen"=>$row["last_seen"]
        ];
    }

    response(true,"",[
        "participants"=>$participants
    ]);
}

if($action==="toggle_mic"){
    if($room_id<=0){
        response(false,"کلاس صوتی نامعتبر است.");
    }

    $room=getRoom($room_id,$course_id);

    if(!$room || $room["status"]!=="active"){
        response(false,"کلاس صوتی فعال نیست.");
    }

    $enabled=isset($_POST["enabled"])?(int)$_POST["enabled"]:1;
    $enabled=$enabled===1?1:0;

    $stmt=$connect->prepare("
        UPDATE voice_participants
        SET mic_enabled=?,last_seen=NOW()
        WHERE room_id=?
        AND user_type=?
        AND user_id=?
        AND left_at IS NULL
    ");
    $stmt->execute([
        $enabled,
        $room_id,
        $user_type,
        $user_id
    ]);

    if($stmt->rowCount()===0){
        response(false,"ابتدا باید وارد کلاس صوتی شوید.");
    }

    response(true,"وضعیت میکروفون تغییر کرد.",[
        "enabled"=>$enabled
    ]);
}

if($action==="teacher_mute"){
    if($user_type!=="teacher"){
        response(false,"فقط معلم می‌تواند میکروفون دانش‌آموز را کنترل کند.");
    }

    if((int)$course["Co_teacherID"]!==$user_id){
        response(false,"شما معلم این درس نیستید.");
    }

    if($room_id<=0){
        response(false,"کلاس صوتی نامعتبر است.");
    }

    $room=getRoom($room_id,$course_id);

    if(!$room || $room["status"]!=="active"){
        response(false,"کلاس صوتی فعال نیست.");
    }

    if((int)$room["teacher_id"]!==$user_id){
        response(false,"شما معلم این کلاس نیستید.");
    }

    $target_type=$_POST["target_type"]??"";
    $target_id=(int)($_POST["target_id"]??0);
    $enabled=isset($_POST["enabled"])?(int)$_POST["enabled"]:0;

    if($target_type!=="student" || $target_id<=0){
        response(false,"دانش‌آموز نامعتبر است.");
    }

    $enabled=$enabled===1?1:0;

    $stmt=$connect->prepare("
        UPDATE voice_participants
        SET mic_enabled=?,last_seen=NOW()
        WHERE room_id=?
        AND user_type='student'
        AND user_id=?
        AND left_at IS NULL
    ");
    $stmt->execute([
        $enabled,
        $room_id,
        $target_id
    ]);

    if($stmt->rowCount()===0){
        response(false,"دانش‌آموز در کلاس صوتی حضور ندارد.");
    }

    response(true,"وضعیت میکروفون تغییر کرد.",[
        "enabled"=>$enabled
    ]);
}

if($action==="send_signal"){
    if($room_id<=0){
        response(false,"کلاس صوتی نامعتبر است.");
    }

    $room=getRoom($room_id,$course_id);

    if(!$room || $room["status"]!=="active"){
        response(false,"کلاس صوتی فعال نیست.");
    }

    $receiver_type=$_POST["receiver_type"]??"";
    $receiver_id=(int)($_POST["receiver_id"]??0);
    $signal_type=$_POST["signal_type"]??"";
    $signal_data=$_POST["signal_data"]??"";

    if(!in_array($receiver_type,["student","teacher"],true)){
        response(false,"گیرنده نامعتبر است.");
    }

    if($receiver_id<=0){
        response(false,"شناسه گیرنده نامعتبر است.");
    }

    if(!in_array($signal_type,["offer","answer","ice"],true)){
        response(false,"نوع سیگنال نامعتبر است.");
    }

    if($signal_data===""){
        response(false,"اطلاعات سیگنال خالی است.");
    }

    $stmt=$connect->prepare("
        SELECT id
        FROM voice_participants
        WHERE room_id=?
        AND user_type=?
        AND user_id=?
        AND left_at IS NULL
        LIMIT 1
    ");
    $stmt->execute([
        $room_id,
        $user_type,
        $user_id
    ]);

    if(!$stmt->fetch()){
        response(false,"شما در این کلاس حضور ندارید.");
    }

    $stmt=$connect->prepare("
        SELECT id
        FROM voice_participants
        WHERE room_id=?
        AND user_type=?
        AND user_id=?
        AND left_at IS NULL
        LIMIT 1
    ");
    $stmt->execute([
        $room_id,
        $receiver_type,
        $receiver_id
    ]);

    if(!$stmt->fetch()){
        response(false,"گیرنده در کلاس حضور ندارد.");
    }

    if(strlen($signal_data)>500000){
        response(false,"اطلاعات سیگنال بیش از حد بزرگ است.");
    }

    $stmt=$connect->prepare("
        INSERT INTO voice_signals
        (
            room_id,
            sender_type,
            sender_id,
            receiver_type,
            receiver_id,
            signal_type,
            signal_data
        )
        VALUES(?,?,?,?,?,?,?)
    ");
    $stmt->execute([
        $room_id,
        $user_type,
        $user_id,
        $receiver_type,
        $receiver_id,
        $signal_type,
        $signal_data
    ]);

    response(true);
}

if($action==="get_signals"){
    if($room_id<=0){
        response(false,"کلاس صوتی نامعتبر است.");
    }

    $room=getRoom($room_id,$course_id);

    if(!$room){
        response(false,"کلاس صوتی پیدا نشد.");
    }

    $after_id=(int)($_POST["after_id"]??$_GET["after_id"]??0);

    $stmt=$connect->prepare("
        SELECT
            id,
            room_id,
            sender_type,
            sender_id,
            receiver_type,
            receiver_id,
            signal_type,
            signal_data,
            created_at
        FROM voice_signals
        WHERE room_id=?
        AND receiver_type=?
        AND receiver_id=?
        AND id>?
        ORDER BY id ASC
        LIMIT 100
    ");
    $stmt->execute([
        $room_id,
        $user_type,
        $user_id,
        $after_id
    ]);

    $signals=$stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach($signals as &$signal){
        $signal["id"]=(int)$signal["id"];
        $signal["room_id"]=(int)$signal["room_id"];
        $signal["sender_id"]=(int)$signal["sender_id"];
        $signal["receiver_id"]=(int)$signal["receiver_id"];
    }
    unset($signal);

    response(true,"",[
        "signals"=>$signals
    ]);
}

if($action==="leave_room"){
    if($room_id<=0){
        response(false,"کلاس صوتی نامعتبر است.");
    }

    $room=getRoom($room_id,$course_id);

    if(!$room){
        response(false,"کلاس صوتی پیدا نشد.");
    }

    $stmt=$connect->prepare("
        UPDATE voice_participants
        SET left_at=NOW(),last_seen=NOW()
        WHERE room_id=?
        AND user_type=?
        AND user_id=?
        AND left_at IS NULL
    ");
    $stmt->execute([
        $room_id,
        $user_type,
        $user_id
    ]);

    response(true,"از کلاس صوتی خارج شدید.");
}

if($action==="end_room"){
    if($user_type!=="teacher"){
        response(false,"فقط معلم می‌تواند کلاس صوتی را پایان دهد.");
    }

    if((int)$course["Co_teacherID"]!==$user_id){
        response(false,"شما معلم این درس نیستید.");
    }

    if($room_id<=0){
        response(false,"کلاس صوتی نامعتبر است.");
    }

    $room=getRoom($room_id,$course_id);

    if(!$room){
        response(false,"کلاس صوتی پیدا نشد.");
    }

    if((int)$room["teacher_id"]!==$user_id){
        response(false,"شما معلم این کلاس نیستید.");
    }

    if($room["status"]!=="active"){
        response(false,"کلاس صوتی قبلاً پایان یافته است.");
    }

    $connect->beginTransaction();

    try{
        $stmt=$connect->prepare("
            UPDATE voice_rooms
            SET status='ended',ended_at=NOW()
            WHERE id=? AND course_id=? AND status='active'
        ");
        $stmt->execute([
            $room_id,
            $course_id
        ]);

        $stmt=$connect->prepare("
            UPDATE voice_participants
            SET left_at=NOW(),last_seen=NOW()
            WHERE room_id=? AND left_at IS NULL
        ");
        $stmt->execute([$room_id]);

        $connect->commit();

        response(true,"کلاس صوتی پایان یافت.");
    }catch(Throwable $e){
        if($connect->inTransaction()){
            $connect->rollBack();
        }
        response(false,"خطا در پایان کلاس صوتی.");
    }
}

response(false,"عملیات نامعتبر است.");