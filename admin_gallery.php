<?php
session_start();
include_once("connect.php");

if (!(isset($_SESSION["state_login"]) && $_SESSION["type"] <= 2)) {
    header("location:login.php");
    exit();
}

// تابع ساده برای دریافت تاریخ امروز شمسی (در صورت نداشتن تابع سراسری)
function getJalaliDate()
{
    return "۱۴۰۵/۰۵/۱۳"; // نمونه تاریخ شمسی
}

// ۱. افزودن آلبوم جدید و چندین عکس
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_album'])) {
    $title = trim($_POST['title']);
    $jalali_date = getJalaliDate();

    if (!empty($title) && isset($_FILES['images'])) {
        try {
            $stmt = $connect->prepare("INSERT INTO gallery_albums (title, created_at) VALUES (?, ?)");
            $stmt->execute([$title, $jalali_date]);
            $album_id = $connect->lastInsertId();

            // بررسی و آپلود فایل‌ها
            foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                    $file_name = time() . '_' . basename($_FILES['images']['name'][$key]);
                    $upload_dir = 'images/uploads/';

                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }

                    $destination = $upload_dir . $file_name;
                    if (move_uploaded_file($tmp_name, $destination)) {
                        $stmt_img = $connect->prepare("INSERT INTO gallery_images (album_id, image_path) VALUES (?, ?)");
                        $stmt_img->execute([$album_id, $destination]);
                    }
                }
            }
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire('موفق', 'آلبوم با موفقیت ثبت شد!', 'success').then(() => {
                        window.location.href='admin_gallery.php';
                    });
                });
            </script>";
        } catch (Exception $e) {
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire('خطا', 'مشکلی در ثبت آلبوم به وجود آمد.', 'error');
                });
            </script>";
        }
    }
}

// ۲. حذف کل آلبوم
if (isset($_GET['delete_album'])) {
    $del_id = intval($_GET['delete_album']);
    try {
        $stmt_files = $connect->prepare("SELECT image_path FROM gallery_images WHERE album_id = ?");
        $stmt_files->execute([$del_id]);
        while ($row = $stmt_files->fetch(PDO::FETCH_ASSOC)) {
            if (file_exists($row['image_path'])) {
                unlink($row['image_path']);
            }
        }
        $stmt_del = $connect->prepare("DELETE FROM gallery_albums WHERE id = ?");
        $stmt_del->execute([$del_id]);
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire('حذف شد', 'آلبوم با موفقیت حذف گردید.', 'success').then(() => {
                    window.location.href='admin_gallery.php';
                });
            });
        </script>";
    } catch (Exception $e) {
        // خطا
    }
}

// ۳. حذف تکی عکس
if (isset($_GET['delete_img'])) {
    $img_id = intval($_GET['delete_img']);
    $stmt_img = $connect->prepare("SELECT image_path FROM gallery_images WHERE id = ?");
    $stmt_img->execute([$img_id]);
    $img_row = $stmt_img->fetch(PDO::FETCH_ASSOC);
    if ($img_row) {
        if (file_exists($img_row['image_path'])) {
            unlink($img_row['image_path']);
        }
        $connect->prepare("DELETE FROM gallery_images WHERE id = ?")->execute([$img_id]);
        echo "<script>window.location.href='admin_gallery.php';</script>";
    }
}
?>
<!doctype html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>مدیریت گالری تصاویر | پورتال هنرستان</title>
    <link rel="stylesheet" href="styles/panel_style.css" />
    <link rel="stylesheet" href="styles/profile_style.css" />
    <link rel="stylesheet" href="styles/font.css">
    <link rel="stylesheet" href="styles/style.css" />
    <link rel="icon" href="images/icons/rahdanesh.png">
    <link rel="stylesheet" href="styles/admin_gallery.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="js/sweetalert2.min.css">
    <script src="js/sweetalert2.min.js"></script>
    <link rel="icon" href="images/icons/rahdanesh.png">
    <style>
        /* استایل‌های دارک‌مود کامل و هماهنگ با پنل مدیریت */
        [data-theme="dark"] body {
            background-color: #0f172a !important;
            color: #f8fafc !important;
        }

        [data-theme="dark"] main.container>div {
            background-color: #1e293b !important;
            color: #f8fafc !important;
            border-color: #334155 !important;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3) !important;
        }

        [data-theme="dark"] input[type="text"] {
            background-color: #0f172a !important;
            color: #f8fafc !important;
            border-color: #475569 !important;
        }

        [data-theme="dark"] input[type="text"]:focus {
            border-color: #3b82f6 !important;
        }

        [data-theme="dark"] label {
            color: #cbd5e1 !important;
        }
    </style>
</head>

