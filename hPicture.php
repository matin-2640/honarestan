<?php
include("connect.php");

// دریافت عکس‌ها از دیتابیس به ترتیب جدیدترین
$result = $connect->query("SELECT * FROM gallery ORDER BY id DESC");
?>

<!doctype html>
<html lang="fa" dir="rtl">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>گالری تصاویر هنرستان راه دانش</title>
    <link rel="stylesheet" href="styles/style.css" />
    <link rel="stylesheet" href="styles/action_styles.css" />

    <link rel="icon" href="images/icons/rahdanesh.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/vazirmatn@33.0.3/Vazirmatn-font-face.css" />
    <link rel="stylesheet" href="styles/font.css">
  </head>
  <body class="news-page">
    <main class="container">
      <section class="news-hero">
        <div class="news-hero-content">
          <span class="pic-badge">گالری تصاویر</span>
          <h1>گالری تصاویر بخش های متفاوت هنرستان</h1>
        </div>
      </section>

      <section class="news-list-section">
        <div class="news-archive-grid">
          
          <?php
          if ($result && $result->rowCount() > 0) {
              while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                  $img_path = $row['image_path'];
                  $title    = !empty($row['title']) ? $row['title'] : "تصویر هنرستان";
                  $date     = $row['created_at'];
          ?>
                  <article class="news-card">
                    <div class="news-img-wrapper">
                      <img class="gallery-img" src="<?php echo htmlspecialchars($img_path); ?>" alt="<?php echo htmlspecialchars($title); ?>" />
                    </div>
                    <div class="pic-body">
                      <h4 style="margin-bottom: 5px; font-size: 14px;"><?php echo htmlspecialchars($title); ?></h4>
                      <div class="pic-meta">
                        <span class="pic-date">
                          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-left: 3px;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                          <?php echo $date; ?>
                        </span>
                      </div>
                    </div>
                  </article>
          <?php 
              }
          } else {
              echo "<p style='text-align: center; grid-column: 1/-1; color: #777; padding: 20px;'>هنوز عکسی به گالری اضافه نشده است.</p>";
          }
          ?>

        </div>

        <div class="back-to-home-container">
          <a href="index.php" class="btn-back-home">
            <span>بازگشت به صفحه اصلی</span>
          </a>
        </div>
      </section>
    </main>

    <div id="lightbox" class="lightbox">
      <img id="lightbox-img" src="" alt="" />
    </div>
    <script src="https://unpkg.com/lenis@1.3.11/dist/lenis.min.js"></script>
    <script type="text/javascript" src="js/theme.js"></script>
  </body>
</html>
