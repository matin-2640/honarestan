// ==========================================================================
// ۱. مدیریت و اعمال تم ذخیره‌شده (دارک‌مود / لایت‌مود)
// ==========================================================================
const currentTheme = localStorage.getItem("theme") || "light";
document.documentElement.setAttribute("data-theme", currentTheme);

// اجرای کدها پس از بارگذاری کامل ساختار DOM (حل مشکل عدم کارکرد در موبایل واقعی)
document.addEventListener("DOMContentLoaded", () => {
  const themeToggle = document.getElementById("themeToggle");

  if (themeToggle) {
    const themeIcon = themeToggle.querySelector("i");
    updateThemeIcon(currentTheme, themeToggle, themeIcon);

    // پشتیبانی همزمان از لمس در موبایل و کلیک در دسکتاپ برای تغییر تم
    const handleThemeSwitch = (e) => {
      e.preventDefault();
      let theme = document.documentElement.getAttribute("data-theme");
      let newTheme = theme === "dark" ? "light" : "dark";

      document.documentElement.setAttribute("data-theme", newTheme);
      localStorage.setItem("theme", newTheme);
      updateThemeIcon(newTheme, themeToggle, themeIcon);
    };

    themeToggle.addEventListener("click", handleThemeSwitch);
  }

  // ==========================================================================
  // ۲. مدیریت منوی واکنش‌گرا همبرگری (مخصوص موبایل و تبلت‌های واقعی)
  // ==========================================================================
  const menuToggle = document.getElementById("menuToggle");
  const navMenu =
    document.getElementById("navMenu") || document.getElementById("panelNav");

  if (menuToggle && navMenu) {
    // تابع اصلی باز و بسته کردن منو
    const toggleMenu = (e) => {
      e.preventDefault(); // جلوگیری از رفتار پیش‌فرض و دبل‌کلیک ناخواسته در موبایل
      navMenu.classList.toggle("active");
      const icon = menuToggle.querySelector("i");

      if (navMenu.classList.contains("active")) {
        icon.className = "fa-solid fa-xmark";
      } else {
        icon.className = "fa-solid fa-bars";
      }
    };

    // ثبت همزمان رویداد کلیک و لمس سریع برای پاسخگویی آنی در موبایل
    menuToggle.addEventListener("click", toggleMenu);
    menuToggle.addEventListener("touchstart", toggleMenu, { passive: false });

    // بسته شدن خودکار منو پس از کلیک روی گزینه‌ها
    const menuLinks = document.querySelectorAll(".nav-menu a, .panel-nav a");
    menuLinks.forEach((link) => {
      const closeMenu = () => {
        navMenu.classList.remove("active");
        const icon = menuToggle.querySelector("i");
        if (icon) icon.className = "fa-solid fa-bars";
      };

      link.addEventListener("click", closeMenu);
      link.addEventListener("touchstart", closeMenu, { passive: true });
    });
  }
});

// تابع کمکی تغییر شکل و رنگ آیکون تم
function updateThemeIcon(theme, toggleElement, iconElement) {
  if (!toggleElement || !iconElement) return;

  if (theme === "dark") {
    iconElement.className = "fa-solid fa-sun";
    toggleElement.style.color = "#eab308";
  } else {
    iconElement.className = "fa-solid fa-moon";
    toggleElement.style.color = "";
  }
}

// ==========================================================================
// ۳. راه‌اندازی اسکرول روان Lenis
// ==========================================================================
if (typeof Lenis !== "undefined") {
  const lenis = new Lenis();

  function raf(time) {
    lenis.raf(time);
    requestAnimationFrame(raf);
  }

  requestAnimationFrame(raf);
}