<body>
    <header class="panel-header">
        <div class="panel-container header-wrapper">
            <div class="user-profile-brief">
                <div class="user-avatar-mini">
                    <svg viewBox="0 0 24 24" class="avatar-svg-placeholder">
                        <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z" />
                    </svg>
                </div>
                <div class="user-info-text">
                    <span>پنل مدیریت هنرستان</span>
                    <small>مدیریت گالری تصاویر</small>
                </div>
            </div>

            <nav class="panel-nav" id="panelNav">
                <a href="admin_panel.php">
                    <svg viewBox="0 0 24 24" class="nav-svg-icon">
                        <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z" />
                    </svg>
                    صفحه نخست
                </a>
                <a href="#" class="active">
                    <svg viewBox="0 0 24 24" class="nav-svg-icon">
                        <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z" />
                    </svg>
                    گالری تصاویر
                </a>
                <a href="admin_panel.php" class="back-link-btn">
                    <svg viewBox="0 0 24 24" class="nav-svg-icon">
                        <path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z" />
                    </svg>
                    بازگشت
                </a>
            </nav>

            <div class="header-actions">
                <button class="theme-toggle" id="themeToggle" title="تغییر حالت شب و روز">
                    <svg viewBox="0 0 24 24" class="theme-svg-icon" id="themeIcon">
                        <path class="moon-path"
                            d="M12.3 2a10 10 0 0 0-1.9 19.8 10 10 0 0 0 11.8-11.8A10 10 0 0 1 12.3 2z" />
                    </svg>
                </button>
            </div>
        </div>
    </header>

    <main class="container" style="padding: 40px 20px;">
        <div class="section-header">
            <h2>مدیریت گالری تصاویر</h2>
            <p>افزودن آلبوم جدید همراه با چند عکس و مدیریت آلبوم‌ها</p>
        </div>

        <div
            style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 25px; margin-bottom: 40px;">
            <h3 style="margin-bottom: 15px;">افزودن آلبوم تصویر جدید</h3>
            <form action="" method="POST" enctype="multipart/form-data"
                style="display: flex; flex-direction: column; gap: 15px;">
                <div>
                    <label style="display: block; margin-bottom: 5px;">عنوان آلبوم:</label>
                    <input type="text" name="title" required
                        style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border-color); background: var(--bg-main); color: var(--text-primary);">
                </div>

                <div id="imageInputsContainer" style="display: flex; flex-direction: column; gap: 10px;">
                    <label style="display: block; margin-bottom: 5px;">تصاویر آلبوم:</label>
                    <div class="image-input-row" style="display: flex; gap: 10px; align-items: center;">
                        <input type="file" name="images[]" accept="image/*" required style="padding: 8px; flex: 1;">
                        <button type="button" onclick="addImageInput()"
                            style="background: #10b981; color: white; border: none; padding: 10px 15px; border-radius: 8px; cursor: pointer;"><i
                                class="fa-solid fa-plus"></i></button>
                    </div>
                </div>

                <button type="submit" name="submit_album" class="btn-main primary"
                    style="width: fit-content; margin-top: 10px;">انتشار آلبوم</button>
            </form>
        </div>

        <div
            style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 25px;">
            <h3>لیست آلبوم‌ها و مدیریت تصاویر</h3>
            <div style="margin-top: 20px; display: flex; flex-direction: column; gap: 25px;">
                <?php
                $all_albums = $connect->query("SELECT * FROM gallery_albums ORDER BY id DESC");
                while ($album = $all_albums->fetch(PDO::FETCH_ASSOC)) {
                    $album_id = $album['id'];
                    ?>
                    <div
                        style="border: 1px solid var(--border-color); border-radius: 8px; padding: 15px; background: var(--bg-main);">
                        <div
                            style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">
                            <h4 style="margin: 0;"><?php echo htmlspecialchars($album['title']); ?> (تاریخ:
                                <?php echo $album['created_at']; ?>)</h4>
                            <a href="admin_gallery.php?delete_album=<?php echo $album_id; ?>"
                                onclick="return confirm('آیا مطمئن هستید که کل این آلبوم حذف شود؟');"
                                style="background: #ef4444; color: white; padding: 5px 12px; border-radius: 6px; text-decoration: none; font-size: 13px;">حذف
                                کل آلبوم</a>
                        </div>

                        <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                            <?php
                            $imgs = $connect->prepare("SELECT * FROM gallery_images WHERE album_id = ?");
                            $imgs->execute([$album_id]);
                            while ($img = $imgs->fetch(PDO::FETCH_ASSOC)) {
                                ?>
                                <div style="position: relative; width: 90px; height: 90px;">
                                    <img src="<?php echo $img['image_path']; ?>"
                                        style="width: 100%; height: 100%; object-fit: cover; border-radius: 6px; border: 1px solid var(--border-color);" />
                                    <a href="admin_gallery.php?delete_img=<?php echo $img['id']; ?>" title="حذف این عکس"
                                        style="position: absolute; top: -5px; right: -5px; background: #ef4444; color: white; width: 22px; height: 22px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 11px; text-decoration: none;">&times;</a>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </main>

    <script src="https://unpkg.com/lenis@1.3.11/dist/lenis.min.js"></script>
    <script type="text/javascript" src="js/theme.js"></script>
    <script>
        // تابع اضافه کردن فیلد آپلود عکس جدید با زدن دکمه پلاس
        function addImageInput() {
            const container = document.getElementById('imageInputsContainer');
            const row = document.createElement('div');
            row.className = 'image-input-row';
            row.style.display = 'flex';
            row.style.gap = '10px';
            row.style.alignItems = 'center';
            row.style.marginTop = '8px';

            row.innerHTML = `
        <input type="file" name="images[]" accept="image/*" required style="padding: 8px; flex: 1;">
        <button type="button" onclick="this.parentElement.remove()" style="background: #ef4444; color: white; border: none; padding: 10px 14px; border-radius: 8px; cursor: pointer;"><i class="fa-solid fa-minus"></i></button>
      `;
            container.appendChild(row);
        }
    </script>
</body>

</html>