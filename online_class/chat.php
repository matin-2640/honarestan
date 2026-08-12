<?php
session_start();
require_once "../connect.php";

if (!isset($_SESSION["ID"], $_SESSION["type"]) || !in_array((int)$_SESSION["type"], [0, 1])) {
    header("Location: ../login.php");
    exit;
}

$course_id = isset($_GET["course_id"]) ? (int)$_GET["course_id"] : 0;

if ($course_id <= 0) {
    exit("درس نامعتبر است.");
}

$type = (int)$_SESSION["type"];
$user_id = (int)$_SESSION["ID"];

$stmt = $connect->prepare("
    SELECT Co_ID, Co_name, Co_teacherID, Co_classID
    FROM courses
    WHERE Co_ID = ?
    LIMIT 1
");
$stmt->execute([$course_id]);
$course = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$course) {
    exit("درس پیدا نشد.");
}

if ($type === 0) {
    $stmt = $connect->prepare("
        SELECT Stu_ID
        FROM students
        WHERE Stu_ID = ?
        AND Stu_classID = ?
        LIMIT 1
    ");

    $stmt->execute([
        $user_id,
        $course["Co_classID"]
    ]);

    if (!$stmt->fetch()) {
        exit("شما به این کلاس دسترسی ندارید.");
    }

    $sender_type = "student";

    $stmt = $connect->prepare("
        SELECT Stu_fullName
        FROM students
        WHERE Stu_ID = ?
        LIMIT 1
    ");

    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    if ((int)$course["Co_teacherID"] !== $user_id) {
        exit("شما به این کلاس دسترسی ندارید.");
    }

    $sender_type = "teacher";

    $stmt = $connect->prepare("
        SELECT T_fullName
        FROM teachers
        WHERE T_ID = ?
        LIMIT 1
    ");

    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}

$user_name = $user["Stu_fullName"] ?? $user["T_fullName"] ?? "";
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title><?= htmlspecialchars($course["Co_name"]) ?></title>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<script src="../js/jquery-1.10.2.min.js"></script>
<script src="../js/sweetalert2.min.js"></script>
<link rel="stylesheet" href="../js/sweetalert2.min.css">
<link rel="stylesheet" href="../styles/font.css">
<link rel="icon" href="../images/icons/rahdanesh.png">
<link rel="stylesheet" href="styles/chat.css">

<style>
.voice-call-btn{
    width:42px;
    height:42px;
    border:0;
    border-radius:50%;
    background:#eaf5ff;
    color:#1684df;
    display:none;
    align-items:center;
    justify-content:center;
    cursor:pointer;
    font-size:19px;
    margin-right:auto;
    flex-shrink:0;
}

.voice-call-btn.teacher-visible{
    display:flex;
}

.voice-call-btn.active{
    display:flex;
}

.voice-call-btn:hover{
    background:#dcefff;
}

.voice-call-panel{
    position:fixed;
    top:0;
    right:0;
    width:390px;
    max-width:100%;
    height:100dvh;
    background:#fff;
    z-index:5000;
    box-shadow:-8px 0 30px rgba(0,0,0,.18);
    transform:translateX(105%);
    transition:.25s ease;
    display:flex;
    flex-direction:column;
}

.voice-call-panel.open{
    transform:translateX(0);
}

.voice-call-head{
    height:68px;
    flex-shrink:0;
    display:flex;
    align-items:center;
    gap:12px;
    padding:0 16px;
    color:#fff;
    background:linear-gradient(135deg,#3390ec,#1684df);
}

.voice-call-head-icon{
    width:42px;
    height:42px;
    border-radius:50%;
    background:rgba(255,255,255,.18);
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:20px;
}

.voice-call-title{
    flex:1;
    min-width:0;
}

.voice-call-title strong{
    display:block;
    font-size:14px;
}

.voice-call-title span{
    display:block;
    margin-top:4px;
    font-size:10px;
    opacity:.8;
}

.voice-call-close{
    width:38px;
    height:38px;
    border:0;
    border-radius:50%;
    background:rgba(255,255,255,.14);
    color:#fff;
    font-size:19px;
    cursor:pointer;
}

.voice-call-body{
    flex:1;
    min-height:0;
    overflow-y:auto;
    background:#f5f7fa;
    padding:15px;
}

.voice-teacher-card{
    background:#fff;
    border-radius:15px;
    padding:13px;
    display:flex;
    align-items:center;
    gap:11px;
    box-shadow:0 2px 8px rgba(0,0,0,.06);
    margin-bottom:14px;
}

.voice-avatar{
    width:45px;
    height:45px;
    border-radius:50%;
    background:#e5f2ff;
    color:#1684df;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:19px;
    flex-shrink:0;
}

.voice-avatar.teacher{
    background:#dff2ff;
    color:#1684df;
}

.voice-user-info{
    flex:1;
    min-width:0;
}

.voice-user-name{
    font-size:12px;
    font-weight:bold;
}

.voice-user-status{
    font-size:9px;
    color:#888;
    margin-top:4px;
}

.voice-mic-state{
    width:34px;
    height:34px;
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
    background:#edf1f4;
    color:#777;
    flex-shrink:0;
}

.voice-mic-state.on{
    background:#e3f7e8;
    color:#159447;
}

.voice-mic-state.off{
    background:#ffe7e7;
    color:#e53935;
}

.voice-participants-title{
    font-size:11px;
    color:#888;
    margin:10px 4px;
}

.voice-participant{
    background:#fff;
    border-radius:13px;
    padding:10px;
    display:flex;
    align-items:center;
    gap:10px;
    margin-bottom:7px;
}

.voice-participant .voice-avatar{
    width:38px;
    height:38px;
    font-size:16px;
}

.voice-participant-actions{
    display:flex;
    align-items:center;
    gap:5px;
}

.voice-control-btn{
    width:31px;
    height:31px;
    border:0;
    border-radius:50%;
    background:#edf1f4;
    color:#555;
    cursor:pointer;
}

.voice-control-btn:hover{
    background:#e0e5e9;
}

.voice-control-btn.danger{
    background:#ffe7e7;
    color:#e53935;
}

.voice-call-footer{
    flex-shrink:0;
    padding:13px;
    background:#fff;
    border-top:1px solid #e4e7ea;
    display:flex;
    gap:8px;
}

.voice-main-btn{
    flex:1;
    height:44px;
    border:0;
    border-radius:23px;
    background:#3390ec;
    color:#fff;
    font-family:inherit;
    cursor:pointer;
    font-size:12px;
}

.voice-main-btn.muted{
    background:#e53935;
}

.voice-leave-btn{
    width:44px;
    height:44px;
    border:0;
    border-radius:50%;
    background:#ffe7e7;
    color:#e53935;
    cursor:pointer;
    font-size:18px;
}

.voice-teacher-end{
    width:44px;
    height:44px;
    border:0;
    border-radius:50%;
    background:#ffe7e7;
    color:#e53935;
    cursor:pointer;
    font-size:18px;
}

.voice-empty{
    text-align:center;
    padding:45px 20px;
    color:#999;
}

.voice-empty i{
    display:block;
    font-size:42px;
    margin-bottom:10px;
    opacity:.5;
}

.voice-start-banner{
    display:none;
    margin:10px;
    padding:10px 13px;
    border-radius:12px;
    background:#eaf5ff;
    color:#1684df;
    font-size:11px;
    align-items:center;
    gap:8px;
}

.voice-start-banner.show{
    display:flex;
}

.voice-browser-error{
    background:#fff4e5;
    color:#a86200;
    border-radius:12px;
    padding:11px;
    font-size:10px;
    line-height:1.9;
    margin-bottom:12px;
}

.swal2-container{
    z-index:10000 !important;
}

@media(max-width:600px){
    .voice-call-panel{
        width:100%;
        height:100dvh;
    }

    .voice-call-head{
        height:60px;
    }

    .voice-call-body{
        padding:11px;
    }

    .voice-call-footer{
        padding:10px 10px calc(10px + env(safe-area-inset-bottom));
    }

    .voice-call-btn{
        width:39px;
        height:39px;
    }
}
</style>
</head>

<body>

<div class="chat-app">

<header class="chat-header">

<button class="back-btn" onclick="location.href='index.php'">
    <i class="bi bi-arrow-right"></i>
</button>

<div class="course-icon">
    <i class="bi bi-people-fill"></i>
</div>

<div class="course-info">

<div class="course-name">
<?= htmlspecialchars($course["Co_name"]) ?>
</div>

<div class="course-status">
کلاس مجازی
</div>

</div>

<button
    class="voice-call-btn <?= $type === 1 ? 'teacher-visible' : '' ?>"
    id="voiceCallBtn"
    title="<?= $type === 1 ? 'کلاس صوتی' : 'ورود به کلاس صوتی' ?>"
>
    <i class="bi bi-mic-fill"></i>
</button>

</header>

<div class="voice-start-banner" id="voiceStartBanner">
    <i class="bi bi-broadcast-pin"></i>
    <span>کلاس صوتی فعال است</span>
    <button
        style="margin-right:auto;border:0;background:none;color:#1684df;font-family:inherit;cursor:pointer"
        onclick="openVoicePanel()"
    >
        ورود
    </button>
</div>

<main class="messages-area" id="messagesArea">
<div class="messages-list" id="messagesList"></div>
</main>

<div class="chat-bottom">

<div class="reply-preview" id="replyPreview">

<div class="reply-preview-content">

<div class="reply-preview-title" id="replyPreviewTitle"></div>

<div class="reply-preview-text" id="replyPreviewText"></div>

</div>

<button class="reply-preview-close" onclick="cancelReply()">
<i class="bi bi-x"></i>
</button>

</div>

<div class="audio-preview" id="audioPreview">

<audio id="previewAudio" controls></audio>

<button class="audio-preview-send" id="sendRecordedAudio">
<i class="bi bi-send-fill"></i>
</button>

<button class="audio-preview-cancel" id="cancelRecordedAudio">
<i class="bi bi-x"></i>
</button>

</div>

<div class="input-wrapper">

<textarea
    id="messageInput"
    class="message-input"
    placeholder="پیام خود را بنویسید..."
    rows="1"
></textarea>

<div class="recording-box" id="recordingBox">

<span class="recording-dot"></span>

<span class="recording-time" id="recordingTime">
00:00
</span>

<button class="cancel-record" id="cancelRecording">
<i class="bi bi-x-lg"></i>
</button>

</div>

<button class="action-btn" id="fileBtn">
<i class="bi bi-paperclip"></i>
</button>

<button class="action-btn" id="voiceBtn">
<i class="bi bi-mic-fill"></i>
</button>

<button class="action-btn send-btn" id="sendBtn">
<i class="bi bi-send-fill"></i>
</button>

<input type="file" id="fileInput" hidden>

</div>

</div>

</div>

<div class="context-menu" id="contextMenu">

<button id="replyAction">
<i class="bi bi-reply"></i>
ریپلای
</button>

<button id="copyAction">
<i class="bi bi-copy"></i>
کپی
</button>

<button id="editAction">
<i class="bi bi-pencil"></i>
ویرایش
</button>

<button class="delete" id="deleteAction">
<i class="bi bi-trash"></i>
حذف
</button>

</div>

<div class="voice-overlay" id="voiceOverlay"></div>

<div class="voice-call-panel" id="voiceCallPanel">

<div class="voice-call-head">

<div class="voice-call-head-icon">
<i class="bi bi-mic-fill"></i>
</div>

<div class="voice-call-title">

<strong>
کلاس صوتی
</strong>

<span id="voiceCallStatus">
در حال بررسی...
</span>

</div>

<button class="voice-call-close" onclick="closeVoicePanel()">
<i class="bi bi-x"></i>
</button>

</div>

<div class="voice-call-body" id="voiceParticipants">

<div class="voice-empty">
<i class="bi bi-people"></i>
در حال دریافت اعضا...
</div>

</div>

<div class="voice-call-footer">

<button
    class="voice-main-btn"
    id="voiceMicButton"
    onclick="toggleMyVoiceMic()"
>
<i class="bi bi-mic-fill"></i>
میکروفون
</button>

<button
    class="voice-leave-btn"
    id="voiceLeaveBtn"
    onclick="leaveVoiceRoom()"
>
<i class="bi bi-telephone-x-fill"></i>
</button>

<?php if ($type === 1): ?>

<button
    class="voice-teacher-end"
    id="voiceEndBtn"
    onclick="endVoiceRoom()"
    title="پایان کلاس صوتی"
>
<i class="bi bi-x-lg"></i>
</button>

<?php endif; ?>

</div>

</div>

<script>

const COURSE_ID = <?= $course_id ?>;
const USER_ID = <?= $user_id ?>;
const USER_TYPE = <?= $type ?>;
const SENDER_TYPE = "<?= $sender_type ?>";

let lastMessageId = 0;
let selectedMessage = null;
let replyToId = null;

let mediaRecorder = null;
let audioChunks = [];
let recordedAudioBlob = null;
let recordingInterval = null;
let recordingSeconds = 0;

let currentUploadXHR = null;
let currentUploadElement = null;

let voiceRoomId = null;
let voiceJoined = false;
let voiceMicEnabled = true;
let voiceSignalId = 0;
let voicePollTimer = null;
let voiceParticipantTimer = null;
let voiceHeartbeatTimer = null;
let voiceReconnectTimer = null;
let voicePermissionBusy = false;

let localVoiceStream = null;
let voiceParticipantsCache = {};

const peerConnections = {};
const pendingIceCandidates = {};

const voiceConfig = {
    iceServers: [
        {
            urls: "stun:stun.l.google.com:19302"
        },
        {
            urls: "stun:stun1.l.google.com:19302"
        },
        {
            urls: "stun:stun2.l.google.com:19302"
        }
    ]
};

function voiceAjax(data, callback){

    $.ajax({
        url:"voice_action.php",
        type:"POST",
        data:data,
        dataType:"json",
        timeout:15000,
        success:function(res){
            if(typeof callback === "function"){
                callback(res);
            }
        },
        error:function(xhr){
            console.log("VOICE ERROR:", xhr.responseText);
            if(typeof callback === "function"){
                callback({
                    success:false,
                    message:"ارتباط با سرور کلاس صوتی برقرار نشد."
                });
            }
        }
    });
}

function getParticipantKey(type,id){
    return String(type) + "_" + String(id);
}

function getParticipantOrder(type,id){

    const typeValue = type === "teacher" ? 0 : 1;
    return typeValue * 1000000000 + parseInt(id,10);
}

function shouldInitiate(participant){

    const myOrder =
        getParticipantOrder(
            SENDER_TYPE,
            USER_ID
        );

    const otherOrder =
        getParticipantOrder(
            participant.user_type,
            participant.user_id
        );

    return myOrder < otherOrder;
}

function checkVoiceRoom(){

    voiceAjax({
        action:"get_room",
        course_id:COURSE_ID
    },function(res){

        if(!res || !res.success){
            return;
        }

        if(res.active && res.room){

            voiceRoomId =
                parseInt(res.room.id,10);

            $("#voiceStartBanner").addClass("show");

            $("#voiceCallStatus")
                .text("کلاس صوتی فعال است");

            $("#voiceCallBtn").addClass("active");

            if(voiceJoined && voiceRoomId){
                loadVoiceParticipants();
            }

        }else{

            $("#voiceStartBanner").removeClass("show");

            <?php if ($type === 0): ?>
            $("#voiceCallBtn").removeClass("active");
            <?php else: ?>
            $("#voiceCallBtn").addClass("teacher-visible");
            <?php endif; ?>

            if(voiceJoined){
                forceLeaveVoiceRoom();
            }

            voiceRoomId = null;
        }
    });
}

<?php if ($type === 1): ?>

$("#voiceCallBtn").click(function(){

    if(voiceRoomId){

        openVoicePanel();
        return;
    }

    Swal.fire({
        title:"شروع کلاس صوتی",
        text:"کلاس صوتی برای تمام اعضای این درس فعال می‌شود.",
        icon:"question",
        showCancelButton:true,
        confirmButtonText:"شروع کلاس",
        cancelButtonText:"انصراف"
    }).then(function(result){

        if(!result.isConfirmed){
            return;
        }

        voiceAjax({
            action:"start_room",
            course_id:COURSE_ID
        },function(res){

            if(res && res.success){

                voiceRoomId =
                    parseInt(res.room_id,10);

                $("#voiceCallBtn").addClass("active");
                $("#voiceStartBanner").addClass("show");

                openVoicePanel();

            }else{

                Swal.fire({
                    icon:"error",
                    title:"خطا",
                    text:res && res.message
                        ? res.message
                        : "کلاس صوتی ایجاد نشد."
                });
            }
        });
    });
});

<?php else: ?>

$("#voiceCallBtn").click(function(){
    openVoicePanel();
});

<?php endif; ?>

function openVoicePanel(){

    if(!voiceRoomId){

        Swal.fire({
            icon:"info",
            title:"کلاس صوتی فعال نیست",
            text:"در حال حاضر کلاس صوتی فعالی وجود ندارد."
        });

        return;
    }

    $("#voiceOverlay").addClass("show");
    $("#voiceCallPanel").addClass("open");

    if(!voiceJoined){
        joinVoiceRoom();
    }else{
        loadVoiceParticipants();
    }
}

function closeVoicePanel(){

    $("#voiceOverlay").removeClass("show");
    $("#voiceCallPanel").removeClass("open");
}

$("#voiceOverlay").click(function(){
    closeVoicePanel();
});

function getMicrophoneErrorMessage(error){

    if(location.protocol !== "https:" && location.hostname !== "localhost" && location.hostname !== "127.0.0.1"){
        return "دسترسی به میکروفون در این صفحه امکان‌پذیر نیست. سایت باید با HTTPS باز شود.";
    }

    if(!navigator.mediaDevices){
        return "مرورگر فعلی API میکروفون را در این صفحه در دسترس قرار نداده است. اگر با گوشی هستید، صفحه را با HTTPS باز کنید و از Chrome یا Safari استفاده کنید.";
    }

    if(error){

        if(error.name === "NotAllowedError" || error.name === "PermissionDeniedError"){
            return "دسترسی میکروفون برای این سایت رد شده است. از تنظیمات مجوزهای مرورگر، Microphone را روی Allow قرار دهید و دوباره وارد کلاس شوید.";
        }

        if(error.name === "NotFoundError"){
            return "هیچ میکروفونی روی دستگاه پیدا نشد.";
        }

        if(error.name === "NotReadableError"){
            return "میکروفون توسط برنامه یا تب دیگری در حال استفاده است.";
        }

        if(error.name === "SecurityError"){
            return "مرورگر به دلایل امنیتی اجازه استفاده از میکروفون را نمی‌دهد. صفحه باید با HTTPS باز شود.";
        }
    }

    return "دسترسی به میکروفون برقرار نشد.";
}

async function requestVoiceMicrophone(){

    if(voicePermissionBusy){
        return null;
    }

    voicePermissionBusy = true;

    try{

        if(!window.isSecureContext &&
            location.hostname !== "localhost" &&
            location.hostname !== "127.0.0.1"){

            throw new Error("INSECURE_CONTEXT");
        }

        if(!navigator.mediaDevices ||
            !navigator.mediaDevices.getUserMedia){

            throw new Error("MEDIA_DEVICES_UNAVAILABLE");
        }

        const stream =
            await navigator.mediaDevices.getUserMedia({
                audio:{
                    echoCancellation:true,
                    noiseSuppression:true,
                    autoGainControl:true,
                    channelCount:1
                },
                video:false
            });

        return stream;

    }catch(error){

        console.log("MIC ERROR:",error);

        let message =
            getMicrophoneErrorMessage(
                error
            );

        if(error.message === "INSECURE_CONTEXT"){
            message =
                "برای استفاده از میکروفون در موبایل، آدرس سایت باید با HTTPS باشد.";
        }

        if(error.message === "MEDIA_DEVICES_UNAVAILABLE"){
            message =
                "دسترسی میکروفون در این صفحه فعال نیست. اگر با گوشی هستید، سایت را با HTTPS و مرورگر Chrome یا Safari باز کنید.";
        }

        Swal.fire({
            icon:"error",
            title:"دسترسی به میکروفون",
            text:message,
            confirmButtonText:"باشه"
        });

        return null;

    }finally{

        voicePermissionBusy = false;
    }
}

async function joinVoiceRoom(){

    if(voiceJoined || !voiceRoomId){
        return;
    }

    localVoiceStream =
        await requestVoiceMicrophone();

    if(!localVoiceStream){
        return;
    }

    voiceAjax({
        action:"join_room",
        course_id:COURSE_ID,
        room_id:voiceRoomId
    },function(res){

        if(!res || !res.success){

            stopLocalVoice();

            Swal.fire({
                icon:"error",
                title:"ورود ناموفق",
                text:res && res.message
                    ? res.message
                    : "ورود به کلاس صوتی انجام نشد."
            });

            return;
        }

        voiceJoined = true;
        voiceMicEnabled = true;

        $("#voiceMicButton")
            .removeClass("muted")
            .html(
                '<i class="bi bi-mic-fill"></i> میکروفون'
            );

        startVoicePolling();
        loadVoiceParticipants();
    });
}

function startVoicePolling(){

    clearInterval(voicePollTimer);
    clearInterval(voiceParticipantTimer);
    clearInterval(voiceHeartbeatTimer);

    voicePollTimer =
        setInterval(
            getVoiceSignals,
            500
        );

    voiceParticipantTimer =
        setInterval(
            loadVoiceParticipants,
            1500
        );

    voiceHeartbeatTimer =
        setInterval(
            voiceHeartbeat,
            4000
        );

    getVoiceSignals();
    loadVoiceParticipants();
    voiceHeartbeat();
}

function voiceHeartbeat(){

    if(!voiceJoined || !voiceRoomId){
        return;
    }

    voiceAjax({
        action:"heartbeat",
        course_id:COURSE_ID,
        room_id:voiceRoomId
    },function(){});
}

async function createPeer(participant){

    const key =
        getParticipantKey(
            participant.user_type,
            participant.user_id
        );

    if(
        peerConnections[key] &&
        peerConnections[key].connectionState !== "closed" &&
        peerConnections[key].connectionState !== "failed"
    ){
        return peerConnections[key];
    }

    if(peerConnections[key]){
        try{
            peerConnections[key].close();
        }catch(e){}
    }

    const pc =
        new RTCPeerConnection(
            voiceConfig
        );

    peerConnections[key] = pc;
    pendingIceCandidates[key] = [];

    if(localVoiceStream){

        localVoiceStream
            .getTracks()
            .forEach(function(track){

                pc.addTrack(
                    track,
                    localVoiceStream
                );

            });
    }

    pc.onicecandidate =
        function(event){

            if(!event.candidate){
                return;
            }

            sendVoiceSignal(
                participant,
                "ice",
                JSON.stringify(
                    event.candidate
                )
            );
        };

    pc.ontrack =
        function(event){

            let audio =
                document.getElementById(
                    "remote-audio-" + key
                );

            if(!audio){

                audio =
                    document.createElement("audio");

                audio.id =
                    "remote-audio-" + key;

                audio.autoplay = true;
                audio.playsInline = true;
                audio.controls = false;
                audio.setAttribute("playsinline","");
                audio.setAttribute("autoplay","");
                audio.style.display = "none";

                document.body.appendChild(audio);
            }

            if(event.streams && event.streams[0]){
                audio.srcObject =
                    event.streams[0];
            }

            const playAudio = function(){

                const promise =
                    audio.play();

                if(promise && promise.catch){
                    promise.catch(function(){
                        document.addEventListener(
                            "click",
                            function(){
                                audio.play().catch(function(){});
                            },
                            {once:true}
                        );
                    });
                }
            };

            playAudio();
        };

    pc.onconnectionstatechange =
        function(){

            if(
                pc.connectionState === "failed" ||
                pc.connectionState === "closed"
            ){

                try{
                    pc.close();
                }catch(e){}

                delete peerConnections[key];

                const audio =
                    document.getElementById(
                        "remote-audio-" + key
                    );

                if(audio){
                    audio.pause();
                    audio.srcObject = null;
                    audio.remove();
                }

                if(
                    voiceJoined &&
                    voiceParticipantsCache[key]
                ){
                    setTimeout(function(){
                        if(voiceJoined){
                            connectToParticipant(
                                voiceParticipantsCache[key]
                            );
                        }
                    },1000);
                }
            }
        };

    return pc;
}

async function flushPendingIce(key){

    const pc =
        peerConnections[key];

    if(!pc){
        return;
    }

    if(
        !pendingIceCandidates[key] ||
        !pendingIceCandidates[key].length
    ){
        return;
    }

    const candidates =
        pendingIceCandidates[key].slice();

    pendingIceCandidates[key] = [];

    for(
        const candidate of candidates
    ){

        try{
            await pc.addIceCandidate(candidate);
        }catch(e){
            console.log("ICE FLUSH ERROR:",e);
        }
    }
}

async function connectToParticipant(participant){

    if(!voiceJoined){
        return;
    }

    if(
        participant.user_type === SENDER_TYPE &&
        parseInt(participant.user_id,10) === USER_ID
    ){
        return;
    }

    const key =
        getParticipantKey(
            participant.user_type,
            participant.user_id
        );

    voiceParticipantsCache[key] =
        participant;

    if(!shouldInitiate(participant)){
        return;
    }

    let pc =
        peerConnections[key];

    if(
        pc &&
        (
            pc.connectionState === "connected" ||
            pc.connectionState === "connecting"
        )
    ){
        return;
    }

    pc =
        await createPeer(participant);

    try{

        if(pc.signalingState !== "stable"){
            return;
        }

        const offer =
            await pc.createOffer({
                offerToReceiveAudio:true,
                offerToReceiveVideo:false
            });

        await pc.setLocalDescription(
            offer
        );

        sendVoiceSignal(
            participant,
            "offer",
            JSON.stringify(
                pc.localDescription
            )
        );

    }catch(error){

        console.log(
            "OFFER ERROR:",
            error
        );
    }
}

function sendVoiceSignal(
    participant,
    signalType,
    signalData
){

    if(!voiceRoomId){
        return;
    }

    voiceAjax({
        action:"send_signal",
        course_id:COURSE_ID,
        room_id:voiceRoomId,
        receiver_id:participant.user_id,
        receiver_type:participant.user_type,
        signal_type:signalType,
        signal_data:signalData
    },function(){});
}

function getVoiceSignals(){

    if(!voiceJoined || !voiceRoomId){
        return;
    }

    voiceAjax({
        action:"get_signals",
        course_id:COURSE_ID,
        room_id:voiceRoomId,
        after_id:voiceSignalId
    },async function(res){

        if(!res || !res.success || !Array.isArray(res.signals)){
            return;
        }

        for(
            const signal of res.signals
        ){

            voiceSignalId =
                Math.max(
                    voiceSignalId,
                    parseInt(signal.id,10)
                );

            try{
                await handleVoiceSignal(signal);
            }catch(error){
                console.log(
                    "SIGNAL HANDLE ERROR:",
                    error
                );
            }
        }
    });
}

async function handleVoiceSignal(signal){

    if(
        signal.sender_type === SENDER_TYPE &&
        parseInt(signal.sender_id,10) === USER_ID
    ){
        return;
    }

    const participant = {
        user_type:signal.sender_type,
        user_id:parseInt(signal.sender_id,10)
    };

    const key =
        getParticipantKey(
            participant.user_type,
            participant.user_id
        );

    voiceParticipantsCache[key] =
        participant;

    const pc =
        await createPeer(
            participant
        );

    let data;

    try{
        data =
            JSON.parse(
                signal.signal_data
            );
    }catch(e){
        return;
    }

    if(signal.signal_type === "offer"){

        if(
            pc.signalingState !== "stable" &&
            pc.signalingState !== "have-remote-offer"
        ){
            return;
        }

        await pc.setRemoteDescription(
            new RTCSessionDescription(data)
        );

        await flushPendingIce(key);

        const answer =
            await pc.createAnswer({
                offerToReceiveAudio:true,
                offerToReceiveVideo:false
            });

        await pc.setLocalDescription(
            answer
        );

        sendVoiceSignal(
            participant,
            "answer",
            JSON.stringify(
                pc.localDescription
            )
        );

        return;
    }

    if(signal.signal_type === "answer"){

        if(
            pc.signalingState === "have-local-offer"
        ){

            await pc.setRemoteDescription(
                new RTCSessionDescription(data)
            );

            await flushPendingIce(key);
        }

        return;
    }

    if(signal.signal_type === "ice"){

        const candidate =
            new RTCIceCandidate(data);

        if(
            pc.remoteDescription &&
            pc.remoteDescription.type
        ){

            try{
                await pc.addIceCandidate(
                    candidate
                );
            }catch(e){
                console.log(
                    "ICE ERROR:",
                    e
                );
            }

        }else{

            if(!pendingIceCandidates[key]){
                pendingIceCandidates[key] = [];
            }

            pendingIceCandidates[key].push(
                candidate
            );
        }
    }
}

function loadVoiceParticipants(){

    if(!voiceRoomId){
        return;
    }

    voiceAjax({
        action:"participants",
        course_id:COURSE_ID,
        room_id:voiceRoomId
    },function(res){

        if(!res || !res.success){
            return;
        }

        const list =
            Array.isArray(res.participants)
                ? res.participants
                : [];

        const currentKeys = {};

        list.forEach(function(participant){

            const key =
                getParticipantKey(
                    participant.user_type,
                    participant.user_id
                );

            currentKeys[key] =
                true;

            voiceParticipantsCache[key] =
                participant;
        });

        Object.keys(voiceParticipantsCache)
            .forEach(function(key){

                if(!currentKeys[key]){

                    const pc =
                        peerConnections[key];

                    if(pc){
                        try{
                            pc.close();
                        }catch(e){}
                    }

                    delete peerConnections[key];
                    delete pendingIceCandidates[key];

                    const audio =
                        document.getElementById(
                            "remote-audio-" + key
                        );

                    if(audio){
                        audio.pause();
                        audio.srcObject = null;
                        audio.remove();
                    }

                    delete voiceParticipantsCache[key];
                }
            });

        renderVoiceParticipants(list);

        list.forEach(function(participant){

            const isMe =
                participant.user_type === SENDER_TYPE &&
                parseInt(participant.user_id,10) === USER_ID;

            if(!isMe){
                connectToParticipant(participant);
            }

            if(isMe){

                const serverMic =
                    parseInt(
                        participant.mic_enabled,
                        10
                    ) === 1;

                if(
                    !serverMic &&
                    voiceMicEnabled
                ){

                    setLocalMic(false);

                    voiceMicEnabled = false;

                    $("#voiceMicButton")
                        .addClass("muted")
                        .html(
                            '<i class="bi bi-mic-mute-fill"></i> میکروفون بسته'
                        );
                }

                if(
                    serverMic &&
                    !voiceMicEnabled
                ){
                    setLocalMic(false);
                }
            }
        });
    });
}

function renderVoiceParticipants(list){

    let html = "";

    const teacher =
        list.find(
            x => x.user_type === "teacher"
        );

    if(teacher){

        html += `
        <div class="voice-teacher-card">

            <div class="voice-avatar teacher">
                <i class="bi bi-person-video3"></i>
            </div>

            <div class="voice-user-info">

                <div class="voice-user-name">
                    ${escapeHtml(teacher.full_name)}
                </div>

                <div class="voice-user-status">
                    معلم کلاس
                </div>

            </div>

            <div class="voice-mic-state ${
                parseInt(teacher.mic_enabled,10)
                    ? "on"
                    : "off"
            }">

                <i class="bi ${
                    parseInt(teacher.mic_enabled,10)
                        ? "bi-mic-fill"
                        : "bi-mic-mute-fill"
                }"></i>

            </div>

        </div>
        `;
    }

    const students =
        list.filter(
            x => x.user_type === "student"
        );

    html += `
        <div class="voice-participants-title">
            اعضای حاضر · ${list.length}
        </div>
    `;

    if(!students.length){

        html += `
        <div class="voice-empty">
            <i class="bi bi-people"></i>
            هنوز دانش‌آموزی وارد کلاس نشده است.
        </div>
        `;

    }else{

        students.forEach(function(p){

            const isMe =
                p.user_type === SENDER_TYPE &&
                parseInt(p.user_id,10) === USER_ID;

            const micEnabled =
                parseInt(
                    p.mic_enabled,
                    10
                ) === 1;

            let teacherAction = "";

            <?php if ($type === 1): ?>

            if(micEnabled){

                teacherAction = `
                    <button
                        class="voice-control-btn danger"
                        onclick="teacherMuteMic('${p.user_type}',${parseInt(p.user_id,10)})"
                        title="بستن میکروفون"
                    >
                        <i class="bi bi-mic-mute"></i>
                    </button>
                `;
            }

            <?php endif; ?>

            html += `
            <div class="voice-participant">

                <div class="voice-avatar">
                    <i class="bi bi-person"></i>
                </div>

                <div class="voice-user-info">

                    <div class="voice-user-name">
                        ${escapeHtml(p.full_name)}
                        ${isMe ? " (شما)" : ""}
                    </div>

                    <div class="voice-user-status">
                        دانش‌آموز
                    </div>

                </div>

                <div class="voice-participant-actions">

                    <div class="voice-mic-state ${
                        micEnabled
                            ? "on"
                            : "off"
                    }">

                        <i class="bi ${
                            micEnabled
                                ? "bi-mic-fill"
                                : "bi-mic-mute-fill"
                        }"></i>

                    </div>

                    ${teacherAction}

                </div>

            </div>
            `;
        });
    }

    $("#voiceParticipants")
        .html(html);

    $("#voiceCallStatus")
        .text(
            list.length +
            " نفر در کلاس صوتی"
        );
}

function setLocalMic(enabled){

    if(!localVoiceStream){
        return;
    }

    localVoiceStream
        .getAudioTracks()
        .forEach(function(track){
            track.enabled =
                !!enabled;
        });
}

function toggleMyVoiceMic(){

    if(!voiceJoined){
        return;
    }

    const next =
        !voiceMicEnabled;

    voiceAjax({
        action:"toggle_mic",
        course_id:COURSE_ID,
        room_id:voiceRoomId,
        enabled:next ? 1 : 0
    },function(res){

        if(!res || !res.success){

            Swal.fire({
                icon:"error",
                title:"خطا",
                text:res && res.message
                    ? res.message
                    : "تغییر وضعیت میکروفون انجام نشد."
            });

            return;
        }

        voiceMicEnabled =
            next;

        setLocalMic(next);

        if(next){

            $("#voiceMicButton")
                .removeClass("muted")
                .html(
                    '<i class="bi bi-mic-fill"></i> میکروفون'
                );

        }else{

            $("#voiceMicButton")
                .addClass("muted")
                .html(
                    '<i class="bi bi-mic-mute-fill"></i> میکروفون بسته'
                );
        }

        loadVoiceParticipants();
    });
}

function teacherMuteMic(
    targetType,
    targetId
){

    voiceAjax({
        action:"teacher_mute",
        course_id:COURSE_ID,
        room_id:voiceRoomId,
        target_type:targetType,
        target_id:targetId,
        enabled:0
    },function(res){

        if(!res || !res.success){

            Swal.fire({
                icon:"error",
                title:"خطا",
                text:res && res.message
                    ? res.message
                    : "بستن میکروفون انجام نشد."
            });

            return;
        }

        loadVoiceParticipants();
    });
}

function stopLocalVoice(){

    if(localVoiceStream){

        localVoiceStream
            .getTracks()
            .forEach(function(track){
                try{
                    track.stop();
                }catch(e){}
            });

        localVoiceStream = null;
    }

    Object.keys(peerConnections)
        .forEach(function(key){

            try{
                peerConnections[key].close();
            }catch(e){}

            delete peerConnections[key];

            const audio =
                document.getElementById(
                    "remote-audio-" + key
                );

            if(audio){

                try{
                    audio.pause();
                }catch(e){}

                audio.srcObject = null;
                audio.remove();
            }
        });

    Object.keys(pendingIceCandidates)
        .forEach(function(key){
            delete pendingIceCandidates[key];
        });
}

function forceLeaveVoiceRoom(){

    voiceJoined = false;

    clearInterval(voicePollTimer);
    clearInterval(voiceParticipantTimer);
    clearInterval(voiceHeartbeatTimer);

    clearTimeout(voiceReconnectTimer);

    voicePollTimer = null;
    voiceParticipantTimer = null;
    voiceHeartbeatTimer = null;

    stopLocalVoice();

    voiceParticipantsCache = {};
    voiceSignalId = 0;

    closeVoicePanel();
}

function leaveVoiceRoom(){

    if(!voiceRoomId){

        closeVoicePanel();
        return;
    }

    voiceAjax({
        action:"leave_room",
        course_id:COURSE_ID,
        room_id:voiceRoomId
    },function(){

        forceLeaveVoiceRoom();

        Swal.fire({
            icon:"success",
            title:"خارج شدید",
            timer:800,
            showConfirmButton:false
        });
    });
}

function endVoiceRoom(){

    if(!voiceRoomId){
        return;
    }

    Swal.fire({
        title:"پایان کلاس صوتی؟",
        text:"با پایان کلاس، تمام اعضا از کلاس صوتی خارج می‌شوند.",
        icon:"warning",
        showCancelButton:true,
        confirmButtonText:"پایان کلاس",
        cancelButtonText:"انصراف"
    }).then(function(result){

        if(!result.isConfirmed){
            return;
        }

        voiceAjax({
            action:"end_room",
            course_id:COURSE_ID,
            room_id:voiceRoomId
        },function(res){

            if(res && res.success){

                forceLeaveVoiceRoom();

                voiceRoomId = null;

                $("#voiceStartBanner")
                    .removeClass("show");

                $("#voiceCallBtn")
                    .addClass("teacher-visible");

                Swal.fire({
                    icon:"success",
                    title:"کلاس صوتی پایان یافت",
                    timer:1000,
                    showConfirmButton:false
                });

            }else{

                Swal.fire({
                    icon:"error",
                    title:"خطا",
                    text:res && res.message
                        ? res.message
                        : "پایان کلاس انجام نشد."
                });
            }
        });
    });
}

window.addEventListener(
    "beforeunload",
    function(){

        if(
            voiceJoined &&
            voiceRoomId
        ){

            const data =
                new URLSearchParams();

            data.append(
                "action",
                "leave_room"
            );

            data.append(
                "course_id",
                COURSE_ID
            );

            data.append(
                "room_id",
                voiceRoomId
            );

            try{
                navigator.sendBeacon(
                    "voice_action.php",
                    data
                );
            }catch(e){}
        }
    }
);

function escapeHtml(text){

    return $("<div>")
        .text(text ?? "")
        .html();
}

function formatSize(bytes){

    bytes = Number(bytes);

    if(bytes < 1024)
        return bytes + " B";

    if(bytes < 1024 * 1024)
        return (
            bytes / 1024
        ).toFixed(1) + " KB";

    if(bytes < 1024 * 1024 * 1024)
        return (
            bytes / 1024 / 1024
        ).toFixed(1) + " MB";

    return (
        bytes / 1024 / 1024 / 1024
    ).toFixed(1) + " GB";
}

function getFileIcon(name){

    const ext =
        name
            .split(".")
            .pop()
            .toLowerCase();

    if(
        ["jpg","jpeg","png","gif","webp"]
            .includes(ext)
    )
        return "bi-file-earmark-image";

    if(
        ["mp3","wav","ogg","m4a","webm"]
            .includes(ext)
    )
        return "bi-file-earmark-music";

    if(
        ["mp4","avi","mkv","mov"]
            .includes(ext)
    )
        return "bi-file-earmark-play";

    if(ext === "pdf")
        return "bi-file-earmark-pdf";

    if(["ppt","pptx"].includes(ext))
        return "bi-file-earmark-slides";

    if(["doc","docx"].includes(ext))
        return "bi-file-earmark-word";

    if(["xls","xlsx"].includes(ext))
        return "bi-file-earmark-excel";

    if(["zip","rar","7z"].includes(ext))
        return "bi-file-earmark-zip";

    return "bi-file-earmark";
}

function loadMessages(scrollBottom = false){

    $.ajax({
        url:"get_messages.php",
        type:"GET",
        data:{
            course_id:COURSE_ID,
            after_id:lastMessageId
        },
        dataType:"json",
        success:function(res){

            if(!res || !res.success){
                return;
            }

            if(res.messages && res.messages.length){

                res.messages.forEach(function(msg){

                    renderMessage(msg);

                    lastMessageId =
                        Math.max(
                            lastMessageId,
                            parseInt(msg.id,10)
                        );
                });

                if(scrollBottom){
                    scrollBottomPage();
                }
            }
        }
    });
}

function renderMessage(msg){

    if(
        $("#message-" + msg.id).length
    ){
        return;
    }

    const mine =
        parseInt(msg.sender_id,10) === USER_ID &&
        msg.sender_type === SENDER_TYPE;

    const senderClass =
        msg.sender_type === "teacher"
            ? "teacher"
            : "";

    const mineClass =
        mine
            ? "mine"
            : "other";

    const badge =
        msg.sender_type === "teacher"
            ? '<span class="teacher-badge">معلم</span>'
            : "";

    let html = `
    <div class="message-row">

        <div
            class="message ${senderClass} ${mineClass}"
            id="message-${msg.id}"
            data-id="${msg.id}"
            data-owner="${mine ? 1 : 0}"
            data-type="${msg.content_type || "text"}"
        >

            <div class="sender-name">
                ${escapeHtml(msg.sender_name)}
            </div>
    `;

    if(msg.reply){

        html += `
        <div class="reply-box">

            <div class="reply-name">
                ${escapeHtml(msg.reply.sender_name)}
            </div>

            <div class="reply-content">
                ${escapeHtml(msg.reply.preview)}
            </div>

        </div>
        `;
    }

    if(msg.content_type === "text"){

        html += `
        <div class="message-text">
            ${escapeHtml(msg.message)}
        </div>
        `;
    }

    if(msg.content_type === "audio"){

        html += `
        <div class="audio-message">

            <audio
                class="audio-player"
                controls
                preload="metadata"
            >
                <source src="${escapeHtml(msg.audio_path)}">
            </audio>

        </div>
        `;
    }

    if(msg.content_type === "file"){

        html += `
        <div class="file-card">

            <div class="file-icon">
                <i class="bi ${getFileIcon(msg.file_name)}"></i>
            </div>

            <div class="file-info">

                <div class="file-name">
                    ${escapeHtml(msg.file_name)}
                </div>

                <div class="file-size">
                    ${formatSize(msg.file_size)}
                </div>

            </div>

            <a
                class="file-download"
                href="${escapeHtml(msg.file_path)}"
                target="_blank"
                download
            >
                <i class="bi bi-download"></i>
            </a>

        </div>
        `;
    }

    html += `
            <div class="message-meta">

                ${badge}

                <span class="message-time">
                    ${escapeHtml(msg.time)}
                </span>

            </div>

        </div>

    </div>
    `;

    $("#messagesList")
        .append(html);
}

function sendMessage(){

    const text =
        $("#messageInput")
            .val()
            .trim();

    if(!text){
        return;
    }

    $.ajax({
        url:"chat_action.php",
        type:"POST",
        data:{
            action:"send_message",
            course_id:COURSE_ID,
            message:text,
            reply_to_id:replyToId || ""
        },
        dataType:"json",
        success:function(res){

            if(res && res.success){

                $("#messageInput")
                    .val("")
                    .trigger("input");

                cancelReply();

                if(res.message){

                    renderMessage(
                        res.message
                    );

                    lastMessageId =
                        Math.max(
                            lastMessageId,
                            parseInt(
                                res.message.id,
                                10
                            )
                        );
                }

                scrollBottomPage();

            }else{

                Swal.fire({
                    icon:"error",
                    title:"خطا",
                    text:res && res.message
                        ? res.message
                        : "ارسال پیام انجام نشد."
                });
            }
        }
    });
}

$("#sendBtn").click(sendMessage);

$("#messageInput").keydown(function(e){

    if(
        e.key === "Enter" &&
        !e.shiftKey
    ){

        e.preventDefault();

        sendMessage();
    }
});

$("#messageInput").on(
    "input",
    function(){

        this.style.height =
            "auto";

        this.style.height =
            Math.min(
                this.scrollHeight,
                130
            ) + "px";

        const hasText =
            $(this)
                .val()
                .trim()
                .length > 0;

        $("#sendBtn")
            .toggle(hasText);

        $("#voiceBtn,#fileBtn")
            .toggle(!hasText);
    }
);

$("#fileBtn").click(function(){
    $("#fileInput").click();
});

$("#fileInput").change(function(){

    const file =
        this.files[0];

    if(!file){
        return;
    }

    if(
        file.size >
        100 * 1024 * 1024
    ){

        Swal.fire({
            icon:"error",
            title:"حجم فایل زیاد است",
            text:"حداکثر حجم فایل 100 مگابایت است."
        });

        this.value = "";

        return;
    }

    uploadFile(file);

    this.value = "";
});

function uploadFile(file){

    const tempId =
        "upload-" + Date.now();

    const html = `
    <div class="message-row" id="${tempId}">

        <div class="message mine">

            <div class="upload-card">

                <div class="upload-top">

                    <div class="upload-icon">
                        <i class="bi ${getFileIcon(file.name)}"></i>
                    </div>

                    <div class="upload-info">

                        <div class="upload-name">
                            ${escapeHtml(file.name)}
                        </div>

                        <div class="upload-size">
                            ${formatSize(file.size)}
                        </div>

                    </div>

                    <button
                        class="upload-cancel"
                        onclick="cancelUpload()"
                    >
                        <i class="bi bi-x-lg"></i>
                    </button>

                </div>

                <div class="upload-progress">

                    <div
                        class="upload-progress-bar"
                        id="${tempId}-bar"
                    ></div>

                </div>

                <div class="upload-status">

                    <span id="${tempId}-percent">
                        0%
                    </span>

                    <span id="${tempId}-sent">
                        0 B / ${formatSize(file.size)}
                    </span>

                </div>

            </div>

        </div>

    </div>
    `;

    $("#messagesList")
        .append(html);

    currentUploadElement =
        tempId;

    scrollBottomPage();

    const formData =
        new FormData();

    formData.append(
        "action",
        "send_file"
    );

    formData.append(
        "course_id",
        COURSE_ID
    );

    formData.append(
        "reply_to_id",
        replyToId || ""
    );

    formData.append(
        "file",
        file
    );

    currentUploadXHR =
        new XMLHttpRequest();

    currentUploadXHR.open(
        "POST",
        "chat_action.php",
        true
    );

    currentUploadXHR.upload.onprogress =
        function(e){

            if(!e.lengthComputable){
                return;
            }

            const percent =
                Math.round(
                    (e.loaded / e.total) *
                    100
                );

            $("#" + tempId + "-bar")
                .css(
                    "width",
                    percent + "%"
                );

            $("#" + tempId + "-percent")
                .text(
                    percent + "%"
                );

            $("#" + tempId + "-sent")
                .text(
                    formatSize(e.loaded) +
                    " / " +
                    formatSize(e.total)
                );
        };

    currentUploadXHR.onload =
        function(){

            const xhr =
                currentUploadXHR;

            currentUploadXHR =
                null;

            currentUploadElement =
                null;

            let res = {};

            try{

                res =
                    JSON.parse(
                        xhr.responseText
                    );

            }catch(e){

                console.log(
                    xhr.responseText
                );
            }

            if(!res.success){

                $("#" + tempId)
                    .remove();

                Swal.fire({
                    icon:"error",
                    title:"خطا در ارسال فایل",
                    text:
                        res.message ||
                        "خطا در ارسال فایل"
                });

                return;
            }

            $("#" + tempId)
                .remove();

            cancelReply();

            if(res.message){

                renderMessage(
                    res.message
                );

                lastMessageId =
                    Math.max(
                        lastMessageId,
                        parseInt(
                            res.message.id,
                            10
                        )
                    );
            }

            scrollBottomPage();
        };

    currentUploadXHR.onerror =
        function(){

            currentUploadXHR =
                null;

            currentUploadElement =
                null;

            $("#" + tempId)
                .remove();

            Swal.fire({
                icon:"error",
                title:"خطا در ارسال فایل",
                text:"ارتباط با سرور برقرار نشد."
            });
        };

    currentUploadXHR.onabort =
        function(){

            currentUploadXHR =
                null;

            currentUploadElement =
                null;

            $("#" + tempId)
                .remove();
        };

    currentUploadXHR.send(
        formData
    );
}

function cancelUpload(){

    if(currentUploadXHR){

        currentUploadXHR.abort();

        currentUploadXHR = null;
        currentUploadElement = null;
    }
}

$("#voiceBtn").click(async function(){

    if(
        !navigator.mediaDevices ||
        !navigator.mediaDevices.getUserMedia
    ){

        Swal.fire({
            icon:"error",
            title:"دسترسی به میکروفون",
            text:getMicrophoneErrorMessage()
        });

        return;
    }

    try{

        const stream =
            await navigator.mediaDevices.getUserMedia({
                audio:true
            });

        let mimeType = "";

        const formats = [
            "audio/webm;codecs=opus",
            "audio/webm",
            "audio/ogg;codecs=opus",
            "audio/ogg",
            "audio/mp4",
            "audio/mpeg",
            "audio/wav"
        ];

        for(
            const format of formats
        ){

            if(
                MediaRecorder
                    .isTypeSupported(format)
            ){

                mimeType = format;

                break;
            }
        }

        mediaRecorder =
            mimeType
                ? new MediaRecorder(
                    stream,
                    {
                        mimeType:mimeType
                    }
                )
                : new MediaRecorder(stream);

        audioChunks = [];

        mediaRecorder.ondataavailable =
            function(e){

                if(e.data.size > 0){
                    audioChunks.push(e.data);
                }
            };

        mediaRecorder.onstop =
            function(){

                stream
                    .getTracks()
                    .forEach(
                        track =>
                            track.stop()
                    );

                recordedAudioBlob =
                    new Blob(
                        audioChunks,
                        {
                            type:
                                mediaRecorder.mimeType ||
                                "audio/webm"
                        }
                    );

                const audioURL =
                    URL.createObjectURL(
                        recordedAudioBlob
                    );

                $("#previewAudio")
                    .attr(
                        "src",
                        audioURL
                    );

                $("#audioPreview")
                    .css(
                        "display",
                        "flex"
                    );

                $("#recordingBox")
                    .hide();

                $("#voiceBtn,#fileBtn")
                    .show();

                $("#messageInput")
                    .show();

                clearInterval(
                    recordingInterval
                );
            };

        mediaRecorder.start();

        recordingSeconds = 0;

        updateRecordingTime();

        $("#recordingBox")
            .css(
                "display",
                "flex"
            );

        $("#voiceBtn,#fileBtn")
            .hide();

        $("#messageInput")
            .hide();

        recordingInterval =
            setInterval(
                function(){

                    recordingSeconds++;

                    updateRecordingTime();

                },
                1000
            );

    }catch(e){

        Swal.fire({
            icon:"error",
            title:"دسترسی به میکروفون",
            text:getMicrophoneErrorMessage(e)
        });
    }
});

function updateRecordingTime(){

    const min =
        Math.floor(
            recordingSeconds / 60
        )
        .toString()
        .padStart(2,"0");

    const sec =
        (recordingSeconds % 60)
            .toString()
            .padStart(2,"0");

    $("#recordingTime")
        .text(
            min + ":" + sec
        );
}

$("#cancelRecording").click(function(){

    if(
        mediaRecorder &&
        mediaRecorder.state !==
        "inactive"
    ){

        mediaRecorder.stop();
    }

    $("#recordingBox")
        .hide();

    $("#messageInput")
        .show();

    $("#voiceBtn,#fileBtn")
        .show();

    clearInterval(
        recordingInterval
    );
});

$("#cancelRecordedAudio").click(function(){

    recordedAudioBlob = null;

    $("#previewAudio")
        .attr("src","");

    $("#audioPreview")
        .hide();

    $("#messageInput")
        .show();

    $("#voiceBtn,#fileBtn")
        .show();
});

$("#sendRecordedAudio").click(function(){

    if(!recordedAudioBlob){
        return;
    }

    const formData =
        new FormData();

    formData.append(
        "action",
        "send_audio"
    );

    formData.append(
        "course_id",
        COURSE_ID
    );

    formData.append(
        "reply_to_id",
        replyToId || ""
    );

    let extension = "webm";

    if(
        recordedAudioBlob.type
            .includes("ogg")
    )
        extension = "ogg";
    else if(
        recordedAudioBlob.type
            .includes("mp4")
    )
        extension = "mp4";
    else if(
        recordedAudioBlob.type
            .includes("mpeg")
    )
        extension = "mp3";

    formData.append(
        "audio",
        recordedAudioBlob,
        "voice_" +
        Date.now() +
        "." +
        extension
    );

    Swal.fire({
        title:"در حال ارسال ویس...",
        allowOutsideClick:false,
        didOpen:()=>{
            Swal.showLoading();
        }
    });

    $.ajax({

        url:"chat_action.php",
        type:"POST",
        data:formData,
        processData:false,
        contentType:false,
        dataType:"json",

        success:function(res){

            Swal.close();

            if(res && res.success){

                recordedAudioBlob =
                    null;

                $("#previewAudio")
                    .attr("src","");

                $("#audioPreview")
                    .hide();

                $("#messageInput")
                    .show();

                $("#voiceBtn,#fileBtn")
                    .show();

                cancelReply();

                if(res.message){

                    renderMessage(
                        res.message
                    );

                    lastMessageId =
                        Math.max(
                            lastMessageId,
                            parseInt(
                                res.message.id,
                                10
                            )
                        );
                }

                scrollBottomPage();

            }else{

                Swal.fire({
                    icon:"error",
                    title:"خطا در ارسال ویس",
                    text:res && res.message
                        ? res.message
                        : "ارسال ویس انجام نشد."
                });
            }
        },

        error:function(xhr){

            Swal.close();

            console.log(
                xhr.responseText
            );

            Swal.fire({
                icon:"error",
                title:"خطا در ارسال ویس",
                text:"پاسخ نامعتبر از سرور دریافت شد."
            });
        }
    });
});

function scrollBottomPage(){

    const area =
        $("#messagesArea")[0];

    if(area){
        area.scrollTop =
            area.scrollHeight;
    }
}

function setReply(msg){

    replyToId = msg.id;

    $("#replyPreviewTitle")
        .text(
            "پاسخ به " +
            msg.sender_name
        );

    $("#replyPreviewText")
        .text(
            msg.preview ||
            msg.message ||
            "محتوا"
        );

    $("#replyPreview")
        .css(
            "display",
            "flex"
        );

    $("#messageInput")
        .focus();
}

function cancelReply(){

    replyToId = null;

    $("#replyPreview")
        .hide();
}

function copyMessage(id){

    const el =
        $("#message-" + id);

    let text =
        el.find(
            ".message-text"
        ).text();

    if(!text){

        text =
            el.find(
                ".file-name"
            ).text();

        if(!text){
            text = "🎤 پیام صوتی";
        }
    }

    if(
        navigator.clipboard &&
        navigator.clipboard.writeText
    ){

        navigator.clipboard
            .writeText(text);

    }else{

        const temp =
            $("<textarea>")
                .val(text)
                .appendTo("body");

        temp.select();

        document.execCommand(
            "copy"
        );

        temp.remove();
    }

    Swal.fire({
        icon:"success",
        title:"کپی شد",
        timer:900,
        showConfirmButton:false
    });
}

function deleteMessage(id){

    Swal.fire({

        title:"حذف پیام؟",

        text:"این عملیات قابل بازگشت نیست.",

        icon:"warning",

        showCancelButton:true,

        confirmButtonText:"حذف",

        cancelButtonText:"انصراف"

    }).then(function(result){

        if(!result.isConfirmed){
            return;
        }

        $.ajax({

            url:"chat_action.php",

            type:"POST",

            data:{
                action:"delete_message",
                id:id,
                course_id:COURSE_ID
            },

            dataType:"json",

            success:function(res){

                if(res && res.success){

                    $("#message-" + id)
                        .closest(".message-row")
                        .remove();

                    Swal.fire({
                        icon:"success",
                        title:"حذف شد",
                        timer:800,
                        showConfirmButton:false
                    });

                }else{

                    Swal.fire({
                        icon:"error",
                        title:"خطا",
                        text:res && res.message
                            ? res.message
                            : "حذف انجام نشد."
                    });
                }
            }
        });
    });
}

function editMessage(id){

    const el =
        $("#message-" + id);

    const oldText =
        el.find(
            ".message-text"
        ).text();

    Swal.fire({

        title:"ویرایش پیام",

        input:"textarea",

        inputValue:oldText,

        showCancelButton:true,

        confirmButtonText:"ذخیره",

        cancelButtonText:"انصراف"

    }).then(function(result){

        if(
            !result.isConfirmed ||
            !result.value ||
            !result.value.trim()
        ){
            return;
        }

        $.ajax({

            url:"chat_action.php",

            type:"POST",

            data:{
                action:"edit_message",
                id:id,
                course_id:COURSE_ID,
                message:result.value
            },

            dataType:"json",

            success:function(res){

                if(res && res.success){

                    el.find(
                        ".message-text"
                    ).text(
                        result.value
                    );

                }else{

                    Swal.fire({
                        icon:"error",
                        title:"خطا",
                        text:res && res.message
                            ? res.message
                            : "ویرایش انجام نشد."
                    });
                }
            }
        });
    });
}

function openContextMenu(e,id){

    selectedMessage = id;

    const el =
        $("#message-" + id);

    const owner =
        parseInt(
            el.attr("data-owner"),
            10
        ) === 1;

    const canDelete =
        owner ||
        USER_TYPE === 1;

    $("#editAction").toggle(
        owner &&
        el.attr("data-type") === "text"
    );

    $("#deleteAction")
        .toggle(canDelete);

    const menu =
        $("#contextMenu");

    const left =
        Math.min(
            e.clientX,
            window.innerWidth - 170
        );

    const top =
        Math.min(
            e.clientY,
            window.innerHeight - 180
        );

    menu.css({
        top:top + "px",
        left:left + "px"
    }).show();
}

$(document).on(
    "contextmenu",
    ".message",
    function(e){

        e.preventDefault();

        openContextMenu(
            e,
            this.dataset.id
        );
    }
);

$("#copyAction").click(function(){

    if(selectedMessage){
        copyMessage(selectedMessage);
    }

    $("#contextMenu")
        .hide();
});

$("#replyAction").click(function(){

    if(selectedMessage){

        const el =
            $("#message-" + selectedMessage);

        setReply({

            id:selectedMessage,

            sender_name:
                el.find(
                    ".sender-name"
                ).text(),

            message:
                el.find(
                    ".message-text"
                ).text(),

            preview:
                el.find(
                    ".message-text"
                ).text()
                ||
                el.find(
                    ".file-name"
                ).text()
                ||
                "🎤 پیام صوتی"
        });
    }

    $("#contextMenu")
        .hide();
});

$("#deleteAction").click(function(){

    if(selectedMessage){
        deleteMessage(selectedMessage);
    }

    $("#contextMenu")
        .hide();
});

$("#editAction").click(function(){

    if(selectedMessage){
        editMessage(selectedMessage);
    }

    $("#contextMenu")
        .hide();
});

$(document).click(function(e){

    if(
        !$(e.target)
            .closest("#contextMenu")
            .length
    ){

        $("#contextMenu")
            .hide();
    }
});

let touchTimer;

$(document).on(
    "touchstart",
    ".message",
    function(){

        const id =
            this.dataset.id;

        touchTimer =
            setTimeout(
                function(){

                    selectedMessage = id;

                    const element =
                        document.getElementById(
                            "message-" + id
                        );

                    if(!element){
                        return;
                    }

                    const rect =
                        element.getBoundingClientRect();

                    openContextMenu(
                        {
                            clientX:
                                Math.min(
                                    rect.left,
                                    window.innerWidth - 160
                                ),
                            clientY:
                                Math.min(
                                    rect.top,
                                    window.innerHeight - 180
                                )
                        },
                        id
                    );

                },
                600
            );
    }
);

$(document).on(
    "touchend touchmove",
    ".message",
    function(){

        clearTimeout(
            touchTimer
        );
    }
);

loadMessages(true);

checkVoiceRoom();

setInterval(
    function(){
        loadMessages(false);
    },
    2500
);

setInterval(
    checkVoiceRoom,
    3000
);

</script>

</body>
</html>