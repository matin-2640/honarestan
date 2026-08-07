<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include("connect.php");

// بررسی دسترسی ادمین
if (!(isset($_SESSION["state_login"]) && $_SESSION["type"] <= 2)) {
    header("location:login.php");
    exit();
}

try {
    // ۱. به محض ورود مدیر به این صفحه، پرونده‌های جدید به خوانده‌شده تبدیل می‌شوند تا چشمک‌زن خاموش شود
    $stmt_update = $connect->prepare("UPDATE teacher_disciplinary SET is_read = 1 WHERE is_read = 0");
    $stmt_update->execute();

    // ۲. دریافت پرونده‌های ثبت‌شده توسط معلمان همراه با نام دانش‌آموز از جدول students
    $sqlRecords = "SELECT 
                        d.*,
                        s.Stu_fullName
                   FROM teacher_disciplinary d
                   JOIN students s ON d.student_id = s.Stu_ID
                   ORDER BY d.id DESC";

    $records = $connect->query($sqlRecords)->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $records = [];
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نظارت بر پرونده‌های انضباطی کلاسی</title>
    <link rel="icon" href="images/icons/rahdanesh.png">
    <link rel="stylesheet" href="styles/disciplinary.css">
    <link rel="stylesheet" href="styles/font.css">
</head>

<body>

    <div class="container">

        <div class="card">

            <div class="card-title" style="justify-content: space-between;">

                <div style="display:flex; align-items:center; gap:10px;">

                    <svg viewBox="0 0 24 24" style="fill:#2563eb; width:24px; height:24px;">
                        <path d="M20 6h-8l-2-2H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2zm0 12H4V8h16v10z" />
                    </svg>

                    نظارت بر پرونده‌های انضباطی کلاسی (ثبت شده توسط معلمان)

                </div>

                <a href="admin_panel.php" class="btn-view-link" style="background:#64748b; font-size:12px; text-decoration:none;">
                    بازگشت به پنل مدیریت
                </a>

            </div>

            <div style="margin-bottom:15px;">

                <input type="text" id="searchInput" onkeyup="filterCards()"
                    placeholder="جستجو بر اساس نام دانش‌آموز، موضوع یا شرح..." style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:6px;">

            </div>

            <div class="records-grid" id="recordsGrid">

                <?php if (empty($records)): ?>

                    <p style="color:#64748b; text-align:center; padding:30px;">
                        هیچ پرونده انضباطی کلاسی یافت نشد.
                    </p>

                <?php else: ?>

                    <?php foreach ($records as $rec): ?>

                        <div class="disc-mini-card"
                            onclick='openModal(<?php echo json_encode($rec, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>)'>

                            <h4>
                                <?php echo htmlspecialchars($rec['Stu_fullName']); ?>
                            </h4>

                            <p>
                                <strong>موضوع:</strong>
                                <?php echo htmlspecialchars($rec['title']); ?>
                            </p>

                            <p style="font-size:11px; color:#dc2626; margin-top:8px;">

                                <?php echo htmlspecialchars($rec['incident_date']); ?>

                                -

                                ساعت

                                <?php echo htmlspecialchars($rec['incident_time']); ?>

                            </p>

                        </div>

                    <?php endforeach; ?>

                <?php endif; ?>

            </div>

        </div>

    </div>

    <div class="modal-overlay" id="detailModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); align-items:center; justify-content:center;">

        <div class="modal-content" style="background:#fff; padding:20px; border-radius:8px; width:90%; max-width:500px;">

            <div class="modal-header" style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #e2e8f0; padding-bottom:10px; margin-bottom:15px;">

                <h3 id="modalStudentName" style="margin:0; color:#dc2626; font-size:16px;"></h3>

                <span class="modal-close" onclick="closeModal()" style="cursor:pointer; font-size:22px; font-weight:bold;">
                    &times;
                </span>

            </div>

            <div>

                <p>
                    <strong>عنوان موضوع:</strong>
                    <span id="modalTitle"></span>
                </p>

                <p style="margin-top:8px;">
                    <strong>زمان وقوع:</strong>
                    <span id="modalDateTime"></span>
                </p>

                <hr style="border:0; border-top:1px solid #e2e8f0; margin:12px 0;">

                <p>
                    <strong>شرح گزارش معلم:</strong>
                </p>

                <p id="modalDescription" style="
                    background:#f8fafc;
                    padding:10px;
                    border-radius:6px;
                    border:1px solid #cbd5e1;
                    font-size:13px;
                    line-height:1.6;
                    margin-top:5px;
                ">
                </p>

                <a href="#" id="smsParentBtn" class="btn-view-link" style="
                    background:#dc2626;
                    font-size:12px;
                    display:inline-flex;
                    margin-top:15px;
                    color:#fff;
                    padding:8px 12px;
                    border-radius:4px;
                    text-decoration:none;
                ">
                    ارسال پیامک به والدین
                </a>

            </div>

        </div>

    </div>

    <script>

        function filterCards() {
            let input = document.getElementById('searchInput').value.toLowerCase();
            let cards = document.getElementsByClassName('disc-mini-card');

            for (let i = 0; i < cards.length; i++) {
                let text = cards[i].innerText.toLowerCase();
                cards[i].style.display = text.includes(input) ? 'block' : 'none';
            }
        }

        function openModal(data) {
            document.getElementById('modalStudentName').innerText = "پرونده انضباطی: " + data.Stu_fullName;
            document.getElementById('modalTitle').innerText = data.title;
            document.getElementById('modalDateTime').innerText = data.incident_date + " (ساعت " + data.incident_time + ")";
            document.getElementById('modalDescription').innerText = data.description;

            let smsButton = document.getElementById('smsParentBtn');

            if (data.student_id) {
                smsButton.href = "sms/disciplinary_sms.php?id=" + encodeURIComponent(data.student_id);
                smsButton.style.display = "inline-flex";
            } else {
                smsButton.href = "#";
                smsButton.style.display = "none";
            }

            document.getElementById('detailModal').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('detailModal').style.display = 'none';
        }

        window.onclick = function (event) {
            let modal = document.getElementById('detailModal');
            if (event.target === modal) {
                closeModal();
            }
        };

    </script>

</body>

</html>
