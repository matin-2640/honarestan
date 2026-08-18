<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (
    !(isset($_SESSION["state_login"]) && ($_SESSION["type"] == 2 || $_SESSION["type"] == 3))
    || $_SESSION["type"] == 4
) {
    header("location:login.php");
    exit();
}

include("connect.php");

$start_date = isset($_GET['start_date']) ? trim($_GET['start_date']) : '';
$end_date = isset($_GET['end_date']) ? trim($_GET['end_date']) : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

function getRecords($connect, $start_date = '', $end_date = '', $search = '')
{
    $sql = "SELECT 
                d.*,
                s.Stu_fullName
            FROM disciplinary_records d
            JOIN Students s ON d.student_id = s.Stu_ID
            WHERE 1=1";

    $params = [];

    if (!empty($start_date) && !empty($end_date)) {
        $sql .= " AND d.incident_date BETWEEN :start_date AND :end_date";
        $params[':start_date'] = $start_date;
        $params[':end_date'] = $end_date;
    }

    if (!empty($search)) {
        $sql .= " AND (
            s.Stu_fullName LIKE :search
            OR d.title LIKE :search
            OR d.description LIKE :search
        )";

        $params[':search'] = '%' . $search . '%';
    }

    $sql .= " ORDER BY d.id DESC";

    $stmt = $connect->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if (
    isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
) {
    $ajaxStart = isset($_GET['start_date']) ? trim($_GET['start_date']) : '';
    $ajaxEnd = isset($_GET['end_date']) ? trim($_GET['end_date']) : '';
    $ajaxSearch = isset($_GET['search']) ? trim($_GET['search']) : '';

    if (empty($ajaxStart) || empty($ajaxEnd)) {
        echo '<p id="defaultMessage" style="color:#64748b;text-align:center;padding:30px;width:100%;">
                ابتدا بازه شروع و پایان جستجو را انتخاب کنید
              </p>';
        exit();
    }

    if ($ajaxStart > $ajaxEnd) {
        echo '<p style="color:#dc2626;text-align:center;padding:30px;width:100%;">
                تاریخ پایان نباید قبل از تاریخ شروع باشد.
              </p>';
        exit();
    }

    try {
        $ajaxRecords = getRecords(
            $connect,
            $ajaxStart,
            $ajaxEnd,
            $ajaxSearch
        );

        if (!empty($ajaxRecords)) {

            foreach ($ajaxRecords as $rec) {

                $jsonData = json_encode(
                    $rec,
                    JSON_UNESCAPED_UNICODE |
                    JSON_HEX_TAG |
                    JSON_HEX_APOS |
                    JSON_HEX_QUOT |
                    JSON_HEX_AMP
                );

                echo '<div class="disc-mini-card"
                        onclick=\'openModal(' . $jsonData . ')\'>';

                echo '<h4>';
                echo htmlspecialchars($rec['Stu_fullName']);
                echo '</h4>';

                echo '<p>';
                echo '<strong>موضوع:</strong> ';
                echo htmlspecialchars($rec['title']);
                echo '</p>';

                echo '<p style="font-size:11px;color:#dc2626;margin-top:8px;">';
                echo htmlspecialchars($rec['incident_date']);
                echo ' - ساعت ';
                echo htmlspecialchars($rec['incident_time']);
                echo '</p>';

                echo '</div>';
            }

        } else {

            echo '<p id="noResultMessage"
                    style="color:#64748b;text-align:center;padding:30px;width:100%;">
                    هیچ پرونده‌ای در این بازه یافت نشد.
                  </p>';
        }

    } catch (Exception $e) {

        echo '<p style="color:#dc2626;text-align:center;padding:30px;width:100%;">
                خطا در دریافت اطلاعات.
              </p>';
    }

    exit();
}

$records = [];

if (!empty($start_date) && !empty($end_date)) {
    try {
        $records = getRecords(
            $connect,
            $start_date,
            $end_date,
            $search
        );
    } catch (Exception $e) {
        $records = [];
    }
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>مشاهده پرونده‌های انضباطی</title>

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
            background: #fff;
        }

        .date-filter-item input:focus {
            border-color: #2563eb;
        }

        .date-error {
            display: none;
            color: #dc2626;
            font-size: 13px;
            margin-bottom: 12px;
            font-weight: bold;
        }

        .filter-loading {
            opacity: 0.5;
            pointer-events: none;
        }

        @media (max-width: 600px) {

            .date-filter-box {
                grid-template-columns: 1fr;
            }

        }

    </style>

