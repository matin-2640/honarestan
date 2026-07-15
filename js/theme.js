// ==========================================================================
// ۱. مدیریت و اعمال تم ذخیره‌شده (دارک‌مود / لایت‌مود)
// ==========================================================================
// اعمال تم از حافظه مرورگر در لحظه لود شدن تمام صفحات
const currentTheme = localStorage.getItem("theme") || "light";
document.documentElement.setAttribute("data-theme", currentTheme);

// پیدا کردن دکمه تغییر تم (سازگار با هدر صفحه اصلی و هدر پنل)
const themeToggle = document.getElementById("themeToggle");

if (themeToggle) {
  const themeIcon = themeToggle.querySelector("i");
  
  // بروزرسانی اولیه ظاهر دکمه تم
  updateThemeIcon(currentTheme, themeToggle, themeIcon);

  // رویداد کلیک برای جابه‌جایی بین حالت شب و روز
  themeToggle.addEventListener("click", () => {
    let theme = document.documentElement.getAttribute("data-theme");
    let newTheme = theme === "dark" ? "light" : "dark";

    document.documentElement.setAttribute("data-theme", newTheme);
    localStorage.setItem("theme", newTheme);
    updateThemeIcon(newTheme, themeToggle, themeIcon);
  });
}

// تابع کمکی برای تغییر شکل و رنگ آیکون تم
function updateThemeIcon(theme, toggleElement, iconElement) {
  if (!toggleElement || !iconElement) return;

  if (theme === "dark") {
    iconElement.className = "fa-solid fa-sun";
    toggleElement.style.color = "#eab308"; // رنگ طلایی خورشید در تم تاریک
  } else {
    iconElement.className = "fa-solid fa-moon";
    toggleElement.style.color = ""; // بازگشت به رنگ پیش‌فرض در تم روشن
  }
}

// ==========================================================================
// ۲. مدیریت منوی واکنش‌گرا همبرگری (مخصوص موبایل - صفحه اصلی و پنل)
// ==========================================================================
const menuToggle = document.getElementById("menuToggle");
// شناسایی هوشمند منو (اگر منوی معمولی پورتال بود یا منوی اختصاصی پنل کاربری)
const navMenu = document.getElementById("navMenu") || document.getElementById("panelNav");

if (menuToggle && navMenu) {
  menuToggle.addEventListener("click", () => {
    navMenu.classList.toggle("active");
    const icon = menuToggle.querySelector("i");
    
    if (navMenu.classList.contains("active")) {
      icon.className = "fa-solid fa-xmark";
    } else {
      icon.className = "fa-solid fa-bars";
    }
  });

  // بسته شدن خودکار منو پس از لمس یا کلیک روی لینک‌های آن
  // این بخش هم کلاس .nav-menu و هم .panel-nav را پوشش می‌دهد
  const menuLinks = document.querySelectorAll(".nav-menu a, .panel-nav a");
  menuLinks.forEach((link) => {
    link.addEventListener("click", () => {
      navMenu.classList.remove("active");
      const icon = menuToggle.querySelector("i");
      if (icon) icon.className = "fa-solid fa-bars";
    });
  });
}

// ==========================================================================
// ۳. راه‌اندازی اسکرول روان Lenis (در صورت لود شدن فایل کتابخانه در سند)
// ==========================================================================
if (typeof Lenis !== "undefined") {
  const lenis = new Lenis();

  function raf(time) {
    lenis.raf(time);
    requestAnimationFrame(raf);
  }

  requestAnimationFrame(raf);
}
