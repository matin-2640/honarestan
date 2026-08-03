<?php
include_once("connect.php");
?>
<!doctype html>
<html lang="fa" dir="rtl">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>اخبار و رویدادها - هنرستان راه دانش</title>
    <link rel="stylesheet" href="styles/style.css" />
    <link rel="stylesheet" href="styles/action_styles.css" />
    <link rel="icon" href="images/icons/rahdanesh.png" />
    <link rel="stylesheet" href="styles/font.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  </head>
  <body class="news-page">
    <main class="container">
      <section class="news-hero">
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
                      $title = htmlspecialchars($row['title']);
                      $category = htmlspecialchars($row['category']);
                      $content = htmlspecialchars($row['content']);
                      $date = htmlspecialchars($row['created_at']);
                      $image = !empty($row['image_path']) ? htmlspecialchars($row['image_path']) : 'images/default.jpg';
          ?>
                      <article class="news-card" onclick="openNewsModal(this)" 
                               data-title="<?php echo $title; ?>" 
                               data-category="<?php echo $category; ?>" 
                               data-date="<?php echo $date; ?>" 
                               data-text="<?php echo $content; ?>" 
                               data-img="<?php echo $image; ?>" style="cursor: pointer;">
                        <div class="news-img-wrapper" style="height: 180px; overflow: hidden;">
                          <img class="gallery-img" src="<?php echo $image; ?>" alt="<?php echo $title; ?>" style="width:100%; height:100%; object-fit:cover;" />
                        </div>
                        <div class="news-body">
                          <div class="news-meta">
                            <span class="news-date">
                              <img src="images/icons/calendar.png" width="12px" height="12px" />
                              <?php echo $date; ?>
                            </span>
                            <span class="news-category"><?php echo $category; ?></span>
                          </div>
                          <h3 class="news-title"><?php echo $title; ?></h3>
                          <p class="news-excerpt"><?php echo mb_substr($content, 0, 90) . '...'; ?></p>
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

        <div class="news-modal" id="newsModal" style="display:none; position:fixed; inset:0; z-index:99999; background:rgba(0,0,0,0.85); justify-content:center; align-items:center;">
          <div class="news-modal-box" style="background:var(--bg-card, #fff); padding:25px; border-radius:12px; max-width:600px; width:90%; position:relative; max-height:90vh; overflow-y:auto;">
            <button class="close-news" onclick="closeNewsModal()" style="position:absolute; top:15px; left:20px; font-size:30px; background:none; border:none; cursor:pointer; color:var(--text-primary);">&times;</button>

            <img id="modalImage" src="" alt="" class="modal-image" style="width:100%; max-height:250px; object-fit:cover; border-radius:8px; margin-bottom:15px;" />

            <div class="news-modal-content">
              <div class="news-meta" style="display:flex; justify-content:space-between; margin-bottom:10px;">
                <span id="modalCategory" class="news-category" style="background:#e2e8f0; padding:2px 8px; border-radius:4px; font-size:12px; color:#333;"></span>
                <small id="modalDate" style="color:var(--text-muted);"></small>
              </div>
              <h2 id="modalTitle" style="margin-bottom:15px; font-size:20px; color:var(--text-primary);"></h2>
              <p id="modalText" style="line-height:1.8; color:var(--text-primary);"></p>
            </div>
          </div>
        </div>

        <div class="back-to-home-container" style="text-align: center; margin-top: 40px;">
          <a href="index.php" class="btn-back-home">
            <span>بازگشت به صفحه اصلی</span>
          </a>
        </div>
      </section>
    </main>

    <script>
      function openNewsModal(card) {
        const title = card.getAttribute('data-title');
        const category = card.getAttribute('data-category');
        const date = card.getAttribute('data-date');
        const text = card.getAttribute('data-text');
        const img = card.getAttribute('data-img');

        document.getElementById('modalTitle').innerText = title;
        document.getElementById('modalCategory').innerText = category;
        document.getElementById('modalDate').innerText = date;
        document.getElementById('modalText').innerText = text;
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
