<?php
session_start();
include_once("connect.php");

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
  <link rel="stylesheet" href="styles/style.css">
  <link rel="stylesheet" href="styles/admin_news.css">
  <link rel="stylesheet" href="styles/font.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="admin-news-body">
  <div class="admin-news-container">
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
</body>
</html>
