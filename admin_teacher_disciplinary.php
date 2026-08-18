<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include("connect.php");

if (!(isset($_SESSION["state_login"]) && $_SESSION["type"] <= 2)) {
    header("location:login.php");
    exit();
}

$start_date = isset($_GET['start_date']) ? trim($_GET['start_date']) : '';
$end_date = isset($_GET['end_date']) ? trim($_GET['end_date']) : '';

$hasDateFilter = !empty($start_date) && !empty($end_date);

try {
    $stmt_update = $connect->prepare("UPDATE teacher_disciplinary SET is_read = 1 WHERE is_read = 0");
    $stmt_update->execute();

    $sqlRecords = "SELECT 
                        d.*,
                        s.Stu_fullName
                   FROM teacher_disciplinary d
                   JOIN students s ON d.student_id = s.Stu_ID";

    if ($hasDateFilter) {
        $sqlRecords .= " WHERE d.incident_date BETWEEN :start_date AND :end_date";
    }

    $sqlRecords .= " ORDER BY d.id DESC";

    $stmt = $connect->prepare($sqlRecords);

    if ($hasDateFilter) {
        $stmt->bindValue(':start_date', $start_date);
        $stmt->bindValue(':end_date', $end_date);
    }

    $stmt->execute();
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

    <link rel="stylesheet"
        href="https://unpkg.com/persian-datepicker@1.2.0/dist/css/persian-datepicker.min.css">

    <style>
        .date-filter-box {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 15px;
        }

        .date-filter-item {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .date-filter-item label {
            font-size: 13px;
            font-weight: bold;
            color: #334155;
        }

        .date-filter-item input {
            width: 100%;
            padding: 10px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            outline: none;
            box-sizing: border-box;
            font-family: inherit;
        }

        .date-filter-item input:focus {
            border-color: #2563eb;
        }

        .date-filter-actions {
            display: flex;
            gap: 8px;
            margin-bottom: 15px;
        }

        .date-filter-btn {
            border: 0;
            background: #2563eb;
            color: #fff;
            padding: 10px 18px;
            border-radius: 6px;
            cursor: pointer;
            font-family: inherit;
            font-weight: bold;
        }

        .date-reset-btn {
            border: 0;
            background: #64748b;
            color: #fff;
            padding: 10px 18px;
            border-radius: 6px;
            cursor: pointer;
            font-family: inherit;
            font-weight: bold;
        }

        .date-error {
            display: none;
            color: #dc2626;
            font-size: 13px;
            margin-bottom: 12px;
        }

        @media (max-width: 600px) {
            .date-filter-box {
                grid-template-columns: 1fr;
            }

            .date-filter-actions {
                flex-direction: column;
            }

            .date-filter-btn,
            .date-reset-btn {
                width: 100%;
            }
        }
    </style>
</head>

<body>

    <div class="container">

        <div class="card">

            <div class="card-title" style="justify-content: space-between;">

                <div style="display:flex; align-items:center; gap:10px;">

                    <svg viewBox="0 0 24 24"
                        style="fill:#2563eb; width:24px; height:24px;">

                        <path
                            d="M20 6h-8l-2-2H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2zm0 12H4V8h16v10z" />

                    </svg>

                    نظارت بر پرونده‌های انضباطی کلاسی (ثبت شده توسط معلمان)

                </div>

                <a href="admin_panel.php"
                    class="btn-view-link"
                    style="background:#64748b; font-size:12px; text-decoration:none;">

                    بازگشت به پنل مدیریت

                </a>

            </div>

            <div class="date-filter-box">

                <div class="date-filter-item">

                    <label for="startDate">
                        از تاریخ:
                    </label>

                    <input
                        type="text"
                        id="startDate"
                        value="<?php echo htmlspecialchars($start_date); ?>"
                        placeholder="تاریخ شروع"
                        autocomplete="off">

                </div>

                <div class="date-filter-item">

                    <label for="endDate">
                        تا تاریخ:
                    </label>

                    <input
                        type="text"
                        id="endDate"
                        value="<?php echo htmlspecialchars($end_date); ?>"
                        placeholder="تاریخ پایان"
                        autocomplete="off">

                </div>

            </div>

            <div id="dateError" class="date-error">
                تاریخ پایان نباید قبل از تاریخ شروع باشد.
            </div>

            <div class="date-filter-actions">

                <button type="button"
                    class="date-filter-btn"
                    id="searchDateBtn">

                    جستجوی بازه

                </button>

                <button type="button"
                    class="date-reset-btn"
                    id="resetDateBtn">

                    پاک کردن بازه

                </button>

            </div>

            <div style="margin-bottom:15px;">

                <input
                    type="text"
                    id="searchInput"
                    placeholder="جستجو بر اساس نام دانش‌آموز، موضوع یا شرح..."
                    style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:6px; box-sizing:border-box;">

            </div>

            <div class="records-grid" id="recordsGrid">

                <?php if (!$hasDateFilter): ?>

                    <p id="defaultMessage"
                        style="color:#64748b; text-align:center; padding:30px; width:100%;">

                        ابتدا بازه شروع و پایان جستجو را انتخاب کنید

                    </p>

                <?php else: ?>

                    <?php if (!empty($records)): ?>

                        <?php foreach ($records as $rec): ?>

                            <div class="disc-mini-card"
                                onclick='openModal(<?php echo json_encode(
                                    $rec,
                                    JSON_UNESCAPED_UNICODE |
                                    JSON_HEX_TAG |
                                    JSON_HEX_APOS |
                                    JSON_HEX_QUOT |
                                    JSON_HEX_AMP
                                ); ?>)'>

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

                    <?php else: ?>

                        <p style="color:#64748b; text-align:center; padding:30px; width:100%;">

                            هیچ پرونده انضباطی کلاسی در این بازه یافت نشد.

                        </p>

                    <?php endif; ?>

                <?php endif; ?>

            </div>

        </div>

    </div>


    <div class="modal-overlay"
        id="detailModal"
        style="
            display:none;
            position:fixed;
            top:0;
            left:0;
            width:100%;
            height:100%;
            background:rgba(0,0,0,0.5);
            align-items:center;
            justify-content:center;
        ">

        <div class="modal-content"
            style="
                background:#fff;
                padding:20px;
                border-radius:8px;
                width:90%;
                max-width:500px;
            ">

            <div class="modal-header"
                style="
                    display:flex;
                    justify-content:space-between;
                    align-items:center;
                    border-bottom:1px solid #e2e8f0;
                    padding-bottom:10px;
                    margin-bottom:15px;
                ">

                <h3 id="modalStudentName"
                    style="
                        margin:0;
                        color:#dc2626;
                        font-size:16px;
                    ">
                </h3>

                <span
                    class="modal-close"
                    onclick="closeModal()"
                    style="
                        cursor:pointer;
                        font-size:22px;
                        font-weight:bold;
                    ">

                    &times;

                </span>

            </div>

            <div>

                <p>

                    <strong>
                        عنوان موضوع:
                    </strong>

                    <span id="modalTitle"></span>

                </p>

                <p style="margin-top:8px;">

                    <strong>
                        زمان وقوع:
                    </strong>

                    <span id="modalDateTime"></span>

                </p>

                <hr style="
                    border:0;
                    border-top:1px solid #e2e8f0;
                    margin:12px 0;
                ">

                <p>

                    <strong>
                        شرح گزارش معلم:
                    </strong>

                </p>

                <p id="modalDescription"
                    style="
                        background:#f8fafc;
                        padding:10px;
                        border-radius:6px;
                        border:1px solid #cbd5e1;
                        font-size:13px;
                        line-height:1.6;
                        margin-top:5px;
                    ">
                </p>

                <a
                    href="#"
                    id="smsParentBtn"
                    class="btn-view-link"
                    style="
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


    <script src="js/jquery-1.10.2.min.js"></script>

    <script src="https://unpkg.com/persian-date@1.1.0/dist/persian-date.min.js"></script>

    <script src="https://unpkg.com/persian-datepicker@1.2.0/dist/js/persian-datepicker.min.js"></script>


    <script>

        $(document).ready(function () {

            var startPicker = $('#startDate').persianDatepicker({
                format: 'YYYY/MM/DD',
                autoClose: true,
                initialValue: false,
                calendar: {
                    persian: {
                        locale: 'fa'
                    }
                }
            });

            var endPicker = $('#endDate').persianDatepicker({
                format: 'YYYY/MM/DD',
                autoClose: true,
                initialValue: false,
                calendar: {
                    persian: {
                        locale: 'fa'
                    }
                }
            });


            $('#searchDateBtn').on('click', function () {

                var startDate = $('#startDate').val().trim();
                var endDate = $('#endDate').val().trim();

                if (startDate === '' || endDate === '') {

                    $('#dateError')
                        .text('لطفاً تاریخ شروع و پایان را انتخاب کنید.')
                        .show();

                    return;
                }

                if (startDate > endDate) {

                    $('#dateError')
                        .text('تاریخ پایان نباید قبل از تاریخ شروع باشد.')
                        .show();

                    return;
                }

                $('#dateError').hide();

                var url =
                    window.location.pathname +
                    '?start_date=' +
                    encodeURIComponent(startDate) +
                    '&end_date=' +
                    encodeURIComponent(endDate);

                window.location.href = url;

            });


            $('#resetDateBtn').on('click', function () {

                $('#startDate').val('');
                $('#endDate').val('');

                window.location.href = window.location.pathname;

            });


            $('#searchInput').on('input', function () {

                var input = $(this).val().toLowerCase().trim();

                var cards = $('.disc-mini-card');

                if (cards.length === 0) {
                    return;
                }

                var found = false;

                cards.each(function () {

                    var text = $(this).text().toLowerCase();

                    if (text.includes(input)) {

                        $(this).show();

                        found = true;

                    } else {

                        $(this).hide();

                    }

                });

                $('#noSearchResult').remove();

                if (input !== '' && !found) {

                    $('#recordsGrid').append(
                        '<p id="noSearchResult" style="color:#64748b;text-align:center;padding:30px;width:100%;">' +
                        'هیچ پرونده‌ای با عبارت جستجو شده یافت نشد.' +
                        '</p>'
                    );

                }

            });

        });


        function openModal(data) {

            document.getElementById('modalStudentName').innerText =
                "پرونده انضباطی: " + data.Stu_fullName;

            document.getElementById('modalTitle').innerText =
                data.title;

            document.getElementById('modalDateTime').innerText =
                data.incident_date +
                " (ساعت " +
                data.incident_time +
                ")";

            document.getElementById('modalDescription').innerText =
                data.description;

            let smsButton =
                document.getElementById('smsParentBtn');

            if (data.student_id) {

                smsButton.href =
                    "sms/disciplinary_sms.php?id=" +
                    encodeURIComponent(data.student_id);

                smsButton.style.display =
                    "inline-flex";

            } else {

                smsButton.href = "#";

                smsButton.style.display =
                    "none";

            }

            document.getElementById('detailModal').style.display =
                'flex';

        }


        function closeModal() {

            document.getElementById('detailModal').style.display =
                'none';

        }


        window.onclick = function (event) {

            let modal =
                document.getElementById('detailModal');

            if (event.target === modal) {

                closeModal();

            }

        };

    </script>

</body>

</html>