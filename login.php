<?php
session_start();

if (isset($_SESSION["state_login"]) && $_SESSION["state_login"] == true) {
  switch ($_SESSION["type"]) {

    case 0:
      header('location:panel.php');
      break;

    case 1:
      header('location:teacher_panel.php');
      break;

    case 2:
      header('location:admin_panel.php');
      break;

    case 3:
      header('location:admin_panel.php');
      break;

    case 4:
      header('location:admin_panel.php');
      break;
  }
}

?>
<!doctype html>
<html lang="fa" dir="rtl">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>ورود به پنل کاربری - هنرستان راه دانش</title>
  <link rel="stylesheet" href="styles/style.css" />
  <link rel="stylesheet" href="styles/action_styles.css" />

  <link rel="icon" href="images/icons/rahdanesh.png">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/vazirmatn@33.0.3/Vazirmatn-font-face.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>

<body class="login-page">
  <div class="login-wrapper">
    <div class="login-tooltip">
      <i class="fa-solid fa-circle-info"></i>
      <span>هنرجویان گرامی؛ نام کاربری و رمز عبور پیش‌فرض شما همان
        <strong>کد ملی</strong> است.</span>
    </div>
    <div class="login-container">
      <a href="index.php" class="back-home" title="بازگشت به صفحه اصلی">
        <i class="fa-solid fa-arrow-right"></i>
      </a>

      <div class="heading">
        <h2>ورود به پنل کاربری</h2>
      </div>

      <form class="form" action="login_back.php" method="post">
        <div class="input-group">
          <i class="fa-regular fa-user input-icon"></i>
          <input placeholder="نام کاربری (کد ملی)" id="username" name="username" type="text" class="input"
            required="" />
        </div>

        <div class="input-group">
          <i class="fa-regular fa-key input-icon"></i>
          <input placeholder="کلمه عبور" id="password" name="password" type="password" class="input" required="" />
        </div>

        <span class="forgot-password">
          <a href="#">رمز عبور خود را فراموش کرده‌اید؟</a>
        </span>

        <button type="submit" class="login-button">
          <span>ورود به سیستم</span>
        </button>
      </form>

    </div>
    <?php
    if (isset($_SESSION['error'])) {
      ?>
      <div class="error_box">
        <span>کاربر گرامی هنوز برای شما نقشی در وبسایت تعریف نشده است</span>
      </div>
      <?php
    }
    ?>
  </div>

  <script src="https://unpkg.com/lenis@1.3.11/dist/lenis.min.js"></script>
  <script type="text/javascript" src="js/theme.js"></script>
</body>

</html>