<?php
include("connect.php");

// دریافت فیلترهای تاریخ و جستجو از طریق متد GET
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// کوئری پایه برای گرفتن غیبت‌ها (بر اساس جدول attendance و ساختار ستون‌های شما)
// فرض بر این است که A_state یا A_type نشان‌دهنده غیبت است (مثلاً مقدار 0)
$sql = "SELECT ar.*, s.Stu_fullName, s.Stu_nationalCode, c.C_grade, c.C_major 
        FROM attendance ar
        JOIN students s ON ar.A_studentID = s.Stu_ID
        JOIN classes c ON s.Stu_classID = c.C_ID
        WHERE (ar.A_state = '0' OR ar.A_type = '0')";

// اعمال فیلتر بازه تاریخ شمسی
if (!empty($start_date) && !empty($end_date)) {
    $sql .= " AND ar.A_date BETWEEN '$start_date' AND '$end_date'";
}

// اعمال فیلتر جستجو (نام یا کد ملی)
if (!empty($search)) {
    $sql .= " AND (s.Stu_fullName LIKE '%$search%' OR s.Stu_nationalCode LIKE '%$search%')";
}

$sql .= " ORDER BY ar.A_date DESC";

$result = $connect->query($sql);
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <title>گزارش غیبت‌های دانش‌آموزان</title>
    <link rel="stylesheet" href="styles/attendance_reports.css">
    <link rel="icon" href="images/icons/rahdanesh.png">
    <link rel="stylesheet" href="https://unpkg.com/persian-datepicker@1.2.0/dist/css/persian-datepicker.min.css">
</head>

<body>

    <div class="container">
        <h2>گزارش غیبت‌های دانش‌آموزان</h2>

        <form method="GET" action="">
            <div class="form-group">
                <label>از تاریخ:</label>
                <input type="text" name="start_date" id="startDate" value="<?php echo $start_date; ?>"
                    placeholder="1403/01/01" autocomplete="off">
            </div>

            <div class="form-group">
                <label>تا تاریخ:</label>
                <input type="text" name="end_date" id="endDate" value="<?php echo $end_date; ?>"
                    placeholder="1403/12/29" autocomplete="off">
            </div>

            <div class="form-group">
                <label>جستجو (نام یا کدملی):</label>
                <input type="text" name="search" value="<?php echo $search; ?>" placeholder="نام یا کد ملی...">
            </div>

            <div class="form-group" style="vertical-align: bottom;">
                <button type="submit" class="btn">جستجو و فیلتر</button>
            </div>
        </form>

        <hr style="border:0; border-top:1px solid #eee; margin: 20px 0;">

        <div class="cards-container">
            <?php
            if ($result && $result->rowCount() > 0) {
                while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                    $name = $row['Stu_fullName'];
                    $national = $row['Stu_nationalCode'];
                    $class_name = "پایه " . $row['C_grade'] . " - " . $row['C_major'];
                    $date = $row['A_date'];

                    echo '<div class="card">';
                    echo '<h4>' . $name . '</h4>';
                    echo '<p><b>کد ملی:</b> ' . $national . '</p>';
                    echo '<p><b>کلاس:</b> ' . $class_name . '</p>';
                    echo '<p><b>تاریخ غیبت:</b> ' . $date . '</p>';
                    echo '</div>';
                }
            } else {
                echo '<div class="no-record">هیچ موردی برای نمایش یافت نشد.</div>';
            }
            ?>
        </div>

    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://unpkg.com/persian-date@1.1.0/dist/persian-date.min.js"></script>
    <script src="https://unpkg.com/persian-datepicker@1.2.0/dist/js/persian-datepicker.min.js"></script>

    <script>
        $(document).ready(function () {
            // فعال‌سازی تقویم شمسی برای فیلتر بازه زمانی
            $('#startDate, #endDate').persianDatepicker({
                format: 'YYYY/MM/DD',
                autoClose: true,
                calendar: {
                    persian: {
                        locale: 'fa'
                    }
                }
            });
        });
    </script>

</body>

</html>