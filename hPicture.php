<?php
include_once("connect.php");
?>
<!doctype html>
<html lang="fa" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>گالری تصاویر - هنرستان راه دانش</title>
  <link rel="stylesheet" href="styles/style.css" />
  <link rel="stylesheet" href="styles/action_styles.css" />
  <link rel="stylesheet" href="styles/admin_gallery.css" />
  <link rel="icon" href="images/icons/rahdanesh.png" />
  <link rel="stylesheet" href="styles/font.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>
<body class="news-page">
  <main class="container">
    <section class="news-hero">
      <div class="news-hero-content">
        <span class="news-badge">آرشیو تصویری</span>
        <h1>گالری تصاویر و رویدادهای هنرستان</h1>
      </div>
    </section>

    <section class="news-list-section">
      <div class="gallery-grid">
        <?php
        try {
            if (isset($connect)) {
                $albums = $connect->query("SELECT * FROM gallery_albums ORDER BY id DESC");
                while ($album = $albums->fetch(PDO::FETCH_ASSOC)) {
                    $album_id = $album['id'];
                    $album_title = !empty($album['title']) ? htmlspecialchars($album['title'], ENT_QUOTES) : "تصاویر هنرستان";
                    $album_date = htmlspecialchars($album['created_at'], ENT_QUOTES);

                    $stmt_img = $connect->prepare("SELECT * FROM gallery_images WHERE album_id = ?");
                    $stmt_img->execute([$album_id]);
                    $images = $stmt_img->fetchAll(PDO::FETCH_ASSOC);
        ?>
                    <article class="index-gallery-card">
                      <div class="custom-slider" data-index="0">
                        <div class="slides-container index-img-box">
                          <?php if (count($images) > 0): ?>
                            <?php foreach ($images as $index => $img): ?>
                              <img class="index-gallery-img slide-img <?php echo $index === 0 ? 'active' : ''; ?>" 
                                   src="<?php echo htmlspecialchars($img['image_path'], ENT_QUOTES); ?>" 
                                   alt="<?php echo $album_title; ?>" 
                                   onclick="openModal(this)" />
                            <?php endforeach; ?>
                          <?php else: ?>
                            <img class="index-gallery-img slide-img active" src="images/default.jpg" alt="پیش‌فرض" />
                          <?php endif; ?>
                        </div>
                        
                        <?php if (count($images) > 1): ?>
                          <button type="button" class="slider-btn prev-btn" onclick="changeSlide(this, -1)">&#10094;</button>
                          <button type="button" class="slider-btn next-btn" onclick="changeSlide(this, 1)">&#10095;</button>
                        <?php endif; ?>
                      </div>

                      <div class="index-pic-body">
                        <h4><?php echo $album_title; ?></h4>
                        <div class="index-pic-meta">
                          <span>
                            <i class="fa-regular fa-calendar" style="margin-left: 4px;"></i>
                            <?php echo $album_date; ?>
                          </span>
                        </div>
                      </div>
                    </article>
        <?php 
                }
            }
        } catch (Exception $e) {
            echo "<p style='text-align: center; grid-column: 1/-1; color: var(--text-muted);'>خطا در بارگذاری گالری.</p>";
        }
        ?>
      </div>

      <div class="back-to-home-container" style="text-align: center; margin-top: 40px;">
        <a href="index.php" class="btn-back-home">
          <span>بازگشت به صفحه اصلی</span>
        </a>
      </div>
    </section>
  </main>

  <div id="imageModal" style="display:none; position:fixed; inset:0; z-index:99999; background:rgba(0,0,0,0.85); justify-content:center; align-items:center;">
    <span onclick="closeModal()" style="position:absolute; top:20px; right:25px; color:#fff; font-size:35px; cursor:pointer; z-index:10;">&times;</span>
    
    <button type="button" onclick="modalChangeSlide(-1)" style="position:absolute; right:20px; background:rgba(255,255,255,0.2); border:none; color:white; font-size:24px; padding:10px 15px; cursor:pointer; border-radius:50%;">&#10094;</button>
    
    <img id="modalImage" src="" alt="تصویر بزرگ" style="max-width:80%; max-height:85vh; object-fit:contain; border-radius:8px;">
    
    <button type="button" onclick="modalChangeSlide(1)" style="position:absolute; left:20px; background:rgba(255,255,255,0.2); border:none; color:white; font-size:24px; padding:10px 15px; cursor:pointer; border-radius:50%;">&#10095;</button>
  </div>

  <script>
    let currentModalImages = [];
    let currentModalIndex = 0;

    function changeSlide(button, direction) {
      const slider = button.closest('.custom-slider');
      const slides = slider.querySelectorAll('.slide-img');
      let currentIndex = parseInt(slider.getAttribute('data-index'));

      slides[currentIndex].classList.remove('active');
      currentIndex += direction;
      if (currentIndex >= slides.length) {
        currentIndex = 0;
      } else if (currentIndex < 0) {
        currentIndex = slides.length - 1;
      }
      slides[currentIndex].classList.add('active');
      slider.setAttribute('data-index', currentIndex);
    }

    function openModal(imgElement) {
      const card = imgElement.closest('.index-gallery-card') || imgElement.closest('.custom-slider');
      const imgElements = card.querySelectorAll('.slide-img, .index-gallery-img');
      
      currentModalImages = Array.from(imgElements).map(img => img.src);
      currentModalIndex = currentModalImages.indexOf(imgElement.src);

      const modal = document.getElementById('imageModal');
      const modalImg = document.getElementById('modalImage');
      modal.style.display = "flex";
      modalImg.src = currentModalImages[currentModalIndex];
    }

    function modalChangeSlide(direction) {
      currentModalIndex += direction;
      if (currentModalIndex >= currentModalImages.length) {
        currentModalIndex = 0;
      } else if (currentModalIndex < 0) {
        currentModalIndex = currentModalImages.length - 1;
      }
      document.getElementById('modalImage').src = currentModalImages[currentModalIndex];
    }

    function closeModal() {
      document.getElementById('imageModal').style.display = "none";
    }
  </script>

  <script src="https://unpkg.com/lenis@1.3.11/dist/lenis.min.js"></script>
  <script type="text/javascript" src="js/theme.js"></script>
</body>
</html>
