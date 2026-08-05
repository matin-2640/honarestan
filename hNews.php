<?php
include_once("connect.php");
?>
<!doctype html>
<html lang="fa" dir="rtl">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>اخبار و رویدادها - هنرستان راه دانش</title>
  <link rel="stylesheet" href="styles/font.css">
  <link rel="stylesheet" href="styles/panel_style.css" />
  <link rel="stylesheet" href="styles/profile_style.css" />
  <link rel="stylesheet" href="styles/style.css" />
  <link rel="stylesheet" href="styles/action_styles.css" />
  <link rel="icon" href="images/icons/rahdanesh.png" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <style>
    /* استایل‌های دارک‌مود برای صفحه اخبار و مودال */
    [data-theme="dark"] body {
      background-color: #0f172a !important;
      color: #f8fafc !important;
    }

    [data-theme="dark"] .news-card {
      background-color: #1e293b !important;
      color: #f8fafc !important;
      border: 1px solid #334155 !important;
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3) !important;
    }

    [data-theme="dark"] .news-title {
      color: #f8fafc !important;
    }

    [data-theme="dark"] .news-excerpt,
    [data-theme="dark"] .news-date {
      color: #94a3b8 !important;
    }

    [data-theme="dark"] .news-modal-box {
      background-color: #1e293b !important;
      color: #f8fafc !important;
      border: 1px solid #334155 !important;
    }

    [data-theme="dark"] #modalText,
    [data-theme="dark"] #modalTitle {
      color: #f8fafc !important;
    }

    [data-theme="dark"] #modalCategory {
      background-color: #334155 !important;
      color: #f8fafc !important;
    }

    [data-theme="dark"] .close-news {
      color: #f8fafc !important;
    }

    .page-container {
      width: 100%;
      max-width: 1300px;
      margin: 30px auto;
      padding: 0 20px;
      box-sizing: border-box;
    }
  </style>
</head>

