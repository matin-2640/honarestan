<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/*
|--------------------------------------------------------------------------
| سطح دسترسی دانش‌آموز
|--------------------------------------------------------------------------
| 0 = دانش‌آموز
| 1 = معلم
| 2 = معاون
| 3 = مدیر
| 4 = دولوپر
|--------------------------------------------------------------------------
*/

$userType = intval($_SESSION['type'] ?? -1);

if ($userType !== 0) {
    header("Location: ../login.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| اتصال دیتابیس
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../connect.php';

/*
 * در پروژه شما ممکن است اتصال با نام $connect باشد.
 * برای هماهنگی با کدهای دیگر، آن را به $pdo نیز متصل می‌کنیم.
 */
if (!isset($pdo) && isset($connect)) {
    $pdo = $connect;
}

if (!isset($pdo)) {
    die("خطا: اتصال به دیتابیس برقرار نشد.");
}


/*
|--------------------------------------------------------------------------
| تاریخ جلالی
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../teacher/jdf.php';


/*
|--------------------------------------------------------------------------
| شناسه دانش‌آموز وارد شده
|--------------------------------------------------------------------------
*/

$studentID = 0;

if (isset($_SESSION['ID'])) {

    $studentID = intval($_SESSION['ID']);

} elseif (isset($_SESSION['student_id'])) {

    $studentID = intval($_SESSION['student_id']);

} elseif (isset($_SESSION['user_id'])) {

    $studentID = intval($_SESSION['user_id']);
}


if ($studentID <= 0) {
    header("Location: ../login.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| دریافت لوح‌های تقدیر فقط برای همین دانش‌آموز
|--------------------------------------------------------------------------
*/

$certificates = [];

try {

    $stmt = $pdo->prepare("
        SELECT
            c.id,
            c.student_id,
            c.title,
            c.description,
            c.created_at,

            s.Stu_fullName,
            s.Stu_name,
            s.Stu_family,

            cl.C_grade,
            cl.C_major

        FROM certificates c

        INNER JOIN students s
            ON c.student_id = s.Stu_ID

        LEFT JOIN classes cl
            ON s.Stu_classID = cl.C_ID

        WHERE c.student_id = ?

        ORDER BY c.id DESC
    ");

    $stmt->execute([$studentID]);

    $certificates = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    $certificates = [];
}


/*
|--------------------------------------------------------------------------
| تابع امن برای نمایش متن
|--------------------------------------------------------------------------
*/

function h($value)
{
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        'UTF-8'
    );
}

?>
<!DOCTYPE html>

<html lang="fa" dir="rtl">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        لوح‌های تقدیر من - هنرستان راه دانش
    </title>


    <!-- فونت سایت -->

    <link
        rel="stylesheet"
        href="../font/font.css"
    >


    <!-- استایل پنل -->

    <link
        rel="stylesheet"
        href="../styles/panel_style.css"
    >


    <!-- Font Awesome -->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
    >


    <style>

        * {
            box-sizing: border-box;
        }

        body {

            font-family:
                'Vazirmatn',
                Tahoma,
                sans-serif;

            background:
                #f4f6f9;

            margin: 0;

            padding: 20px;

        }


        .container {

            max-width: 1100px;

            margin: 0 auto;

            background: #fff;

            padding: 25px;

            border-radius: 14px;

            box-shadow:
                0 4px 18px
                rgba(0, 0, 0, 0.06);

        }


        .top-nav {

            margin-bottom: 20px;

        }


        .btn-back {

            display: inline-flex;

            align-items: center;

            gap: 7px;

            background: #6c757d;

            color: #fff;

            padding: 9px 16px;

            border-radius: 7px;

            text-decoration: none;

            font-size: 14px;

        }


        .btn-back:hover {

            background: #5a6268;

        }


        h2 {

            margin: 0 0 25px;

            color: #333;

            border-bottom:
                2px solid #007bff;

            padding-bottom: 12px;

        }


        .cards-grid {

            display: grid;

            grid-template-columns:
                repeat(
                    auto-fill,
                    minmax(300px, 1fr)
                );

            gap: 20px;

        }


        .cert-card {

            background: #fff;

            border:
                1px solid #e1e1e1;

            border-radius: 12px;

            padding: 20px;

            box-shadow:
                0 3px 10px
                rgba(0, 0, 0, 0.04);

            display: flex;

            flex-direction: column;

            justify-content:
                space-between;

            min-height: 250px;

        }


        .cert-card-header {

            display: flex;

            align-items: center;

            gap: 8px;

            font-size: 18px;

            font-weight: bold;

            color: #b8860b;

            margin-bottom: 15px;

        }


        .cert-card-header i {

            font-size: 20px;

        }


        .cert-card-info {

            font-size: 13px;

            color: #666;

            line-height: 2;

            border-top:
                1px dashed #ddd;

            padding-top: 10px;

            margin-bottom: 12px;

        }


        .cert-card-body {

            font-size: 14px;

            color: #444;

            line-height: 1.9;

            margin-bottom: 20px;

        }


        .cert-card-actions {

            display: flex;

            gap: 8px;

            border-top:
                1px solid #eee;

            padding-top: 15px;

        }


        .action-btn {

            flex: 1;

            padding: 9px 8px;

            border-radius: 7px;

            text-align: center;

            text-decoration: none;

            font-size: 13px;

            display: inline-flex;

            align-items: center;

            justify-content: center;

            gap: 5px;

            transition: 0.2s;

        }


        .btn-view {

            background: #17a2b8;

            color: #fff;

        }


        .btn-view:hover {

            background: #138496;

        }


        .btn-print {

            background: #ffc107;

            color: #000;

        }


        .btn-print:hover {

            background: #e0a800;

        }


        .btn-pdf {

            background: #dc3545;

            color: #fff;

        }


        .btn-pdf:hover {

            background: #bd2130;

        }


        .no-data {

            grid-column: 1 / -1;

            text-align: center;

            padding: 60px 20px;

            color: #777;

        }


        .no-data i {

            display: block;

            font-size: 45px;

            margin-bottom: 15px;

            color: #aaa;

        }


        @media (max-width: 600px) {

            body {
                padding: 10px;
            }

            .container {
                padding: 15px;
            }

            .cards-grid {
                grid-template-columns: 1fr;
            }

            .cert-card-actions {
                flex-direction: column;
            }

        }

    </style>

</head>


<body>


<div class="container">


    <!-- بازگشت -->

    <div class="top-nav">

        <a
            href="../panel.php"
            class="btn-back"
        >

            <i class="fa fa-arrow-right"></i>

            بازگشت به پنل کاربری

        </a>

    </div>


    <!-- عنوان -->

    <h2>

        <i class="fa fa-award"></i>

        لوح‌های تقدیر من

    </h2>


    <!-- لیست لوح‌ها -->

    <div class="cards-grid">


        <?php if (count($certificates) > 0): ?>


            <?php foreach ($certificates as $cert): ?>


                <?php

                /*
                |--------------------------------------------------------------------------
                | نام دانش‌آموز
                |--------------------------------------------------------------------------
                */

                $stuName = '';

                if (!empty($cert['Stu_fullName'])) {

                    $stuName =
                        $cert['Stu_fullName'];

                } else {

                    $stuName = trim(
                        ($cert['Stu_name'] ?? '')
                        . ' '
                        . ($cert['Stu_family'] ?? '')
                    );

                }

                if ($stuName === '') {

                    $stuName = 'هنرجو';

                }


                /*
                |--------------------------------------------------------------------------
                | کلاس
                |--------------------------------------------------------------------------
                */

                $classInfo = trim(

                    ($cert['C_grade'] ?? '')
                    . ' '
                    . ($cert['C_major'] ?? '')

                );

                if ($classInfo === '') {

                    $classInfo =
                        'هنرستان راه دانش';

                }


                /*
                |--------------------------------------------------------------------------
                | عنوان
                |--------------------------------------------------------------------------
                */

                $certTitle =
                    !empty($cert['title'])
                    ? $cert['title']
                    : 'لوح سپاس و تقدیر';


                /*
                |--------------------------------------------------------------------------
                | توضیحات
                |--------------------------------------------------------------------------
                */

                $description =
                    trim(
                        $cert['description'] ?? ''
                    );


                /*
                |--------------------------------------------------------------------------
                | سال تحصیلی
                |--------------------------------------------------------------------------
                */

                $timestamp =
                    strtotime(
                        $cert['created_at']
                    );


                $jalaliYear =
                    intval(
                        jdate(
                            'Y',
                            $timestamp
                        )
                    );


                $jalaliMonth =
                    intval(
                        jdate(
                            'm',
                            $timestamp
                        )
                    );


                if ($jalaliMonth >= 7) {

                    $academicYear =
                        $jalaliYear
                        . '-'
                        . ($jalaliYear + 1);

                } else {

                    $academicYear =
                        ($jalaliYear - 1)
                        . '-'
                        . $jalaliYear;

                }


                /*
                |--------------------------------------------------------------------------
                | متن کوتاه
                |--------------------------------------------------------------------------
                */

                $shortDescription =
                    mb_substr(
                        strip_tags(
                            $description
                        ),
                        0,
                        120,
                        'UTF-8'
                    );


                if (
                    mb_strlen(
                        strip_tags($description),
                        'UTF-8'
                    ) > 120
                ) {

                    $shortDescription .= '...';

                }

                ?>


                <!-- کارت لوح -->

                <div class="cert-card">


                    <div>


                        <!-- عنوان لوح -->

                        <div
                            class="cert-card-header"
                        >

                            <i
                                class="fa fa-medal"
                            ></i>

                            <?php
                            echo h(
                                $certTitle
                            );
                            ?>

                        </div>


                        <!-- اطلاعات -->

                        <div
                            class="cert-card-info"
                        >

                            <div>

                                <strong>
                                    نام:
                                </strong>

                                <?php
                                echo h(
                                    $stuName
                                );
                                ?>

                            </div>


                            <div>

                                <strong>
                                    کلاس:
                                </strong>

                                <?php
                                echo h(
                                    $classInfo
                                );
                                ?>

                            </div>


                            <div>

                                <strong>
                                    سال تحصیلی:
                                </strong>

                                <?php
                                echo h(
                                    $academicYear
                                );
                                ?>

                            </div>

                        </div>


                        <!-- متن لوح -->

                        <?php if ($shortDescription !== ''): ?>

                            <div
                                class="cert-card-body"
                            >

                                <strong>
                                    متن لوح:
                                </strong>

                                <br>

                                <?php
                                echo nl2br(
                                    h(
                                        $shortDescription
                                    )
                                );
                                ?>

                            </div>

                        <?php endif; ?>


                    </div>


                    <!-- دکمه‌ها -->

                    <div
                        class="cert-card-actions"
                    >


                        <!-- مشاهده -->

                        <a
                            href="../certificate.php?id=<?php echo (int)$cert['id']; ?>&action=view"
                            target="_blank"
                            class="action-btn btn-view"
                            title="مشاهده لوح"
                        >

                            <i
                                class="fa fa-eye"
                            ></i>

                            مشاهده

                        </a>


                        <!-- چاپ -->

                        <a
                            href="../certificate.php?id=<?php echo (int)$cert['id']; ?>&action=print"
                            target="_blank"
                            class="action-btn btn-print"
                            title="چاپ لوح"
                        >

                            <i
                                class="fa fa-print"
                            ></i>

                            چاپ

                        </a>


                        <!-- PDF -->

                        <a
                            href="../certificate.php?id=<?php echo (int)$cert['id']; ?>&action=print"
                            target="_blank"
                            class="action-btn btn-pdf"
                            title="ذخیره PDF"
                        >

                            <i
                                class="fa fa-file-pdf"
                            ></i>

                            PDF

                        </a>


                    </div>


                </div>


            <?php endforeach; ?>


        <?php else: ?>


            <!-- بدون لوح -->

            <div class="no-data">

                <i
                    class="fa fa-folder-open"
                ></i>

                هنوز لوح تقدیری برای شما
                ثبت نشده است.

            </div>


        <?php endif; ?>


    </div>


</div>


</body>

</html>