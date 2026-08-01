<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include("connect.php");

// دریافت پرونده‌ها و نام دانش‌آموز از دیتابیس rahdanesh
$sqlRecords = "SELECT d.*, s.Stu_fullName 
               FROM disciplinary_records d 
               JOIN Students s ON d.student_id = s.Stu_ID 
               ORDER BY d.id DESC";
$records = $connect->query($sqlRecords)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مشاهده پرونده‌های انضباطی</title>
    <link rel="stylesheet" href="styles/disciplinary.css">
</head>
<body>

<div class="container">

    <div class="card">
        <div class="card-title" style="justify-content: space-between;">
            <div style="display:flex; align-items:center; gap:10px;">
                <svg viewBox="0 0 24 24" style="fill:#2563eb;"><path d="M20 6h-8l-2-2H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2zm0 12H4V8h16v10z"/></svg>
                لیست پرونده‌های انضباطی ثبت‌شده
            </div>
            <a href="admin_disciplinary.php" class="btn-view-link" style="background:#64748b; font-size:12px;">
                بازگشت به فرم ثبت
            </a>
        </div>

        <div style="margin-bottom: 15px;">
            <input type="text" id="searchInput" onkeyup="filterCards()" placeholder="جستجو بر اساس نام دانش‌آموز یا موضوع..." style="width: 100%;">
        </div>

        <div class="records-grid" id="recordsGrid">
            <?php if (empty($records)): ?>
                <p style="color: #64748b;">هیچ پرونده‌ای یافت نشد.</p>
            <?php else: ?>
                <?php foreach ($records as $rec): ?>
                    <div class="disc-mini-card" onclick="openModal(<?php echo htmlspecialchars(json_encode($rec)); ?>)">
                        <h4><?php echo htmlspecialchars($rec['Stu_fullName']); ?></h4>
                        <p><strong>موضوع:</strong> <?php echo htmlspecialchars($rec['title']); ?></p>
                        <p style="font-size:11px; color:#dc2626; margin-top:8px;">
                            <?php echo htmlspecialchars($rec['incident_date']); ?> - ساعت <?php echo htmlspecialchars($rec['incident_time']); ?>
                        </p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

</div>

<div class="modal-overlay" id="detailModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalStudentName" style="margin: 0; color: #dc2626;"></h3>
            <span class="modal-close" onclick="closeModal()">&times;</span>
        </div>
        <div>
            <p><strong>عنوان موضوع:</strong> <span id="modalTitle"></span></p>
            <p><strong>زمان وقوع:</strong> <span id="modalDateTime"></span></p>
            <hr style="border:0; border-top:1px solid #e2e8f0; margin:10px 0;">
            <p><strong>شرح گزارش:</strong></p>
            <p id="modalDescription" style="background:#f8fafc; padding:10px; border-radius:6px; border:1px solid #cbd5e1; font-size:13px; line-height:1.6;"></p>
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
    
    document.getElementById('detailModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('detailModal').style.display = 'none';
}

window.onclick = function(event) {
    let modal = document.getElementById('detailModal');
    if (event.target === modal) {
        closeModal();
    }
};
</script>

</body>
</html>
