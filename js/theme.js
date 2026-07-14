// ==========================================================================
// ۱. مدیریت و اعمال تم ذخیره‌شده (دارک‌مود / لایت‌مود)
// ==========================================================================
// این بخش در تمامی صفحات (حتی بدون دکمه سوئیچ) تم درست را از حافظه مرورگر اعمال می‌کند
const currentTheme = localStorage.getItem("theme") || "light";
document.documentElement.setAttribute("data-theme", currentTheme);

// پیدا کردن دکمه تغییر تم (اگر در صفحه وجود داشته باشد - مثلاً در صفحه اصلی)
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
// ۲. مدیریت منوی واکنش‌گرا همبرگری (مخصوص موبایل)
// ==========================================================================
// کدهای این بخش فقط در صورت وجود منوی موبایل در صفحه اجرا می‌شوند و مانع بروز خطا در hNews می‌گردند
const menuToggle = document.getElementById("menuToggle");
const navMenu = document.getElementById("navMenu");

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
  document.querySelectorAll(".nav-menu a").forEach((link) => {
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
