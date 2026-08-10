<?php
require_once 'connect.php';
session_start();
if (!(isset($_SESSION["state_login"]) && $_SESSION["type"] == 2 || $_SESSION["type"] == 3) || $_SESSION["type"] == 4) {
    header("location:login.php");
    exit();
}
if (!isset($connect) || !($connect instanceof PDO)) {
    die('خطا: اتصال PDO در فایل connect.php پیدا نشد.');
}
$connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$connect->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

if (
    isset($_GET['action']) &&
    $_GET['action'] === 'get_students'
) {
    header('Content-Type: application/json; charset=utf-8');

    try {
        $classId = (int) ($_GET['class_id'] ?? 0);
        if ($classId <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'کلاس انتخاب‌شده معتبر نیست.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $stmt = $connect->prepare("
            SELECT
                Stu_ID,
                Stu_fullName
            FROM students
            WHERE Stu_classID = :class_id
            ORDER BY Stu_fullName ASC
        ");
        $stmt->execute([
            ':class_id' => $classId
        ]);
        $students = $stmt->fetchAll();

        echo json_encode([
            'success' => true,
            'students' => $students
        ], JSON_UNESCAPED_UNICODE);

    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'خطا در دریافت اطلاعات دانش‌آموزان.'
        ], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    ($_POST['action'] ?? '') === 'add'
) {

    header('Content-Type: application/json; charset=utf-8');
    try {
        $studentId = (int) ($_POST['student_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $type = (int) ($_POST['type'] ?? 0);
        if ($studentId <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'لطفاً دانش‌آموز را انتخاب کنید.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }


        if ($title === '') {
            echo json_encode([
                'success' => false,
                'message' => 'لطفاً عنوان لوح را وارد کنید.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }


        if (!in_array($type, [1, 2, 3], true)) {
            echo json_encode([
                'success' => false,
                'message' => 'نوع لوح انتخاب‌شده معتبر نیست.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $checkStudent = $connect->prepare("
            SELECT Stu_ID
            FROM students
            WHERE Stu_ID = :student_id
            LIMIT 1
        ");
        $checkStudent->execute([
            ':student_id' => $studentId
        ]);
        if (!$checkStudent->fetch()) {
            echo json_encode([
                'success' => false,
                'message' => 'دانش‌آموز انتخاب‌شده پیدا نشد.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $stmt = $connect->prepare("
            INSERT INTO certificate
            (
                title,
                description,
                type,
                student_ID
            )
            VALUES
            (
                :title,
                :description,
                :type,
                :student_id
            )
        ");

        $stmt->execute([
            ':title' => $title,
            ':description' => $description,
            ':type' => $type,
            ':student_id' => $studentId
        ]);

        echo json_encode([
            'success' => true,
            'message' => 'لوح تقدیر با موفقیت ثبت شد.'
        ], JSON_UNESCAPED_UNICODE);

    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'خطایی هنگام ثبت لوح تقدیر رخ داد.'
        ], JSON_UNESCAPED_UNICODE);
    }
    exit;
}


/*
|--------------------------------------------------------------------------
| AJAX: ویرایش لوح تقدیر
|--------------------------------------------------------------------------
*/

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    ($_POST['action'] ?? '') === 'edit'
) {
    header('Content-Type: application/json; charset=utf-8');
    try {
        $certificateId = (int) ($_POST['certificate_id'] ?? 0);
        $studentId = (int) ($_POST['student_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $type = (int) ($_POST['type'] ?? 0);
        if ($certificateId <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'شناسه لوح معتبر نیست.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if ($studentId <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'لطفاً دانش‌آموز را انتخاب کنید.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if ($title === '') {
            echo json_encode([
                'success' => false,
                'message' => 'لطفاً عنوان لوح را وارد کنید.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if (!in_array($type, [1, 2, 3], true)) {
            echo json_encode([
                'success' => false,
                'message' => 'نوع لوح انتخاب‌شده معتبر نیست.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $checkCertificate = $connect->prepare("
            SELECT ID
            FROM certificate
            WHERE ID = :id
            LIMIT 1
        ");

        $checkCertificate->execute([
            ':id' => $certificateId
        ]);

        if (!$checkCertificate->fetch()) {
            echo json_encode([
                'success' => false,
                'message' => 'لوح موردنظر پیدا نشد.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $stmt = $connect->prepare("
            UPDATE certificate
            SET
                title = :title,
                description = :description,
                type = :type,
                student_ID = :student_id
            WHERE ID = :id
        ");

        $stmt->execute([
            ':title' => $title,
            ':description' => $description,
            ':type' => $type,
            ':student_id' => $studentId,
            ':id' => $certificateId
        ]);

        echo json_encode([
            'success' => true,
            'message' => 'لوح تقدیر با موفقیت ویرایش شد.'
        ], JSON_UNESCAPED_UNICODE);

    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'خطایی هنگام ویرایش لوح تقدیر رخ داد.'
        ], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    ($_POST['action'] ?? '') === 'delete'
) {
    header('Content-Type: application/json; charset=utf-8');
    try {
        $certificateId = (int) ($_POST['id'] ?? 0);
        if ($certificateId <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'شناسه لوح معتبر نیست.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $stmt = $connect->prepare("
            DELETE FROM certificate
            WHERE ID = :id
        ");

        $stmt->execute([
            ':id' => $certificateId
        ]);

        if ($stmt->rowCount() === 0) {
            echo json_encode([
                'success' => false,
                'message' => 'لوح موردنظر پیدا نشد یا قبلاً حذف شده است.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        echo json_encode([
            'success' => true,
            'message' => 'لوح تقدیر با موفقیت حذف شد.'
        ], JSON_UNESCAPED_UNICODE);

    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'خطایی هنگام حذف لوح تقدیر رخ داد.'
        ], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

try {
    $stmt = $connect->query("
        SELECT
            C_ID,
            C_grade,
            C_major
        FROM classes
        ORDER BY C_grade ASC, C_major ASC
    ");
    $classes = $stmt->fetchAll();
} catch (PDOException $e) {
    $classes = [];
}

try {
    $stmt = $connect->query("
        SELECT
            c.ID,
            c.title,
            c.description,
            c.type,
            c.student_ID,

            s.Stu_fullName,
            s.Stu_classID,

            cl.C_grade,
            cl.C_major

        FROM certificate AS c

        INNER JOIN students AS s
            ON c.student_ID = s.Stu_ID

        LEFT JOIN classes AS cl
            ON s.Stu_classID = cl.C_ID

        ORDER BY c.ID DESC
    ");

    $certificates = $stmt->fetchAll();
} catch (PDOException $e) {
    $certificates = [];
}

function certificateType($type)
{
    switch ((int) $type) {
        case 1:
            return 'آموزشی';
        case 2:
            return 'ورزشی';
        case 3:
            return 'فرهنگ و هنری';
        default:
            return 'نامشخص';
    }
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مدیریت لوح‌های تقدیر</title>
    <link rel="icon" href="images/icons/rahdanesh.png">
    <link rel="stylesheet" href="js/sweetalert2.min.css">
    <script src="js/sweetalert2.min.js"></script>
    <link rel="stylesheet" href="styles/font.css">
    <link rel="stylesheet" href="styles/certificate.css">
</head>

<body>
    <div class="container">
        <div class="page-header">
            <div>
                <h1>مدیریت لوح‌های تقدیر</h1>
                <p>ثبت، ویرایش و مدیریت لوح‌های تقدیر دانش‌آموزان</p>
            </div>
            <a href="admin_panel.php" class="btn-back">بازگشت به صفحه اصلی</a>
        </div>

        <div class="form-box">
            <h2 id="formTitle">افزودن لوح تقدیر جدید</h2>
            <form id="certificateForm">
                <input type="hidden" name="certificate_id" id="certificate_id" value="">
                <div class="form-group">
                    <label for="class_id">کلاس</label>
                    <select name="class_id" id="class_id" required>
                        <option value="">انتخاب کلاس</option>

                        <?php foreach ($classes as $class): ?>
                            <option value="<?= (int) $class['C_ID'] ?>">
                                <?= htmlspecialchars(
                                    $class['C_grade'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>

                                -

                                <?= htmlspecialchars(
                                    $class['C_major'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>

                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="student_id">دانش‌آموز</label>
                    <select name="student_id" id="student_id" required disabled>
                        <option value="">ابتدا کلاس را انتخاب کنید</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="type">نوع لوح</label>
                    <select name="type" id="type" required>
                        <option value="">انتخاب نوع لوح</option>
                        <option value="1">آموزشی</option>
                        <option value="2">ورزشی</option>
                        <option value="3">فرهنگ و هنری</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="title">عنوان لوح</label>
                    <input type="text" name="title" id="title" maxlength="255"
                        placeholder="عنوان لوح تقدیر را وارد کنید" required>
                </div>
                <div class="form-group">
                    <label for="description">توضیحات</label>
                    <textarea name="description" id="description"
                        placeholder="توضیحات لوح تقدیر را وارد کنید..."></textarea>
                </div>
                <button type="submit" class="btn-primary" id="submitButton">ثبت لوح تقدیر</button>
                <button type="button" id="cancelEditButton" class="hidden">انصراف از ویرایش</button>
            </form>
        </div>
        <section>
            <h2>لوح‌های ثبت‌شده</h2>
            <?php if (empty($certificates)): ?>
                <div class="empty-state">هنوز هیچ لوح تقدیری ثبت نشده است.</div>
            <?php else: ?>
                <div class="certificates-grid">
                    <?php foreach ($certificates as $certificate): ?>
                        <article class="certificate-card">
                            <h3>
                                <?= htmlspecialchars(
                                    $certificate['title'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </h3>
                            <p>
                                <strong>دانش‌آموز:</strong>
                                <?= htmlspecialchars(
                                    $certificate['Stu_fullName'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </p>
                            <p>
                                <strong>کلاس:</strong>
                                <?= htmlspecialchars(
                                    $certificate['C_grade'] ?? '-',
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>

                                -

                                <?= htmlspecialchars(
                                    $certificate['C_major'] ?? '-',
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>

                            </p>
                            <p>
                                <strong>نوع:</strong>
                                <?= htmlspecialchars(
                                    certificateType(
                                        $certificate['type']
                                    ),
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>
                            </p>
                            <?php if (
                                !empty($certificate['description'])
                            ): ?>
                                <p>
                                    <strong>توضیحات:</strong>
                                    <?= nl2br(
                                        htmlspecialchars(
                                            $certificate['description'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        )
                                    ) ?>
                                </p>
                            <?php endif; ?>
                            <div class="card-actions">

                                <button type="button" class="btn-edit edit-certificate"
                                    data-id="<?= (int) $certificate['ID'] ?>"
                                    data-student="<?= (int) $certificate['student_ID'] ?>"
                                    data-class="<?= (int) $certificate['Stu_classID'] ?>"
                                    data-type="<?= (int) $certificate['type'] ?>" data-title="<?= htmlspecialchars(
                                           $certificate['title'],
                                           ENT_QUOTES,
                                           'UTF-8'
                                       ) ?>" data-description="<?= htmlspecialchars(
                                            $certificate['description'] ?? '',
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>">
                                    ویرایش
                                </button>
                                <button type="button" class="btn-delete delete-certificate"
                                    data-id="<?= (int) $certificate['ID'] ?>">
                                    حذف
                                </button>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>
    <script>
        const form =
            document.getElementById('certificateForm');
        const classSelect =
            document.getElementById('class_id');
        const studentSelect =
            document.getElementById('student_id');
        const typeSelect =
            document.getElementById('type');
        const titleInput =
            document.getElementById('title');
        const descriptionInput =
            document.getElementById('description');
        const certificateIdInput =
            document.getElementById('certificate_id');
        const submitButton =
            document.getElementById('submitButton');
        const cancelEditButton =
            document.getElementById('cancelEditButton');
        const formTitle =
            document.getElementById('formTitle');

        async function loadStudents(
            classId,
            selectedStudentId = null
        ) {
            if (!classId) {
                studentSelect.innerHTML = `
            <option value="">
                ابتدا کلاس را انتخاب کنید
            </option>
        `;
                studentSelect.disabled = true;
                return;
            }
            studentSelect.disabled = true;
            studentSelect.innerHTML = `
        <option value="">
            در حال دریافت دانش‌آموزان...
        </option>
    `;

            try {
                const response = await fetch(
                    '?action=get_students&class_id=' +
                    encodeURIComponent(classId)
                );
                const data =
                    await response.json();

                if (!data.success) {
                    throw new Error(
                        data.message ||
                        'خطا در دریافت اطلاعات.'
                    );
                }

                studentSelect.innerHTML = `
            <option value="">
                انتخاب دانش‌آموز
            </option>
        `;

                if (
                    !data.students ||
                    data.students.length === 0
                ) {
                    studentSelect.innerHTML = `
                <option value="">
                    دانش‌آموزی در این کلاس وجود ندارد
                </option>
            `;
                    return;
                }

                data.students.forEach(student => {
                    const option =
                        document.createElement('option');
                    option.value =
                        student.Stu_ID;
                    option.textContent =
                        student.Stu_fullName;

                    if (
                        selectedStudentId !== null &&
                        String(student.Stu_ID) ===
                        String(selectedStudentId)
                    ) {
                        option.selected = true;
                    }
                    studentSelect.appendChild(option);
                });
                studentSelect.disabled = false;

            } catch (error) {
                console.error(error);
                studentSelect.innerHTML = `
            <option value="">
                خطا در دریافت اطلاعات
            </option>
        `;
                Swal.fire({
                    icon: 'error',
                    title: 'خطا',
                    text:
                        error.message ||
                        'ارتباط با سرور برقرار نشد.',
                    confirmButtonText: 'باشه'
                });
            }
        }

        classSelect.addEventListener(
            'change',
            function () {
                loadStudents(this.value);
            }
        );

        form.addEventListener(
            'submit',
            async function (event) {
                event.preventDefault();
                const certificateId =
                    certificateIdInput.value.trim();
                const action =
                    certificateId
                        ? 'edit'
                        : 'add';
                const formData =
                    new FormData(form);
                formData.append(
                    'action',
                    action
                );
                submitButton.disabled = true;
                Swal.fire({
                    title:
                        action === 'add'
                            ? 'در حال ثبت لوح...'
                            : 'در حال ذخیره تغییرات...',

                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                try {
                    const response =
                        await fetch(
                            '',
                            {
                                method: 'POST',
                                body: formData
                            }
                        );
                    const data =
                        await response.json();
                    Swal.close();
                    if (!data.success) {
                        throw new Error(
                            data.message ||
                            'عملیات با خطا مواجه شد.'
                        );
                    }

                    await Swal.fire({
                        icon: 'success',
                        title: 'موفق',
                        text: data.message,
                        confirmButtonText: 'باشه'
                    });

                    window.location.reload();
                } catch (error) {
                    Swal.close();
                    Swal.fire({
                        icon: 'error',
                        title: 'خطا',
                        text:
                            error.message ||
                            'خطایی در ارتباط با سرور رخ داد.',
                        confirmButtonText: 'باشه'
                    });
                    submitButton.disabled = false;
                }
            }
        );

        document
            .querySelectorAll('.edit-certificate')
            .forEach(button => {
                button.addEventListener(
                    'click',
                    async function () {

                        const id =
                            this.dataset.id;
                        const studentId =
                            this.dataset.student;
                        const classId =
                            this.dataset.class;
                        const type =
                            this.dataset.type;
                        const title =
                            this.dataset.title;
                        const description =
                            this.dataset.description;

                        certificateIdInput.value =
                            id;
                        titleInput.value =
                            title;
                        descriptionInput.value =
                            description;
                        typeSelect.value =
                            type;
                        classSelect.value =
                            classId;

                        await loadStudents(
                            classId,
                            studentId
                        );

                        formTitle.textContent =
                            'ویرایش لوح تقدیر';
                        submitButton.textContent =
                            'ذخیره تغییرات';
                        cancelEditButton.classList.remove(
                            'hidden'
                        );

                        window.scrollTo({
                            top: 0,
                            behavior: 'smooth'
                        });
                    }
                );
            });

        cancelEditButton.addEventListener(
            'click',
            function () {
                resetForm();
            }
        );
        document
            .querySelectorAll('.delete-certificate')
            .forEach(button => {

                button.addEventListener(
                    'click',
                    async function () {
                        const certificateId =
                            this.dataset.id;
                        const result =
                            await Swal.fire({
                                icon: 'warning',
                                title: 'حذف لوح تقدیر',
                                text:
                                    'آیا از حذف این لوح تقدیر مطمئن هستید؟',
                                showCancelButton: true,
                                confirmButtonText:
                                    'بله، حذف شود',
                                cancelButtonText:
                                    'انصراف',
                                reverseButtons: true
                            });

                        if (!result.isConfirmed) {
                            return;
                        }

                        const formData =
                            new FormData();

                        formData.append(
                            'action',
                            'delete'
                        );

                        formData.append(
                            'id',
                            certificateId
                        );

                        Swal.fire({
                            title: 'در حال حذف...',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        try {
                            const response =
                                await fetch(
                                    '',
                                    {
                                        method: 'POST',
                                        body: formData
                                    }
                                );

                            const data =
                                await response.json();

                            Swal.close();
                            if (!data.success) {
                                throw new Error(
                                    data.message ||
                                    'حذف انجام نشد.'
                                );
                            }

                            await Swal.fire({
                                icon: 'success',
                                title: 'حذف شد',
                                text: data.message,
                                confirmButtonText: 'باشه'
                            });


                            window.location.reload();
                        } catch (error) {
                            Swal.close();
                            Swal.fire({
                                icon: 'error',
                                title: 'خطا',
                                text:
                                    error.message ||
                                    'خطایی هنگام حذف رخ داد.',
                                confirmButtonText: 'باشه'
                            });
                        }
                    }
                );
            });

        function resetForm() {
            form.reset();
            certificateIdInput.value =
                '';
            studentSelect.innerHTML = `
        <option value="">
            ابتدا کلاس را انتخاب کنید
        </option>
    `;
            studentSelect.disabled =
                true;
            formTitle.textContent =
                'افزودن لوح تقدیر جدید';
            submitButton.textContent =
                'ثبت لوح تقدیر';
            cancelEditButton.classList.add(
                'hidden'
            );
        }
    </script>

</body>

</html>