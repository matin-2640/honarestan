<?php
require_once 'connect.php';
session_start();
if (!(isset($_SESSION["state_login"]) && $_SESSION["type"] == 2 || $_SESSION["type"] == 3) || $_SESSION["type"] == 4) {
    header("location:login.php");
    exit();
}
// دریافت وضعیت ترم‌های فعال برای نمایش کارت‌ها و دکمه‌ها
$active_terms = [];
try {
    $stmt = $connect->query("SELECT term FROM report_license WHERE publish = 1");
    $active_terms = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $active_terms = array_map('intval', $active_terms);
} catch (PDOException $e) {
    // مدیریت خطای دیتابیس در صورت نیاز
}

$terms_list = [
    1 => 'مهر و آبان',
    2 => 'آذر',
    3 => 'نوبت اول',
    4 => 'اسفند',
    5 => 'فروردین و اردیبهشت',
    6 => 'نوبت دوم'
];
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مدیریت مجوز انتشار کارنامه‌ها</title>
    <!-- FontAwesome & SweetAlert2 -->
    <link rel="stylesheet" href="js/sweetalert2.min.css">
    <script src="js/sweetalert2.min.js"></script>
    <link rel="icon" href="images/icons/rahdanesh.png" />
    <style>
        :root {
            --bg-primary: #0f172a;
            --bg-card: rgba(30, 41, 59, 0.7);
            --accent-gold: #c5a880;
            --accent-gold-hover: #e0c39a;
            --text-light: #f8fafc;
            --text-muted: #94a3b8;
            --danger: #ef4444;
            --danger-hover: #dc2626;
            --success: #10b981;
            --border-color: rgba(197, 168, 128, 0.2);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Vazirmatn', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: var(--bg-primary);
            background-image:
                radial-gradient(at 0% 0%, rgba(197, 168, 128, 0.12) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(30, 41, 59, 0.8) 0px, transparent 50%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            color: var(--text-light);
            padding: 20px;
            overflow-x: hidden;
        }

        .container {
            width: 100%;
            max-width: 900px;
            background: var(--bg-card);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--border-color);
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.4);
            animation: fadeInContainer 0.8s ease-out forwards;
        }

        @keyframes fadeInContainer {
            from {
                opacity: 0;
                transform: translateY(30px) scale(0.98);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 35px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border-color);
        }

        .header-title {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .header-title i {
            font-size: 2rem;
            color: var(--accent-gold);
            filter: drop-shadow(0 0 10px rgba(197, 168, 128, 0.3));
        }

        .header-title h1 {
            font-size: 1.6rem;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 10px 20px;
            background: transparent;
            border: 1px solid var(--accent-gold);
            color: var(--accent-gold);
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-back:hover {
            background: var(--accent-gold);
            color: var(--bg-primary);
            box-shadow: 0 0 15px rgba(197, 168, 128, 0.4);
            transform: translateX(-3px);
        }

        .grant-section {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.05);
            padding: 25px;
            border-radius: 16px;
            margin-bottom: 35px;
        }

        .form-group {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }

        .form-select {
            flex: 1;
            min-width: 240px;
            padding: 14px 20px;
            background: rgba(30, 41, 59, 0.9);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            color: var(--text-light);
            font-size: 1rem;
            outline: none;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .form-select:focus {
            border-color: var(--accent-gold);
            box-shadow: 0 0 10px rgba(197, 168, 128, 0.2);
        }

        .btn-submit {
            padding: 14px 28px;
            background: linear-gradient(135deg, var(--accent-gold), #a38258);
            border: none;
            color: #1e293b;
            font-weight: 700;
            border-radius: 12px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(197, 168, 128, 0.3);
            background: linear-gradient(135deg, var(--accent-gold-hover), var(--accent-gold));
        }

        .section-title {
            font-size: 1.1rem;
            color: var(--text-muted);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .grid-terms {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
        }

        .term-card {
            background: rgba(15, 23, 42, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.05);
            padding: 18px 20px;
            border-radius: 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s ease;
        }

        .term-card.active {
            border-color: rgba(16, 185, 129, 0.4);
            background: rgba(16, 185, 129, 0.05);
        }

        .term-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .badge-status {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: var(--text-muted);
        }

        .term-card.active .badge-status {
            background: var(--success);
            box-shadow: 0 0 10px var(--success);
        }

        .term-name {
            font-weight: 600;
            font-size: 0.95rem;
        }

        .btn-revoke {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger);
            border: 1px solid rgba(239, 68, 68, 0.2);
            padding: 6px 12px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.3s ease;
            opacity: 0;
            pointer-events: none;
        }

        .term-card.active .btn-revoke {
            opacity: 1;
            pointer-events: auto;
        }

        .btn-revoke:hover {
            background: var(--danger);
            color: #fff;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }

        .swal2-popup {
            background: #1e293b !important;
            color: #f8fafc !important;
            border-radius: 20px !important;
            border: 1px solid rgba(197, 168, 128, 0.3);
        }

        .swal2-title {
            color: #f8fafc !important;
        }

        .swal2-html-container {
            color: #94a3b8 !important;
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="header">
            <div class="header-title">
                <i class="fa-solid fa-file-signature me-2"></i>
                <h1>مدیریت مجوز کارنامه‌ها</h1>
            </div>
            <a href="admin_panel.php" class="btn-back">
                <i class="fa-solid fa-arrow-right"></i>
                <span>بازگشت به پنل</span>
            </a>
        </div>

        <div class="grant-section">
            <form id="licenseForm">
                <div class="form-group">
                    <select id="termSelect" name="term" class="form-select" required>
                        <option value="" disabled selected>انتخاب دوره تحصیلی...</option>
                        <?php foreach ($terms_list as $val => $name): ?>
                            <option value="<?= $val ?>"><?= $name ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn-submit">
                        <i class="fa-solid fa-key"></i>
                        <span>اعطای مجوز نمایش</span>
                    </button>
                </div>
            </form>
        </div>

        <div class="section-title">
            <i class="fa-solid fa-list-check"></i>
            <span>وضعیت دسترسی دوره‌ها</span>
        </div>

        <div class="grid-terms">
            <?php foreach ($terms_list as $val => $name):
                $isActive = in_array($val, $active_terms);
                ?>
                <div class="term-card <?= $isActive ? 'active' : '' ?>" id="card-term-<?= $val ?>">
                    <div class="term-info">
                        <span class="badge-status"></span>
                        <span class="term-name"><?= $name ?></span>
                    </div>
                    <button class="btn-revoke" onclick="revokeAccess(<?= $val ?>, '<?= $name ?>')">
                        <i class="fa-solid fa-lock"></i>
                        <span>لغو مجوز</span>
                    </button>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        document.getElementById('licenseForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const termVal = document.getElementById('termSelect').value;

            if (!termVal) return;

            const formData = new FormData();
            formData.append('action', 'grant');
            formData.append('term', termVal);

            fetch('License_report_back.php', {
                method: 'POST',
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'عملیات موفق',
                            text: data.message,
                            confirmButtonText: 'متوجه شدم',
                            confirmButtonColor: '#c5a880'
                        });
                        document.getElementById(`card-term-${termVal}`).classList.add('active');
                    } else if (data.status === 'warning') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'مجوز تکراری',
                            text: data.message,
                            confirmButtonText: 'متوجه شدم',
                            confirmButtonColor: '#c5a880'
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'خطا',
                            text: data.message || 'خطایی در پردازش اطلاعات رخ داد.',
                            confirmButtonText: 'تلاش مجدد'
                        });
                    }
                });
        });

        function revokeAccess(termVal, termName) {
            Swal.fire({
                title: 'آیا اطمینان دارید؟',
                text: `دسترسی مشاهده کارنامه ${termName} از هنرجویان گرفته خواهد شد!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#475569',
                confirmButtonText: 'بله، لغو شود',
                cancelButtonText: 'انصراف'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('action', 'revoke');
                    formData.append('term', termVal);

                    fetch('License_report_back.php', {
                        method: 'POST',
                        body: formData
                    })
                        .then(res => res.json())
                        .then(data => {
                            if (data.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'حذف مجوز',
                                    text: `دسترسی دوره ${termName} با موفقیت لغو شد.`,
                                    confirmButtonText: 'تایید',
                                    confirmButtonColor: '#c5a880'
                                });
                                document.getElementById(`card-term-${termVal}`).classList.remove('active');
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'خطا',
                                    text: data.message || 'خطایی در لغو دسترسی رخ داد.',
                                    confirmButtonText: 'تایید'
                                });
                            }
                        });
                }
            });
        }
    </script>

</body>

</html>