<?php
session_start();
include_once("connect.php");

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
  <link rel="stylesheet" href="styles/style.css">
  <link rel="stylesheet" href="styles/admin_news.css">
  <link rel="stylesheet" href="styles/font.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="admin-news-body">
  <div class="admin-news-container" style="max-width: 900px;">
    <h2><i class="fa-solid fa-newspaper"></i> افزودن خبر جدید</h2>
    
    <?php if(!empty($message)): ?>
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
                <a href="edit_news.php?id=<?php echo $row['id']; ?>" style="color: #2563eb; margin-left: 15px; text-decoration: none;"><i class="fa-solid fa-pen-to-square"></i> ویرایش</a>
                <a href="admin_news.php?delete=<?php echo $row['id']; ?>" onclick="return confirm('آیا از حذف این خبر اطمینان دارید؟');" style="color: #dc2626; text-decoration: none;"><i class="fa-solid fa-trash"></i> حذف</a>
              </td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
  </div>
</body>
</html>
