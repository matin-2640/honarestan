<?php
session_start();
require_once "../connect.php";

if (!isset($_SESSION["ID"], $_SESSION["type"]) || !in_array((int) $_SESSION["type"], [0, 1])) {
    header("Location: ../login.php");
    exit;
}

$course_id = isset($_GET["course_id"]) ? (int) $_GET["course_id"] : 0;

if ($course_id <= 0) {
    exit("درس نامعتبر است.");
}

$type = (int) $_SESSION["type"];
$user_id = (int) $_SESSION["ID"];

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
    $stmt->execute([$user_id, $course["Co_classID"]]);

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

    if ((int) $course["Co_teacherID"] !== $user_id) {
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= htmlspecialchars($course["Co_name"]) ?></title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="../js/jquery-1.10.2.min.js"></script>
    <script src="../js/sweetalert2.min.js"></script>
    <link rel="stylesheet" href="../js/sweetalert2.min.css">
    <link rel="stylesheet" href="../styles/font.css">
    <link rel="icon" href="../images/icons/rahdanesh.png">
    <link rel="stylesheet" href="styles/chat.css">
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

        </header>

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

                <textarea id="messageInput" class="message-input" placeholder="پیام خود را بنویسید..."
                    rows="1"></textarea>

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

        function escapeHtml(text) {
            return $("<div>").text(text ?? "").html();
        }

        function formatSize(bytes) {

            bytes = Number(bytes);

            if (bytes < 1024)
                return bytes + " B";

            if (bytes < 1024 * 1024)
                return (bytes / 1024).toFixed(1) + " KB";

            if (bytes < 1024 * 1024 * 1024)
                return (bytes / 1024 / 1024).toFixed(1) + " MB";

            return (bytes / 1024 / 1024 / 1024).toFixed(1) + " GB";
        }

        function getFileIcon(name) {

            const ext =
                name.split(".").pop().toLowerCase();

            if (["jpg", "jpeg", "png", "gif", "webp"].includes(ext))
                return "bi-file-earmark-image";

            if (["mp3", "wav", "ogg", "m4a", "webm"].includes(ext))
                return "bi-file-earmark-music";

            if (["mp4", "avi", "mkv", "mov"].includes(ext))
                return "bi-file-earmark-play";

            if (ext === "pdf")
                return "bi-file-earmark-pdf";

            if (["ppt", "pptx"].includes(ext))
                return "bi-file-earmark-slides";

            if (["doc", "docx"].includes(ext))
                return "bi-file-earmark-word";

            if (["xls", "xlsx"].includes(ext))
                return "bi-file-earmark-excel";

            if (["zip", "rar", "7z"].includes(ext))
                return "bi-file-earmark-zip";

            return "bi-file-earmark";
        }

        function loadMessages(scrollBottom = false) {

            $.ajax({

                url: "get_messages.php",

                type: "GET",

                data: {
                    course_id: COURSE_ID,
                    after_id: lastMessageId
                },

                dataType: "json",

                success: function (res) {

                    if (!res.success)
                        return;

                    if (res.messages.length) {

                        res.messages.forEach(function (msg) {

                            renderMessage(msg);

                            lastMessageId =
                                Math.max(
                                    lastMessageId,
                                    parseInt(msg.id)
                                );
                        });

                        if (scrollBottom)
                            scrollBottomPage();
                    }
                }
            });
        }

        function renderMessage(msg) {

            if ($("#message-" + msg.id).length)
                return;

            const mine =
                parseInt(msg.sender_id) === USER_ID &&
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
                data-type="${msg.content_type || "text"}">

                <div class="sender-name">
                    ${escapeHtml(msg.sender_name)}
                </div>
    `;

            if (msg.reply) {

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

            if (msg.content_type === "text") {

                html += `
            <div class="message-text">
                ${escapeHtml(msg.message)}
            </div>
        `;
            }

            if (msg.content_type === "audio") {

                html += `
            <div class="audio-message">

                <audio
                    class="audio-player"
                    controls
                    preload="metadata">

                    <source src="${escapeHtml(msg.audio_path)}">

                </audio>

            </div>
        `;
            }

            if (msg.content_type === "file") {

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
                    download>

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

            $("#messagesList").append(html);
        }

        function sendMessage() {

            const text =
                $("#messageInput").val().trim();

            if (!text)
                return;

            $.ajax({

                url: "chat_action.php",

                type: "POST",

                data: {
                    action: "send_message",
                    course_id: COURSE_ID,
                    message: text,
                    reply_to_id: replyToId || ""
                },

                dataType: "json",

                success: function (res) {

                    if (res.success) {

                        $("#messageInput")
                            .val("")
                            .trigger("input");

                        cancelReply();

                        if (res.message) {

                            renderMessage(res.message);

                            lastMessageId =
                                Math.max(
                                    lastMessageId,
                                    parseInt(res.message.id)
                                );
                        }

                        scrollBottomPage();

                    } else {

                        Swal.fire({
                            icon: "error",
                            title: "خطا",
                            text: res.message,
                            confirmButtonText: "باشه"
                        });
                    }
                }
            });
        }

        $("#sendBtn").click(sendMessage);

        $("#messageInput").keydown(function (e) {

            if (e.key === "Enter" && !e.shiftKey) {

                e.preventDefault();

                sendMessage();
            }
        });

        $("#messageInput").on("input", function () {

            this.style.height = "auto";

            this.style.height =
                Math.min(this.scrollHeight, 130) + "px";

            const hasText =
                $(this).val().trim().length > 0;

            $("#sendBtn").toggle(hasText);

            $("#voiceBtn,#fileBtn").toggle(!hasText);
        });

        $("#fileBtn").click(function () {
            $("#fileInput").click();
        });

        $("#fileInput").change(function () {

            const file = this.files[0];

            if (!file)
                return;

            if (file.size > 100 * 1024 * 1024) {

                Swal.fire({
                    icon: "error",
                    title: "حجم فایل زیاد است",
                    text: "حداکثر حجم فایل 100 مگابایت است.",
                    confirmButtonText: "باشه"
                });

                this.value = "";

                return;
            }

            uploadFile(file);

            this.value = "";
        });

        function uploadFile(file) {

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
                            onclick="cancelUpload()">

                            <i class="bi bi-x-lg"></i>

                        </button>

                    </div>

                    <div class="upload-progress">

                        <div
                            class="upload-progress-bar"
                            id="${tempId}-bar">
                        </div>

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

            $("#messagesList").append(html);

            currentUploadElement = tempId;

            scrollBottomPage();

            const formData = new FormData();

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
                function (e) {

                    if (!e.lengthComputable)
                        return;

                    const percent =
                        Math.round(
                            (e.loaded / e.total) * 100
                        );

                    $("#" + tempId + "-bar")
                        .css("width", percent + "%");

                    $("#" + tempId + "-percent")
                        .text(percent + "%");

                    $("#" + tempId + "-sent")
                        .text(
                            formatSize(e.loaded)
                            + " / "
                            + formatSize(e.total)
                        );
                };

            currentUploadXHR.onload =
                function () {

                    const xhr =
                        currentUploadXHR;

                    currentUploadXHR = null;
                    currentUploadElement = null;

                    let res = {};

                    try {
                        res =
                            JSON.parse(
                                xhr.responseText
                            );
                    } catch (e) {

                        console.log(
                            "SERVER RESPONSE:",
                            xhr.responseText
                        );
                    }

                    if (!res.success) {

                        $("#" + tempId).remove();

                        Swal.fire({
                            icon: "error",
                            title: "خطا در ارسال فایل",
                            text:
                                res.message ||
                                "پاسخ نامعتبر از سرور دریافت شد.",
                            confirmButtonText: "باشه"
                        });

                        return;
                    }

                    $("#" + tempId).remove();

                    cancelReply();

                    if (res.message) {

                        renderMessage(res.message);

                        lastMessageId =
                            Math.max(
                                lastMessageId,
                                parseInt(res.message.id)
                            );
                    }

                    scrollBottomPage();
                };

            currentUploadXHR.onerror =
                function () {

                    currentUploadXHR = null;
                    currentUploadElement = null;

                    $("#" + tempId).remove();

                    Swal.fire({
                        icon: "error",
                        title: "خطا در ارسال فایل",
                        text: "ارتباط با سرور برقرار نشد.",
                        confirmButtonText: "باشه"
                    });
                };

            currentUploadXHR.onabort =
                function () {

                    currentUploadXHR = null;
                    currentUploadElement = null;

                    $("#" + tempId).remove();

                    Swal.fire({
                        icon: "info",
                        title: "ارسال لغو شد",
                        timer: 1000,
                        showConfirmButton: false
                    });
                };

            currentUploadXHR.send(formData);
        }

        function cancelUpload() {

            if (currentUploadXHR) {

                currentUploadXHR.abort();

                currentUploadXHR = null;
                currentUploadElement = null;
            }
        }

        $("#voiceBtn").click(async function () {

            if (
                !navigator.mediaDevices ||
                !navigator.mediaDevices.getUserMedia
            ) {

                Swal.fire({
                    icon: "error",
                    title: "عدم پشتیبانی",
                    text: "مرورگر شما از ضبط صدا پشتیبانی نمی‌کند.",
                    confirmButtonText: "باشه"
                });

                return;
            }

            try {

                const stream =
                    await navigator.mediaDevices.getUserMedia({
                        audio: true
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

                for (const format of formats) {

                    if (
                        MediaRecorder.isTypeSupported(format)
                    ) {

                        mimeType = format;
                        break;
                    }
                }

                mediaRecorder =
                    mimeType
                        ? new MediaRecorder(
                            stream,
                            { mimeType: mimeType }
                        )
                        : new MediaRecorder(stream);

                audioChunks = [];

                mediaRecorder.ondataavailable =
                    function (e) {

                        if (e.data.size > 0)
                            audioChunks.push(e.data);
                    };

                mediaRecorder.onstop =
                    function () {

                        stream
                            .getTracks()
                            .forEach(
                                track => track.stop()
                            );

                        recordedAudioBlob =
                            new Blob(
                                audioChunks,
                                {
                                    type:
                                        mediaRecorder.mimeType
                                        || "audio/webm"
                                }
                            );

                        const audioURL =
                            URL.createObjectURL(
                                recordedAudioBlob
                            );

                        $("#previewAudio")
                            .attr("src", audioURL);

                        $("#audioPreview")
                            .css("display", "flex");

                        $("#recordingBox").hide();

                        $("#voiceBtn,#fileBtn").show();

                        $("#messageInput").show();

                        clearInterval(
                            recordingInterval
                        );
                    };

                mediaRecorder.start();

                recordingSeconds = 0;

                updateRecordingTime();

                $("#recordingBox")
                    .css("display", "flex");

                $("#voiceBtn,#fileBtn").hide();

                $("#messageInput").hide();

                recordingInterval =
                    setInterval(function () {

                        recordingSeconds++;

                        updateRecordingTime();

                    }, 1000);

            } catch (e) {

                Swal.fire({
                    icon: "error",
                    title: "دسترسی به میکروفون",
                    text: "اجازه استفاده از میکروفون داده نشده است.",
                    confirmButtonText: "باشه"
                });
            }
        });

        function updateRecordingTime() {

            const min =
                Math.floor(
                    recordingSeconds / 60
                ).toString().padStart(2, "0");

            const sec =
                (recordingSeconds % 60)
                    .toString().padStart(2, "0");

            $("#recordingTime")
                .text(min + ":" + sec);
        }

        $("#cancelRecording").click(function () {

            if (
                mediaRecorder &&
                mediaRecorder.state !== "inactive"
            ) {

                mediaRecorder.stop();
            }

            $("#recordingBox").hide();

            $("#messageInput").show();

            $("#voiceBtn,#fileBtn").show();

            clearInterval(recordingInterval);
        });

        $("#cancelRecordedAudio").click(function () {

            recordedAudioBlob = null;

            $("#previewAudio").attr("src", "");

            $("#audioPreview").hide();

            $("#messageInput").show();

            $("#voiceBtn,#fileBtn").show();
        });

        $("#sendRecordedAudio").click(function () {

            if (!recordedAudioBlob)
                return;

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

            if (
                recordedAudioBlob.type.includes("ogg")
            ) {
                extension = "ogg";
            } else if (
                recordedAudioBlob.type.includes("mp4")
            ) {
                extension = "mp4";
            } else if (
                recordedAudioBlob.type.includes("mpeg")
            ) {
                extension = "mp3";
            }

            formData.append(
                "audio",
                recordedAudioBlob,
                "voice_" + Date.now() + "." + extension
            );

            Swal.fire({
                title: "در حال ارسال ویس...",
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({

                url: "chat_action.php",

                type: "POST",

                data: formData,

                processData: false,

                contentType: false,

                dataType: "json",

                success: function (res) {

                    Swal.close();

                    if (res.success) {

                        recordedAudioBlob = null;

                        $("#previewAudio")
                            .attr("src", "");

                        $("#audioPreview")
                            .hide();

                        $("#messageInput")
                            .show();

                        $("#voiceBtn,#fileBtn")
                            .show();

                        cancelReply();

                        if (res.message) {

                            renderMessage(res.message);

                            lastMessageId =
                                Math.max(
                                    lastMessageId,
                                    parseInt(res.message.id)
                                );
                        }

                        scrollBottomPage();

                    } else {

                        Swal.fire({
                            icon: "error",
                            title: "خطا در ارسال ویس",
                            text: res.message,
                            confirmButtonText: "باشه"
                        });
                    }
                },

                error: function (xhr) {

                    Swal.close();

                    console.log(
                        "AUDIO ERROR:",
                        xhr.responseText
                    );

                    Swal.fire({
                        icon: "error",
                        title: "خطا در ارسال ویس",
                        text: "پاسخ نامعتبر از سرور دریافت شد.",
                        confirmButtonText: "باشه"
                    });
                }
            });
        });

        function scrollBottomPage() {

            const area =
                $("#messagesArea")[0];

            area.scrollTop =
                area.scrollHeight;
        }

        function setReply(msg) {

            replyToId = msg.id;

            $("#replyPreviewTitle")
                .text(
                    "پاسخ به " + msg.sender_name
                );

            $("#replyPreviewText")
                .text(
                    msg.preview ||
                    msg.message ||
                    "محتوا"
                );

            $("#replyPreview")
                .css("display", "flex");

            $("#messageInput").focus();
        }

        function cancelReply() {

            replyToId = null;

            $("#replyPreview").hide();
        }

        function copyMessage(id) {

            const el =
                $("#message-" + id);

            let text =
                el.find(".message-text").text();

            if (!text) {

                text =
                    el.find(".file-name").text();

                if (!text)
                    text = "🎤 پیام صوتی";
            }

            navigator.clipboard.writeText(text);

            Swal.fire({
                icon: "success",
                title: "کپی شد",
                timer: 900,
                showConfirmButton: false
            });
        }

        function deleteMessage(id) {

            Swal.fire({

                title: "حذف پیام؟",

                text: "این عملیات قابل بازگشت نیست.",

                icon: "warning",

                showCancelButton: true,

                confirmButtonText: "حذف",

                cancelButtonText: "انصراف"

            }).then(function (result) {

                if (!result.isConfirmed)
                    return;

                $.ajax({

                    url: "chat_action.php",

                    type: "POST",

                    data: {
                        action: "delete_message",
                        id: id,
                        course_id: COURSE_ID
                    },

                    dataType: "json",

                    success: function (res) {

                        if (res.success) {

                            $("#message-" + id)
                                .closest(".message-row")
                                .remove();

                            Swal.fire({
                                icon: "success",
                                title: "حذف شد",
                                timer: 800,
                                showConfirmButton: false
                            });

                        } else {

                            Swal.fire({
                                icon: "error",
                                title: "خطا",
                                text: res.message
                            });
                        }
                    }
                });
            });
        }

        function editMessage(id) {

            const el =
                $("#message-" + id);

            const oldText =
                el.find(".message-text").text();

            Swal.fire({

                title: "ویرایش پیام",

                input: "textarea",

                inputValue: oldText,

                showCancelButton: true,

                confirmButtonText: "ذخیره",

                cancelButtonText: "انصراف"

            }).then(function (result) {

                if (
                    !result.isConfirmed ||
                    !result.value.trim()
                )
                    return;

                $.ajax({

                    url: "chat_action.php",

                    type: "POST",

                    data: {
                        action: "edit_message",
                        id: id,
                        course_id: COURSE_ID,
                        message: result.value
                    },

                    dataType: "json",

                    success: function (res) {

                        if (res.success) {

                            el.find(".message-text")
                                .text(result.value);

                        } else {

                            Swal.fire({
                                icon: "error",
                                title: "خطا",
                                text: res.message
                            });
                        }
                    }
                });
            });
        }

        function openContextMenu(e, id) {

            selectedMessage = id;

            const el =
                $("#message-" + id);

            const owner =
                parseInt(
                    el.attr("data-owner")
                ) === 1;

            const canDelete =
                owner || USER_TYPE === 1;

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
                top: top + "px",
                left: left + "px"
            }).show();
        }

        $(document).on(
            "contextmenu",
            ".message",
            function (e) {

                e.preventDefault();

                openContextMenu(
                    e,
                    this.dataset.id
                );
            }
        );

        $("#copyAction").click(function () {

            if (selectedMessage)
                copyMessage(selectedMessage);

            $("#contextMenu").hide();
        });

        $("#replyAction").click(function () {

            if (selectedMessage) {

                const el =
                    $("#message-" + selectedMessage);

                setReply({

                    id: selectedMessage,

                    sender_name:
                        el.find(".sender-name").text(),

                    message:
                        el.find(".message-text").text(),

                    preview:
                        el.find(".message-text").text()
                        ||
                        el.find(".file-name").text()
                        ||
                        "🎤 پیام صوتی"
                });
            }

            $("#contextMenu").hide();
        });

        $("#deleteAction").click(function () {

            if (selectedMessage)
                deleteMessage(selectedMessage);

            $("#contextMenu").hide();
        });

        $("#editAction").click(function () {

            if (selectedMessage)
                editMessage(selectedMessage);

            $("#contextMenu").hide();
        });

        $(document).click(function (e) {

            if (
                !$(e.target)
                    .closest("#contextMenu")
                    .length
            ) {

                $("#contextMenu").hide();
            }
        });

        let touchTimer;

        $(document).on(
            "touchstart",
            ".message",
            function () {

                const id =
                    this.dataset.id;

                touchTimer =
                    setTimeout(function () {

                        selectedMessage = id;

                        const rect =
                            document
                                .getElementById(
                                    "message-" + id
                                )
                                .getBoundingClientRect();

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

                    }, 600);
            }
        );

        $(document).on(
            "touchend touchmove",
            ".message",
            function () {

                clearTimeout(touchTimer);
            }
        );

        loadMessages(true);

        setInterval(function () {
            loadMessages(false);
        }, 2500);
    </script>

</body>

</html>