<body class="news-page">

  <header class="panel-header">
    <div class="panel-container header-wrapper">
      <div class="user-profile-brief">
        <div class="user-avatar-mini">
          <svg viewBox="0 0 24 24" class="avatar-svg-placeholder">
            <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z" />
          </svg>
        </div>
        <div class="user-info-text">
          <span>هنرستان راه دانش</span>
          <small>اخبار و اطلاعیه‌ها</small>
        </div>
      </div>

      <nav class="panel-nav" id="panelNav">
        <a href="index.php">
          <svg viewBox="0 0 24 24" class="nav-svg-icon">
            <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z" />
          </svg>
          صفحه نخست
        </a>
        <a href="#" class="active">
          <svg viewBox="0 0 24 24" class="nav-svg-icon">
            <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z" />
          </svg>
          اخبار
        </a>
      </nav>

      <div class="header-actions">
        <button class="theme-toggle" id="themeToggle" title="تغییر حالت شب و روز">
          <svg viewBox="0 0 24 24" class="theme-svg-icon" id="themeIcon">
            <path class="moon-path" d="M12.3 2a10 10 0 0 0-1.9 19.8 10 10 0 0 0 11.8-11.8A10 10 0 0 1 12.3 2z" />
          </svg>
        </button>
      </div>
    </div>
  </header>

  <main class="page-container">
    <section class="news-hero" style="margin-bottom: 25px;">
      <div class="news-hero-content">
        <span class="news-badge">اطلاع‌رسانی رسمی</span>
        <h1>آخرین اخبار و رویدادهای هنرستان</h1>
      </div>
    </section>

    <section class="news-list-section">
      <div class="news-archive-grid">
        <?php
        try {
          if (isset($connect)) {
            $news_query = $connect->query("SELECT * FROM news ORDER BY id DESC");
            while ($row = $news_query->fetch(PDO::FETCH_ASSOC)) {
              $title = $row['title'];
              $category = $row['category'];
              $content = $row['content'];
              $date = $row['created_at'];
              $image = !empty($row['image_path']) ? $row['image_path'] : 'images/default.jpg';
              ?>
              <article class="news-card" onclick="openNewsModal(
                          <?php echo htmlspecialchars(json_encode($title), ENT_QUOTES, 'UTF-8'); ?>,
                          <?php echo htmlspecialchars(json_encode($category), ENT_QUOTES, 'UTF-8'); ?>,
                          <?php echo htmlspecialchars(json_encode($date), ENT_QUOTES, 'UTF-8'); ?>,
                          <?php echo htmlspecialchars(json_encode($content), ENT_QUOTES, 'UTF-8'); ?>,
                          <?php echo htmlspecialchars(json_encode($image), ENT_QUOTES, 'UTF-8'); ?>
                      )" style="cursor: pointer;">
                <div class="news-img-wrapper" style="height: 180px; overflow: hidden;">
                  <img class="gallery-img" src="<?php echo htmlspecialchars($image); ?>"
                    alt="<?php echo htmlspecialchars($title); ?>" style="width:100%; height:100%; object-fit:cover;" />
                </div>
                <div class="news-body">
                  <div class="news-meta">
                    <span class="news-date">
                      <i class="fa-regular fa-calendar" style="margin-left: 4px;"></i>
                      <?php echo htmlspecialchars($date); ?>
                    </span>
                    <span class="news-category"><?php echo htmlspecialchars($category); ?></span>
                  </div>
                  <h3 class="news-title"><?php echo htmlspecialchars($title); ?></h3>
                  <p class="news-excerpt"><?php echo mb_substr(htmlspecialchars($content), 0, 90) . '...'; ?></p>
                </div>
              </article>
              <?php
            }
          }
        } catch (Exception $e) {
          echo "<p style='text-align: center; grid-column: 1/-1; color: var(--text-muted);'>خطا در بارگذاری اخبار.</p>";
        }
        ?>
      </div>

      <div class="news-modal" id="newsModal"
        style="display:none; position:fixed; inset:0; z-index:99999; background:rgba(0,0,0,0.85); justify-content:center; align-items:center;">
        <div class="news-modal-box"
          style="background:var(--bg-card, #fff); padding:25px; border-radius:12px; max-width:600px; width:90%; position:relative; max-height:90vh; overflow-y:auto;">
          <button class="close-news" onclick="closeNewsModal()"
            style="position:absolute; top:15px; left:20px; font-size:30px; background:none; border:none; cursor:pointer; color:var(--text-primary);">&times;</button>

          <img id="modalImage" src="" alt="" class="modal-image"
            style="width:100%; max-height:250px; object-fit:cover; border-radius:8px; margin-bottom:15px;" />

          <div class="news-modal-content">
            <div class="news-meta" style="display:flex; justify-content:space-between; margin-bottom:10px;">
              <span id="modalCategory" class="news-category"
                style="background:#e2e8f0; padding:2px 8px; border-radius:4px; font-size:12px; color:#333;"></span>
              <small id="modalDate" style="color:var(--text-muted);"></small>
            </div>
            <h2 id="modalTitle" style="margin-bottom:15px; font-size:20px; color:var(--text-primary);"></h2>
            <p id="modalText" style="line-height:1.8; color:var(--text-primary); white-space: pre-line;"></p>
          </div>
        </div>
      </div>

      <div class="back-to-home-container" style="text-align: center; margin-top: 40px;">
        <a href="index.php" class="btn-back-home"
          style="display: inline-flex; align-items: center; gap: 8px; background-color: #2563eb; color: #ffffff; text-decoration: none; padding: 12px 25px; border-radius: 8px; font-weight: bold;">
          <span>بازگشت به صفحه اصلی</span>
        </a>
      </div>
    </section>
  </main>

  <script>
    function openNewsModal(title, category, date, text, img) {
      document.getElementById('modalTitle').textContent = title;
      document.getElementById('modalCategory').textContent = category;
      document.getElementById('modalDate').textContent = date;
      document.getElementById('modalText').textContent = text;
      document.getElementById('modalImage').src = img;

      document.getElementById('newsModal').style.display = "flex";
    }

    function closeNewsModal() {
      document.getElementById('newsModal').style.display = "none";
    }
  </script>

  <script src="https://unpkg.com/lenis@1.3.11/dist/lenis.min.js"></script>
  <script type="text/javascript" src="js/theme.js"></script>
</body>

</html>