<?php
session_start();
include_once("connect.php");

if (!(isset($_SESSION["state_login"]) && $_SESSION["type"] <= 2)) {
    header("location:login.php");
    exit();
}

$message = "";

// عملیات حذف خبر
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    try {
        $stmt_img = $connect->prepare("SELECT image_path FROM news WHERE id = ?");
        $stmt_img->execute([$id]);
        $news_item = $stmt_img->fetch(PDO::FETCH_ASSOC);
        if ($news_item && !empty($news_item['image_path']) && file_exists($news_item['image_path'])) {
            unlink($news_item['image_path']);
        }

        $stmt = $connect->prepare("DELETE FROM news WHERE id = ?");
        $stmt->execute([$id]);
        $message = "خبر با موفقیت حذف شد!";
    } catch (Exception $e) {
        $message = "خطا در حذف خبر: " . $e->getMessage();
    }
}

// عملیات افزودن خبر جدید
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $category = trim($_POST['category']);
    $content = trim($_POST['content']);
    $created_at = trim($_POST['created_at']); // دریافت تاریخ فارسی از فرم

    $image_path = "";
    if (isset($_FILES['news_image']) && $_FILES['news_image']['error'] === 0) {
        $upload_dir = "images/news/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        $file_name = time() . '_' . basename($_FILES['news_image']['name']);
        $target_file = $upload_dir . $file_name;
        if (move_uploaded_file($_FILES['news_image']['tmp_name'], $target_file)) {
            $image_path = $target_file;
        }
    }

    if (!empty($title) && !empty($content)) {
        try {
            $stmt = $connect->prepare("INSERT INTO news (title, category, content, image_path, created_at) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$title, $category, $content, $image_path, $created_at]);
            $message = "خبر با موفقیت افزوده شد!";
        } catch (Exception $e) {
            $message = "خطا در ثبت خبر: " . $e->getMessage();
        }
    } else {
        $message = "لطفاً فیلدهای ضروری را پر کنید.";
    }
}
?>
<!doctype html>
<html lang="fa" dir="rtl">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>مدیریت اخبار - هنرستان راه دانش</title>
  <link rel="stylesheet" href="styles/panel_style.css" />
  <link rel="stylesheet" href="styles/profile_style.css" />
  <link rel="stylesheet" href="styles/style.css">
  <link rel="stylesheet" href="styles/admin_news.css">
  <link rel="stylesheet" href="styles/font.css">
  <link rel="stylesheet" href="js/sweetalert2.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
      /* استایل‌های دارک‌مود کامل و هماهنگ با پنل */
      [data-theme="dark"] body {
          background-color: #0f172a !important;
          color: #f8fafc !important;
      }
      [data-theme="dark"] .admin-news-container {
          background-color: #1e293b !important;
          color: #f8fafc !important;
          box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3) !important;
          border: 1px solid #334155;
      }
      [data-theme="dark"] .form-group label {
          color: #cbd5e1 !important;
      }
      [data-theme="dark"] .form-group input,
      [data-theme="dark"] .form-group textarea {
          background-color: #0f172a !important;
          color: #f8fafc !important;
          border-color: #475569 !important;
      }
      [data-theme="dark"] .form-group input:focus,
      [data-theme="dark"] .form-group textarea:focus {
          border-color: #3b82f6 !important;
      }
      [data-theme="dark"] table {
          color: #f8fafc !important;
      }
      [data-theme="dark"] tr {
          border-bottom-color: #334155 !important;
      }
      [data-theme="dark"] thead tr {
          background: #0f172a !important;
      }
  </style>
</head>

