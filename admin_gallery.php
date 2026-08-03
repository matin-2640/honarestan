<?php
include("connect.php");

// آپلود عکس جدید
if (isset($_POST['submit_img'])) {
    $title = trim($_POST['title']);
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $file_name = $_FILES['image']['name'];
        $file_tmp  = $_FILES['image']['tmp_name'];
        
        $folder = "images/gallery/";
        if (!is_dir($folder)) {
            mkdir($folder, 0777, true);
        }
        
        $new_name = time() . "_" . $file_name;
        $target_path = $folder . $new_name;
        
        if (move_uploaded_file($file_tmp, $target_path)) {
            $sql = "INSERT INTO gallery (image_path, title) VALUES ('$target_path', '$title')";
            if ($connect->query($sql)) {
                header("Location: admin_gallery.php");
                exit();
            }
        }
    }
}

// حذف عکس
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    
    $sel = $connect->query("SELECT image_path FROM gallery WHERE id = $id");
    $row = $sel->fetch(PDO::FETCH_ASSOC);
    
    if ($row) {
        $file_path = $row['image_path'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
        $connect->query("DELETE FROM gallery WHERE id = $id");
    }
    
    header("Location: admin_gallery.php");
    exit();
}

$all_images = $connect->query("SELECT * FROM gallery ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مدیریت گالری تصاویر - پنل ادمین</title>
    <link rel="stylesheet" href="styles/admin_gallery.css">
      <link rel="icon" href="images/icons/rahdanesh.png">
    <link rel="stylesheet" href="js/sweetalert2.min.css">
</head>
<body>

<div class="admin-container">

    <div class="admin-header">
        <h1>
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle;"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
            مدیریت گالری تصاویر هنرستان
        </h1>
        <a href="hPicture.php" class="link-public">مشاهده صفحه عمومی گالری</a>
    </div>

    <div class="card-box">
        <h3>افزودن عکس جدید به گالری</h3>
        
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>عنوان تصویر (اختیاری):</label>
                <input type="text" name="title" placeholder="مثلاً اردو تفریحی، کارگاه کامپیوتر...">
            </div>

            <div class="form-group">
                <label>انتخاب عکس:</label>
                <input type="file" name="image" accept="image/*" required>
            </div>

            <button type="submit" name="submit_img" class="btn">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                آپلود و ذخیره عکس
            </button>
        </form>
    </div>

    <div class="card-box">
        <h3>عکس‌های موجود در گالری</h3>
        
        <div class="gallery-grid">
            <?php if ($all_images && $all_images->rowCount() > 0): ?>
                <?php while ($img = $all_images->fetch(PDO::FETCH_ASSOC)): ?>
                    <div class="image-card">
                        <img src="<?php echo htmlspecialchars($img['image_path']); ?>" alt="تصویر">
                        <div class="image-card-body">
                            <p><?php echo htmlspecialchars($img['title'] ? $img['title'] : 'بدون عنوان'); ?></p>
                            <button type="button" class="btn-delete" onclick="confirmDelete(<?php echo $img['id']; ?>)">
                                حذف
                            </button>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-photo">
                    <p>هنوز هیچ عکسی در گالری ثبت نشده است.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<script src="js/sweetalert2.min.js"></script>
<script>
function confirmDelete(id) {
    Swal.fire({
        title: 'آیا مطمئن هستید؟',
        text: "این عکس برای همیشه حذف خواهد شد!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'بله، حذف شود',
        cancelButtonText: 'انصراف'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'admin_gallery.php?delete_id=' + id;
        }
    });
}
</script>

</body>
</html>