</head>

<body>

    <div class="container">

        <div class="card">

            <div class="card-title"
                style="justify-content:space-between;">

                <div style="
                    display:flex;
                    align-items:center;
                    gap:10px;
                ">

                    <svg viewBox="0 0 24 24"
                        style="fill:#2563eb;">

                        <path
                            d="M20 6h-8l-2-2H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2zm0 12H4V8h16v10z" />

                    </svg>

                    لیست پرونده‌های انضباطی ثبت‌شده

                </div>

                <a href="admin_disciplinary.php"
                    class="btn-view-link"
                    style="
                        background:#64748b;
                        font-size:12px;
                        text-decoration:none;
                    ">

                    بازگشت به فرم ثبت

                </a>

            </div>


            <div class="date-filter-box">

                <div class="date-filter-item">

                    <label for="startDate">
                        از تاریخ
                    </label>

                    <input
                        type="text"
                        id="startDate"
                        placeholder="تاریخ شروع"
                        autocomplete="off">

                </div>


                <div class="date-filter-item">

                    <label for="endDate">
                        تا تاریخ
                    </label>

                    <input
                        type="text"
                        id="endDate"
                        placeholder="تاریخ پایان"
                        autocomplete="off">

                </div>

            </div>


            <div id="dateError"
                class="date-error">
            </div>


            <div style="margin-bottom:15px;">

                <input
                    type="text"
                    id="searchInput"
                    placeholder="جستجو بر اساس نام دانش‌آموز یا موضوع..."
                    style="
                        width:100%;
                        box-sizing:border-box;
                    ">

            </div>


            <div
                class="records-grid"
                id="recordsGrid">

                <?php if (empty($start_date) || empty($end_date)): ?>

                    <p id="defaultMessage"
                        style="
                            color:#64748b;
                            text-align:center;
                            padding:30px;
                            width:100%;
                        ">

                        ابتدا بازه شروع و پایان جستجو را انتخاب کنید

                    </p>

                <?php else: ?>

                    <?php if (!empty($records)): ?>

                        <?php foreach ($records as $rec): ?>

                            <div
                                class="disc-mini-card"
                                onclick='openModal(<?php echo json_encode(
                                    $rec,
                                    JSON_UNESCAPED_UNICODE |
                                    JSON_HEX_TAG |
                                    JSON_HEX_APOS |
                                    JSON_HEX_QUOT |
                                    JSON_HEX_AMP
                                ); ?>)'>

                                <h4>

                                    <?php echo htmlspecialchars(
                                        $rec['Stu_fullName']
                                    ); ?>

                                </h4>

                                <p>

                                    <strong>
                                        موضوع:
                                    </strong>

                                    <?php echo htmlspecialchars(
                                        $rec['title']
                                    ); ?>

                                </p>

                                <p style="
                                    font-size:11px;
                                    color:#dc2626;
                                    margin-top:8px;
                                ">

                                    <?php echo htmlspecialchars(
                                        $rec['incident_date']
                                    ); ?>

                                    -

                                    ساعت

                                    <?php echo htmlspecialchars(
                                        $rec['incident_time']
                                    ); ?>

                                </p>

                            </div>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <p
                            style="
                                color:#64748b;
                                text-align:center;
                                padding:30px;
                                width:100%;
                            ">

                            هیچ پرونده‌ای در این بازه یافت نشد.

                        </p>

                    <?php endif; ?>

                <?php endif; ?>

            </div>

        </div>

    </div>


    <div
        class="modal-overlay"
        id="detailModal">

        <div class="modal-content">

            <div class="modal-header">

                <h3
                    id="modalStudentName"
                    style="
                        margin:0;
                        color:#dc2626;
                    ">
                </h3>

                <span
                    class="modal-close"
                    onclick="closeModal()">

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


                <p>

                    <strong>
                        زمان وقوع:
                    </strong>

                    <span id="modalDateTime"></span>

                </p>


                <hr style="
                    border:0;
                    border-top:1px solid #e2e8f0;
                    margin:10px 0;
                ">


                <p>

                    <strong>
                        شرح گزارش:
                    </strong>

                </p>


                <p
                    id="modalDescription"
                    style="
                        background:#f8fafc;
                        padding:10px;
                        border-radius:6px;
                        border:1px solid #cbd5e1;
                        font-size:13px;
                        line-height:1.6;
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
                        margin-top:10px;
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

            var searchTimeout = null;


            $('#startDate').persianDatepicker({

                format: 'YYYY/MM/DD',

                autoClose: true,

                initialValue: false,

                calendar: {
                    persian: {
                        locale: 'fa'
                    }
                },

                onSelect: function () {

                    checkAndSearch();

                }

            });


            $('#endDate').persianDatepicker({

                format: 'YYYY/MM/DD',

                autoClose: true,

                initialValue: false,

                calendar: {
                    persian: {
                        locale: 'fa'
                    }
                },

                onSelect: function () {

                    checkAndSearch();

                }

            });


            function checkAndSearch() {

                var startDate =
                    $('#startDate').val().trim();

                var endDate =
                    $('#endDate').val().trim();


                if (startDate === '' || endDate === '') {

                    $('#dateError').hide();

                    $('#recordsGrid').html(
                        '<p id="defaultMessage" ' +
                        'style="color:#64748b;text-align:center;padding:30px;width:100%;">' +
                        'ابتدا بازه شروع و پایان جستجو را انتخاب کنید' +
                        '</p>'
                    );

                    return;

                }


                if (startDate > endDate) {

                    $('#dateError')
                        .text('تاریخ پایان نباید قبل از تاریخ شروع باشد.')
                        .show();

                    return;

                }


                $('#dateError').hide();

                fetchRecords();

            }


            function fetchRecords() {

                var startDate =
                    $('#startDate').val().trim();

                var endDate =
                    $('#endDate').val().trim();

                var search =
                    $('#searchInput').val().trim();


                if (
                    startDate === '' ||
                    endDate === ''
                ) {
                    return;
                }


                if (startDate > endDate) {
                    return;
                }


                var params = {

                    start_date: startDate,

                    end_date: endDate,

                    search: search

                };


                $('#recordsGrid')
                    .addClass('filter-loading');


                $.ajax({

                    url: window.location.pathname,

                    type: 'GET',

                    data: params,

                    headers: {
                        'X-Requested-With':
                            'XMLHttpRequest'
                    },

                    success: function (response) {

                        $('#recordsGrid')
                            .html(response)
                            .removeClass('filter-loading');

                    },

                    error: function () {

                        $('#recordsGrid')
                            .removeClass('filter-loading')
                            .html(
                                '<p style="' +
                                'color:#dc2626;' +
                                'text-align:center;' +
                                'padding:30px;' +
                                'width:100%;">' +
                                'خطا در دریافت اطلاعات.' +
                                '</p>'
                            );

                    }

                });

            }


            $('#searchInput').on('input', function () {

                clearTimeout(searchTimeout);

                searchTimeout = setTimeout(function () {

                    var startDate =
                        $('#startDate').val().trim();

                    var endDate =
                        $('#endDate').val().trim();


                    if (
                        startDate !== '' &&
                        endDate !== ''
                    ) {

                        fetchRecords();

                    }

                }, 300);

            });

        });


        function openModal(data) {

            document.getElementById(
                'modalStudentName'
            ).innerText =
                "پرونده انضباطی: " +
                data.Stu_fullName;


            document.getElementById(
                'modalTitle'
            ).innerText =
                data.title;


            document.getElementById(
                'modalDateTime'
            ).innerText =
                data.incident_date +
                " (ساعت " +
                data.incident_time +
                ")";


            document.getElementById(
                'modalDescription'
            ).innerText =
                data.description;


            let smsButton =
                document.getElementById(
                    'smsParentBtn'
                );


            if (data.student_id) {

                smsButton.href =
                    "sms/disciplinary_sms.php?id=" +
                    encodeURIComponent(
                        data.student_id
                    );

                smsButton.style.display =
                    "inline-flex";

            } else {

                smsButton.href = "#";

                smsButton.style.display =
                    "none";

            }


            document.getElementById(
                'detailModal'
            ).style.display =
                'flex';

        }


        function closeModal() {

            document.getElementById(
                'detailModal'
            ).style.display =
                'none';

        }


        window.onclick = function (event) {

            let modal =
                document.getElementById(
                    'detailModal'
                );


            if (event.target === modal) {

                closeModal();

            }

        };

    </script>

</body>

</html>