<body class="admin-news-body">
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
                    <small>مدیریت اخبار</small>
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
                    مدیریت اخبار
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

    <div class="admin-news-container" style="max-width: 900px; margin-top: 30px;">
        <h2><i class="fa-solid fa-newspaper"></i> افزودن خبر جدید</h2>

        <?php if (!empty($message)): ?>
            <div class="admin-news-alert"><?php echo $message; ?></div>
        <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data" style="margin-bottom: 40px;">
            <div class="form-group">
                <label>عنوان خبر:</label>
                <input type="text" name="title" required placeholder="عنوان جذاب برای خبر بنویسید...">
            </div>

            <div class="form-group">
                <label>دسته‌بندی:</label>
                <input type="text" name="category" required placeholder="مثلاً: برنامه‌نویسی، اطلاعیه...">
            </div>

            <div class="form-group">
                <label>تاریخ خبر (شمسی):</label>
                <input type="text" name="created_at" value="۱۴۰۵/۰۵/۱۴" required placeholder="مثلاً ۱۴۰۵/۰۵/۱۴">
            </div>

            <div class="form-group">
                <label>تصویر خبر:</label>
                <input type="file" name="news_image" accept="image/*">
            </div>

            <div class="form-group">
                <label>متن کامل خبر:</label>
                <textarea name="content" rows="4" required placeholder="متن خبر..."></textarea>
            </div>

    <h2><i class="fa-solid fa-list"></i> لیست اخبار منتشر شده</h2>
    <div style="overflow-x: auto;">
      <table style="width:100%; border-collapse: collapse; margin-top: 15px; font-size: 0.9rem;">
        <thead>
          <tr style="background: var(--bg-main, #f1f5f9); text-align: right;">
            <th style="padding: 10px;">عنوان خبر</th>
            <th style="padding: 10px;">دسته‌بندی</th>
            <th style="padding: 10px;">تاریخ</th>
            <th style="padding: 10px; text-align: center;">عملیات</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $all_news = $connect->query("SELECT * FROM news ORDER BY id DESC");
          while ($row = $all_news->fetch(PDO::FETCH_ASSOC)) {
          ?>
            <tr style="border-bottom: 1px solid var(--border-color, #e2e8f0);">
              <td style="padding: 12px;"><?php echo htmlspecialchars($row['title']); ?></td>
              <td style="padding: 12px;"><?php echo htmlspecialchars($row['category']); ?></td>
              <td style="padding: 12px;"><?php echo htmlspecialchars($row['created_at']); ?></td>
              <td style="padding: 12px; text-align: center;">
                <a href="edit_news.php?id=<?php echo $row['id']; ?>" style="color: #2563eb; margin-left: 15px; text-decoration: none;"><i class="fa-solid fa-pen-to-square"></i> ویرایش</a>
                <button type="button" onclick="confirmDelete(<?php echo $row['id']; ?>);" style="background: none; border: none; color: #dc2626; cursor: pointer; font-size: 0.9rem; padding: 0;"><i class="fa-solid fa-trash"></i> حذف</button>
              </td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
            <div class="form-actions">
                <button type="submit" class="btn-submit">انتشار خبر</button>
                <a href="admin_panel.php" class="btn-back">بازگشت به پنل مدیریت</a>
            </div>
        </form>

        <hr style="border:0; border-top:1px solid var(--border-color, #e2e8f0); margin: 30px 0;">

        <h2><i class="fa-solid fa-list"></i> لیست اخبار منتشر شده</h2>
        <div style="overflow-x: auto;">
            <table style="width:100%; border-collapse: collapse; margin-top: 15px; font-size: 0.9rem;">
                <thead>
                    <tr style="background: var(--bg-main, #f1f5f9); text-align: right;">
                        <th style="padding: 10px;">عنوان خبر</th>
                        <th style="padding: 10px;">دسته‌بندی</th>
                        <th style="padding: 10px;">تاریخ</th>
                        <th style="padding: 10px; text-align: center;">عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $all_news = $connect->query("SELECT * FROM news ORDER BY id DESC");
                    while ($row = $all_news->fetch(PDO::FETCH_ASSOC)) {
                        ?>
                        <tr style="border-bottom: 1px solid var(--border-color, #e2e8f0);">
                            <td style="padding: 12px;"><?php echo htmlspecialchars($row['title']); ?></td>
                            <td style="padding: 12px;"><?php echo htmlspecialchars($row['category']); ?></td>
                            <td style="padding: 12px;"><?php echo htmlspecialchars($row['created_at']); ?></td>
                            <td style="padding: 12px; text-align: center;">
                                <a href="edit_news.php?id=<?php echo $row['id']; ?>"
                                    style="color: #2563eb; margin-left: 15px; text-decoration: none;"><i
                                        class="fa-solid fa-pen-to-square"></i> ویرایش</a>
                                <a href="admin_news.php?delete=<?php echo $row['id']; ?>"
                                    onclick="return confirm('آیا از حذف این خبر اطمینان دارید؟');"
                                    style="color: #dc2626; text-decoration: none;"><i class="fa-solid fa-trash"></i> حذف</a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

  <script src="js/sweetalert2.min.js"></script>
  <script src="https://unpkg.com/lenis@1.3.11/dist/lenis.min.js"></script>
  <script type="text/javascript" src="js/theme.js"></script>
  <script>
    function confirmDelete(id) {
        Swal.fire({
            title: 'آیا از حذف این خبر اطمینان دارید؟',
            text: "این عمل قابل بازگشت نیست!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'بله، حذف شود',
            cancelButtonText: 'انصراف'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'admin_news.php?delete=' + id;
            }
        });
    }
  </script>
</body>

</html>