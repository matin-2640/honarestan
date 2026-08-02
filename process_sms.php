<?php

include("connect.php");

header('Content-Type: application/json; charset=utf-8');


/*
|--------------------------------------------------------------------------
| تشخیص نوع درخواست
|--------------------------------------------------------------------------
|
| get_recipients:
| فقط لیست هنرجوها / هنرآموزها را برمی‌گرداند.
|
| send_sms:
| پیامک را ارسال می‌کند.
|
*/

$action = isset($_POST['action'])
    ? $_POST['action']
    : 'send_sms';


/*
|--------------------------------------------------------------------------
| دریافت لیست گیرندگان
|--------------------------------------------------------------------------
*/

if ($action === 'get_recipients') {

    $recipient_type = isset($_POST['recipient_type'])
        ? $_POST['recipient_type']
        : '';

    $html = '';


    /*
    |--------------------------------------------------------------------------
    | لیست هنرجویان یک کلاس
    |--------------------------------------------------------------------------
    */

    if (strpos($recipient_type, 'class_') === 0) {

        $class_id = str_replace('class_', '', $recipient_type);


        if (!ctype_digit($class_id)) {

            echo json_encode([
                'status' => 'error',
                'message' => 'شناسه کلاس نامعتبر است.'
            ], JSON_UNESCAPED_UNICODE);

            exit;
        }


        $stmt = $connect->prepare("
            SELECT
                Stu_ID,
                Stu_fullName,
                Stu_phone
            FROM Students
            WHERE Stu_classID = :class_id
            ORDER BY Stu_fullName ASC
        ");


        $stmt->bindValue(
            ':class_id',
            (int)$class_id,
            PDO::PARAM_INT
        );

        $stmt->execute();

        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);


        if (count($students) === 0) {

            echo json_encode([
                'status' => 'error',
                'message' => 'هیچ هنرجویی در این کلاس پیدا نشد.'
            ], JSON_UNESCAPED_UNICODE);

            exit;
        }


        /*
         * ساخت باکس هنرجویان
         *
         * checked یعنی به صورت پیش‌فرض همه انتخاب شده‌اند.
         */

        foreach ($students as $student) {

            $id = htmlspecialchars(
                $student['Stu_ID'],
                ENT_QUOTES,
                'UTF-8'
            );

            $name = htmlspecialchars(
                $student['Stu_fullName'],
                ENT_QUOTES,
                'UTF-8'
            );

            $phone = htmlspecialchars(
                $student['Stu_phone'],
                ENT_QUOTES,
                'UTF-8'
            );


            $html .= '
                <div class="sms-recipient-box">
                    <label>

                        <input
                            type="checkbox"
                            class="recipient-checkbox"
                            name="selected_students[]"
                            value="' . $id . '"
                            checked>

                        <span>
                            <strong>' . $name . '</strong>
                            <br>
                            <span>' .
                                ($phone !== ''
                                    ? $phone
                                    : 'شماره ثبت نشده')
                            . '</span>
                        </span>

                    </label>
                </div>
            ';
        }
    }


    /*
    |--------------------------------------------------------------------------
    | لیست هنرآموزان
    |--------------------------------------------------------------------------
    */

    elseif ($recipient_type === 'teachers') {

        $stmt = $connect->prepare("
            SELECT
                T_ID,
                T_fullName,
                T_phone
            FROM Teachers
            ORDER BY T_fullName ASC
        ");

        $stmt->execute();

        $teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);


        if (count($teachers) === 0) {

            echo json_encode([
                'status' => 'error',
                'message' => 'هیچ هنرآموزی پیدا نشد.'
            ], JSON_UNESCAPED_UNICODE);

            exit;
        }


        /*
         * ساخت باکس هنرآموزان
         *
         * checked یعنی به صورت پیش‌فرض همه انتخاب شده‌اند.
         */

        foreach ($teachers as $teacher) {

            $id = htmlspecialchars(
                $teacher['T_ID'],
                ENT_QUOTES,
                'UTF-8'
            );

            $name = htmlspecialchars(
                $teacher['T_fullName'],
                ENT_QUOTES,
                'UTF-8'
            );

            $phone = htmlspecialchars(
                $teacher['T_phone'],
                ENT_QUOTES,
                'UTF-8'
            );


            $html .= '
                <div class="sms-recipient-box">
                    <label>

                        <input
                            type="checkbox"
                            class="recipient-checkbox"
                            name="selected_teachers[]"
                            value="' . $id . '"
                            checked>

                        <span>
                            <strong>' . $name . '</strong>
                            <br>
                            <span>' .
                                ($phone !== ''
                                    ? $phone
                                    : 'شماره ثبت نشده')
                            . '</span>
                        </span>

                    </label>
                </div>
            ';
        }
    }


    /*
    |--------------------------------------------------------------------------
    | نوع گیرنده نامعتبر
    |--------------------------------------------------------------------------
    */

    else {

        echo json_encode([
            'status' => 'error',
            'message' => 'نوع گیرنده نامعتبر است.'
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | ارسال HTML به Send_sms.php
    |--------------------------------------------------------------------------
    */

    echo json_encode([
        'status' => 'success',
        'html' => $html
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


/*
|--------------------------------------------------------------------------
| شروع ارسال پیامک
|--------------------------------------------------------------------------
*/

$recipient_type = isset($_POST['recipient_type'])
    ? $_POST['recipient_type']
    : '';

$text = isset($_POST['sms_text'])
    ? trim($_POST['sms_text'])
    : '';

$send_to_parents = isset($_POST['send_to_parents']);


if (empty($recipient_type) || empty($text)) {

    echo json_encode([
        'status' => 'error',
        'message' => 'اطلاعات ارسالی ناقص است.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


$receivers = [];


/*
|--------------------------------------------------------------------------
| 1. همه هنرجویان
|--------------------------------------------------------------------------
*/

if ($recipient_type === 'all_students') {

    $stmt = $connect->prepare("
        SELECT
            Stu_fullName,
            Stu_phone,
            Stu_fatherName,
            Stu_fatherPhone
        FROM Students
    ");

    $stmt->execute();

    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);


    foreach ($students as $row) {

        /*
         * ارسال به هنرجو
         */

        if (!empty($row['Stu_phone'])) {

            $receivers[] = [
                'name' => $row['Stu_fullName'],
                'phone' => $row['Stu_phone']
            ];
        }


        /*
         * ارسال به والد
         */

        if (
            $send_to_parents &&
            !empty($row['Stu_fatherPhone'])
        ) {

            $receivers[] = [
                'name' => 'ولی ' . $row['Stu_fullName'],
                'phone' => $row['Stu_fatherPhone']
            ];
        }
    }
}


/*
|--------------------------------------------------------------------------
| 2. کلاس خاص
|--------------------------------------------------------------------------
|
| فقط هنرجوهایی که چک‌باکس آنها تیک خورده ارسال می‌شوند.
|
*/

elseif (strpos($recipient_type, 'class_') === 0) {

    $class_id = str_replace('class_', '', $recipient_type);


    if (!ctype_digit($class_id)) {

        echo json_encode([
            'status' => 'error',
            'message' => 'شناسه کلاس نامعتبر است.'
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    /*
     * دریافت هنرجوهای انتخاب‌شده
     */

    $selected_students = isset($_POST['selected_students'])
        ? $_POST['selected_students']
        : [];


    if (!is_array($selected_students)) {
        $selected_students = [];
    }


    /*
     * تبدیل IDها به عدد
     * و حذف IDهای نامعتبر
     */

    $selected_students = array_filter(
        array_map('intval', $selected_students),
        function ($id) {
            return $id > 0;
        }
    );


    if (count($selected_students) === 0) {

        echo json_encode([
            'status' => 'error',
            'message' => 'هیچ هنرجویی انتخاب نشده است.'
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    /*
     * ساخت placeholder برای IN
     */

    $placeholders = implode(
        ',',
        array_fill(0, count($selected_students), '?')
    );


    /*
     * فقط هنرجویان انتخاب‌شده همان کلاس
     *
     * این قسمت از دستکاری ID توسط کاربر جلوگیری می‌کند.
     */

    $sql = "
        SELECT
            Stu_ID,
            Stu_fullName,
            Stu_phone,
            Stu_fatherName,
            Stu_fatherPhone
        FROM Students
        WHERE Stu_classID = ?
        AND Stu_ID IN ($placeholders)
    ";


    $params = array_merge(
        [(int)$class_id],
        array_values($selected_students)
    );


    $stmt = $connect->prepare($sql);

    $stmt->execute($params);

    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);


    foreach ($students as $row) {

        /*
         * ارسال به خود هنرجو
         */

        if (!empty($row['Stu_phone'])) {

            $receivers[] = [
                'name' => $row['Stu_fullName'],
                'phone' => $row['Stu_phone']
            ];
        }


        /*
         * ارسال به والد همان هنرجوی انتخاب‌شده
         */

        if (
            $send_to_parents &&
            !empty($row['Stu_fatherPhone'])
        ) {

            $receivers[] = [
                'name' => 'ولی ' . $row['Stu_fullName'],
                'phone' => $row['Stu_fatherPhone']
            ];
        }
    }
}


/*
|--------------------------------------------------------------------------
| 3. هنرآموزان
|--------------------------------------------------------------------------
|
| فقط هنرآموزهایی که چک‌باکس آنها تیک خورده ارسال می‌شوند.
|
*/

elseif ($recipient_type === 'teachers') {

    $selected_teachers = isset($_POST['selected_teachers'])
        ? $_POST['selected_teachers']
        : [];


    if (!is_array($selected_teachers)) {
        $selected_teachers = [];
    }


    /*
     * تبدیل IDها به عدد
     */

    $selected_teachers = array_filter(
        array_map('intval', $selected_teachers),
        function ($id) {
            return $id > 0;
        }
    );


    if (count($selected_teachers) === 0) {

        echo json_encode([
            'status' => 'error',
            'message' => 'هیچ هنرآموزی انتخاب نشده است.'
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    /*
     * ساخت placeholder
     */

    $placeholders = implode(
        ',',
        array_fill(0, count($selected_teachers), '?')
    );


    /*
     * دریافت فقط هنرآموزهای انتخاب‌شده
     */

    $sql = "
        SELECT
            T_ID,
            T_fullName,
            T_phone
        FROM Teachers
        WHERE T_ID IN ($placeholders)
    ";


    $stmt = $connect->prepare($sql);

    $stmt->execute(
        array_values($selected_teachers)
    );


    $teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);


    foreach ($teachers as $row) {

        if (!empty($row['T_phone'])) {

            $receivers[] = [
                'name' => $row['T_fullName'],
                'phone' => $row['T_phone']
            ];
        }
    }
}


/*
|--------------------------------------------------------------------------
| بررسی وجود گیرنده
|--------------------------------------------------------------------------
*/

if (count($receivers) === 0) {

    echo json_encode([
        'status' => 'error',
        'message' => 'هیچ شماره تماسی برای گیرندگان انتخاب‌شده پیدا نشد.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


/*
|--------------------------------------------------------------------------
| ارسال پیامک
|--------------------------------------------------------------------------
*/

foreach ($receivers as $person) {

    $name = $person['name'];

    $phone = $person['phone'];

    /*
     * فایل ارسال پیامک
     */
    include("sms/adminsms.php");
}


/*
|--------------------------------------------------------------------------
| پاسخ نهایی
|--------------------------------------------------------------------------
*/

echo json_encode([
    'status' => 'success',
    'message' => 'ارسال با موفقیت انجام شد. تعداد گیرندگان: ' . count($receivers)
], JSON_UNESCAPED_UNICODE);

exit;

?>