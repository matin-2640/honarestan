<?php
session_start();
include_once("connect.php");

if (!(isset($_SESSION["state_login"]) && $_SESSION["type"] <= 2)) {
    header("location:login.php");
    exit();
}

$message = "";
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$stmt = $connect->prepare("SELECT * FROM news WHERE id = ?");
$stmt->execute([$id]);
$news = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$news) {
    echo "خبر مورد نظر یافت نشد.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $category = trim($_POST['category']);
    $content = trim($_POST['content']);
    $created_at = trim($_POST['created_at']);
    $image_path = $news['image_path'];

    if (isset($_FILES['news_image']) && $_FILES['news_image']['error'] === 0) {
        $upload_dir = "images/news/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        $file_name = time() . '_' . basename($_FILES['news_image']['name']);
        $target_file = $upload_dir . $file_name;
        if (move_uploaded_file($_FILES['news_image']['tmp_name'], $target_file)) {
            if (!empty($news['image_path']) && file_exists($news['image_path'])) {
                unlink($news['image_path']);
            }
            $image_path = $target_file;
        }
    }

    if (!empty($title) && !empty($content)) {
        try {
            $update_stmt = $connect->prepare("UPDATE news SET title = ?, category = ?, content = ?, image_path = ?, created_at = ? WHERE id = ?");
            $update_stmt->execute([$title, $category, $content, $image_path, $created_at, $id]);
            $message = "خبر با موفقیت ویرایش شد!";
            
            $stmt->execute([$id]);
            $news = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $message = "خطا در ویرایش خبر: " . $e->getMessage();
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
  <title>ویرایش خبر - هنرستان راه دانش</title>
  <link rel="stylesheet" href="styles/panel_style.css" />
  <link rel="stylesheet" href="styles/profile_style.css" />
  <link rel="stylesheet" href="styles/style.css">
  <link rel="stylesheet" href="styles/admin_news.css">
  <link rel="stylesheet" href="styles/font.css">
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
                  <small>ویرایش خبر</small>
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
                  ویرایش خبر
              </a>
              <a href="admin_news.php" class="back-link-btn">
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

  <div class="admin-news-container" style="margin-top: 30px;">
    <h2><i class="fa-solid fa-pen-to-square"></i> ویرایش خبر</h2>
    
    <?php if(!empty($message)): ?>
      <div class="admin-news-alert"><?php echo $message; ?></div>
    <?php endif; ?>
    
    <form action="" method="POST" enctype="multipart/form-data">
      <div class="form-group">
        <label>عنوان خبر:</label>
        <input type="text" name="title" value="<?php echo htmlspecialchars($news['title']); ?>" required>
      </div>

      <div class="form-group">
        <label>دسته‌بندی:</label>
        <input type="text" name="category" value="<?php echo htmlspecialchars($news['category']); ?>" required>
      </div>

      <div class="form-group">
        <label>تاریخ خبر (شمسی):</label>
        <input type="text" name="created_at" value="<?php echo htmlspecialchars($news['created_at']); ?>" required>
      </div>

      <div class="form-group">
        <label>تصویر فعلی:</label>
        <?php if(!empty($news['image_path'])): ?>
          <div style="margin-bottom: 10px;">
            <img src="<?php echo htmlspecialchars($news['image_path']); ?>" alt="تصویر خبر" style="max-height: 100px; border-radius: 6px;">
          </div>
        <?php endif; ?>
        <input type="file" name="news_image" accept="image/*">
      </div>

      <div class="form-group">
        <label>متن کامل خبر:</label>
        <textarea name="content" rows="6" required><?php echo htmlspecialchars($news['content']); ?></textarea>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn-submit">ذخیره تغییرات</button>
        <a href="admin_news.php" class="btn-back">بازگشت به مدیریت اخبار</a>
      </div>
    </form>
  </div>

  <script src="https://unpkg.com/lenis@1.3.11/dist/lenis.min.js"></script>
  <script type="text/javascript" src="js/theme.js"></script>
</body>
</html>
