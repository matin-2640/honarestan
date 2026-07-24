<?php
session_start();
unset($_SESSION['error']);
?>
<!doctype html>
<html lang="fa" dir="rtl">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>هنرستان فنی حرفه ای راه دانش</title>
  <link rel="stylesheet" href="styles/style.css" />

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link rel="icon" href="images/icons/rahdanesh.png">
  <link rel="stylesheet" href="styles/font.css">
</head>

<body>
  <div id="loader">
    <div class="loader-box">
        <img src="images/icons/rahdanesh.png" class="loader-logo">
        <div class="loader-spinner"></div>
    </div>
</div>
  <header class="main-header">
    <div class="container header-wrapper">
      <div class="logo">
        <img class="honarestanlogo" src="images/logo.png" alt="Honarestan" />
        <div class="logo-text">
          <span>هنرستان راه دانش</span>
        </div>
      </div>

      <nav class="nav-menu" id="navMenu">
        <a href="#" class="active">
          <img src="images/icons/home.png" width="20px" height="20px" />
          صفحه اصلی</a>
        <a href="hNews.html">آخرین اخبار</a>
        <a href="hPicture.html">گالری تصاویر</a>
        <a href="panel.html">پنل هنرجو</a>
        <a href="teacher_panel.html">پنل معلمین</a>
        <a href="admin_panel.html">پنل مدیران</a>
      </nav>

      <div class="header-actions">
        <button class="theme-toggle" id="themeToggle" title="تغییر حالت شب و روز">
          <i class="fa-solid fa-moon"></i>
        </button>
        <a href="login.php" class="btn-portal">
          <span>پنل کاربری</span>
        </a>
        <button class="menu-toggle" id="menuToggle" aria-label="باز کردن منو">
          <i class="fa-solid fa-bars"></i>
        </button>
      </div>
    </div>
  </header>

  <main class="container">
    <section class="hero-section">
      <div class="hero-content">
        <span class="hero-badge">به دنیای مهارت و خلاقیت خوش آمدید</span>
        <h1>آموزش نوین، کلید ورود به بازار کار آینده</h1>
        <p>
          هنرستان راه دانش با بهره‌گیری از اساتید مجرب و کارگاه‌های مجهز،
          بستری پویا را برای شکوفایی استعدادهای فنی، هنری و محاسباتی هنرجویان
          عزیز فراهم کرده است تا مسیر شغلی خود را با اطمینان بسازند.
        </p>
        <div class="hero-actions">
          <a href="#majors" class="btn-main primary">آشنایی با رشته‌ها</a>
          <a href="#contact" class="btn-main outline">راه‌های ارتباطی</a>
        </div>
      </div>
      <div class="hero-visual">
        <div class="visual-wrapper">
          <i class="fa-solid fa-school hero-icon"></i>
        </div>
      </div>
    </section>

    <section class="section-padding" id="majors">
      <div class="section-header">
        <h2>رشته‌های تحصیلی هنرستان</h2>
      </div>
      <div class="majors-grid">
        <div class="major-card network-theme">
          <div class="major-icon">
            <img src="images/icons/shabake.png" width="30px" height="30px" />
          </div>
          <h3>شبکه و نرم‌افزار</h3>
          <div class="major-meta">
            <span class="student-count"> ۱۲۰ هنرجو</span>
            <span class="major-badge">فنی و مهندسی</span>
          </div>
        </div>

        <div class="major-card photo-theme">
          <div class="major-icon">
            <img src="images/icons/camera.png" width="30px" height="30px" />
          </div>
          <h3>فتوگرافیک</h3>
          <div class="major-meta">
            <span class="student-count">۸۵ هنرجو</span>
            <span class="major-badge">هنر</span>
          </div>
        </div>

        <div class="major-card accounting-theme">
          <div class="major-icon">
            <img src="images/icons/calculator.png" width="30px" height="30px" />
          </div>
          <h3>حسابداری</h3>
          <div class="major-meta">
            <span class="student-count">۹۸ هنرجو</span>
            <span class="major-badge">خدمات و مالی</span>
          </div>
        </div>
      </div>
    </section>

    <section class="section-padding" id="news">
      <div class="section-header">
        <h2>آخرین اخبار و رویدادها</h2>
      </div>
      <div class="news-grid">
        <article class="news-card">
          <div class="news-img-placeholder"></div>
          <div class="news-body">
            <span class="news-date" calendar><img src="images/icons/calendar.png" width="12px" height="12px" />
              ۲۳ تیر ۱۴۰۵</span>
            <h3>کسب رتبه اول استانی در مسابقات برنامه‌نویسی</h3>
            <p>
              هنرجویان پایه دوازدهم شبکه و نرم‌افزار موفق به کسب رتبه‌های برتر
              در المپیاد مهارتی شدند...
            </p>
            <a href="#" class="news-link">ادامه مطلب</a>
          </div>
        </article>

        <article class="news-card">
          <div class="news-img-placeholder"></div>
          <div class="news-body">
            <span class="news-date"><img src="images/icons/calendar.png" width="12px" height="12px" />۱۵ تیر ۱۴۰۵</span>
            <h3>برگزاری نمایشگاه عکس هنرجویان فتوگرافیک</h3>
            <p>
              آثار برگزیده هنرجویان خلاق رشته فتوگرافیک در نگارخانه هنرستان به
              نمایش در آمد...
            </p>
            <a href="#" class="news-link">ادامه مطلب </a>
          </div>
        </article>

        <article class="news-card">
          <div class="news-img-placeholder"></div>
          <div class="news-body">
            <span class="news-date"><img src="images/icons/calendar.png" width="12px" height="12px" />۱۰ تیر ۱۴۰۵</span>
            <h3>اطلاعیه زمان‌بندی ثبت‌نام سال تحصیلی جدید</h3>
            <p>
              شرایط، مدارک لازم و مراحل ثبت‌نام حضوری و الکترونیکی هنرجویان
              جدیدالورود اعلام شد...
            </p>
            <a href="#" class="news-link">ادامه مطلب </a>
          </div>
        </article>
      </div>
    </section>

    <section class="section-padding" id="gallery">
      <div class="section-header">
        <h2>گالری تصاویر هنرستان</h2>
      </div>
      <div class="gallery-grid">
        <div class="gallery-item">
          <div class="gallery-placeholder">
            <span>کارگاه کامپیوتر مجهز</span>
          </div>
        </div>
        <div class="gallery-item">
          <div class="gallery-placeholder">
            <span>آتلیه تخصصی عکاسی</span>
          </div>
        </div>
        <div class="gallery-item">
          <div class="gallery-placeholder">
            <span>حیاط و فضای سبز هنرستان</span>
          </div>
        </div>
        <div class="gallery-item">
          <div class="gallery-placeholder">
            <span>کتابخانه و سالن مطالعه</span>
          </div>
        </div>
        <div class="gallery-item">
          <div class="gallery-placeholder">
            <span>کارگاه عملی حسابداری</span>
          </div>
        </div>
        <div class="gallery-item">
          <div class="gallery-placeholder">
            <span>جشنواره‌های دانش‌آموزی</span>
          </div>
        </div>
      </div>
    </section>

    <section class="section-padding" id="portals">
      <div class="section-header">
        <h2>سامانه‌های آموزشی مرتبط</h2>
      </div>
      <div class="portals-grid">
        <a href="https://my.medu.ir" target="_blank" class="portal-card">
          <div class="portal-icon">
            <img src="images/icons/portal.png" width="55px" height="55px" />
          </div>
          <h4>مای مدیو (My Medu)</h4>
        </a>
        <a href="https://sida.medu.ir" target="_blank" class="portal-card">
          <div class="portal-icon">
            <img src="images/icons/portal.png" width="55px" height="55px" />
          </div>
          <h4>سامانه سیدا (Sida)</h4>
        </a>
        <a href="https://web.shad.ir/" target="_blank" class="portal-card">
          <div class="portal-icon">
            <img src="images/icons/portal.png" width="55px" height="55px" />
          </div>
          <h4>شبکه آموزشی شاد</h4>
        </a>
      </div>
    </section>

    <section class="section-padding" id="location">
      <div class="section-header">
        <h2>موقعیت جغرافیایی ما</h2>
      </div>
      <div class="location-wrapper">
        <div class="location-info">
          <h3>نشانی هنرستان</h3>
          <p>
            <img src="images/icons/map.png" width="18px" height="18px" />
            مریوان - میدان معلم
          </p>
          <p>
            <img src="images/icons/phone.png" width="18px" height="18px" />
            شماره تماس: 34542002-087
          </p>
          <p>
            <img src="images/icons/work.png" width="18px" height="18px" />
            ساعت کاری: دوشنبه و چهارشنبه (8:00 الی 12:30)
          </p>
        </div>
        <div class="map-container">
          <div class="map-placeholder">
            <span>نقشه تعاملی جهت مسیریابی آسان</span>
          </div>
        </div>
      </div>
    </section>

    <section class="section-padding" id="designers">
      <div class="section-header">
        <h2>معماران و سازندگان وب‌سایت</h2>
      </div>
      <div class="team-grid">
        <div class="developer-card dev-kamyar">
          <div class="dev-avatar">
            <img class="designers_pic" src="images/icons/manager.png" width="55px" height="55px" />
          </div>
          <h3>کامیار حیدری</h3>
          <p class="dev-role">مدیر و معمار ارشد پروژه (Project Manager)</p>
          <div class="dev-socials">
            <a href="#"><img src="images/icons/github.png" width="11px" height="11px" /></a>
          </div>
        </div>

        <div class="developer-card dev-monib">
          <div class="dev-avatar">
            <img class="designers_pic" src="images/icons/programmerMo.png" width="55px" height="55px" />
          </div>
          <h3>منیب رحیمی</h3>
          <p class="dev-role">طراح فول استک وبسایت</p>
          <div class="dev-socials">
            <a href="#"><img src="images/icons/github.png" width="11px" height="11px" /></a>
          </div>
        </div>

        <div class="developer-card dev-matin">
          <div class="dev-avatar">
            <img src="images/icons/programmerMa.png" width="55px" height="55px" class="designers_pic" />
          </div>
          <h3>متین کریمی</h3>
          <p class="dev-role">طراح فول استک وبسایت</p>
          <div class="dev-socials">
            <a href="#"><img src="images/icons/github.png" width="11px" height="11px" /></a>
          </div>
        </div>
      </div>
    </section>
  </main>

  <footer class="main-footer" id="contact">
    <div class="container footer-grid">
      <div class="footer-about">
        <h3>هنرستان فنی مهارتی</h3>
        <p>
          ما متعهد به تربیت نسلی از متخصصان خلاق و کارآفرین هستیم که با دانش
          تئوری قوی و تجربه عملی ارزشمند وارد بازارهای رقابتی کشور می‌شوند.
        </p>
      </div>
      <div class="footer-links">
        <h4>لینک‌های کاربردی</h4>
        <ul>
          <li>
            <a href="#"><img src="images/icons/Chevron-left.png" width="15px" height="15px" />
              پرتال اولیا و مربیان</a>
          </li>
          <li>
            <a href="#"><img src="images/icons/Chevron-left.png" width="15px" height="15px" />
              جدول زمان‌بندی هفتگی کلاسی</a>
          </li>
          <li>
            <a href="#"><img src="images/icons/Chevron-left.png" width="15px" height="15px" />
              آیین‌نامه انضباطی هنرجویان</a>
          </li>
          <li>
            <a href="#"><img src="images/icons/Chevron-left.png" width="15px" height="15px" />
              تقویم اجرایی سالانه</a>
          </li>
        </ul>
      </div>
      <div class="footer-social">
        <h4>شبکه‌های ارتباطی هنرستان</h4>
        <div class="social-icons">
          <a href="#" title="تلگرام"><img src="images/icons/telegram.png" width="19px" height="19px" /></a>
          <a href="#" title="شاد"><img src="images/icons/shad.png" width="19px" height="19px" /></a>
          <a href="#" title="تماس"><img src="images/icons/call.png" width="19px" height="19px" /></a>
        </div>
        <p class="tel-info">
          <img src="images/icons/phone.png" width="15px" height="15px" /> واحد
          پشتیبانی: 34542002-087
        </p>
      </div>
    </div>
    <div class="footer-bottom">
      <div class="container footer-bottom-wrapper">
        <p>
          © ۱۴۰۵ تمامی حقوق مادی و معنوی این پرتال برای هنرستان محفوظ است.
        </p>
      </div>
    </div>
  </footer>

  <script src="https://unpkg.com/lenis@1.3.11/dist/lenis.min.js"></script>
  <script type="text/javascript" src="js/theme.js"></script>
</body>

</html>