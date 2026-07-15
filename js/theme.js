// ==========================================================================
// ۱. مدیریت و اعمال تم ذخیره‌شده (دارک‌مود / لایت‌مود)
// ==========================================================================
const currentTheme = localStorage.getItem("theme") || "light";
document.documentElement.setAttribute("data-theme", currentTheme);

// اجرای کدها پس از بارگذاری کامل ساختار DOM
document.addEventListener("DOMContentLoaded", () => {
  const themeToggle = document.getElementById("themeToggle");

  if (themeToggle) {
    const themeIcon = themeToggle.querySelector("i");
    updateThemeIcon(currentTheme, themeToggle, themeIcon);

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
  // ۲. مدیریت منوی واکنش‌گرا همبرگری
  // ==========================================================================
  const menuToggle = document.getElementById("menuToggle");
  const navMenu =
    document.getElementById("navMenu") || document.getElementById("panelNav") ;

  if (menuToggle && navMenu) {
    // تابع اصلی باز و بسته کردن منو
    const toggleMenu = (e) => {
      e.preventDefault();
      navMenu.classList.toggle("active");
      const icon = menuToggle.querySelector("i");

      if (navMenu.classList.contains("active")) {
        icon.className = "fa-solid fa-xmark";
      } else {
        icon.className = "fa-solid fa-bars";
      }
    };

    // استفاده از click به تنهایی برای جلوگیری از باگ کلیک دوگانه (Double-Triggering) در موبایل
    menuToggle.addEventListener("click", toggleMenu);

    // بسته شدن خودکار منو پس از کلیک واقعی روی گزینه‌ها
    // سلکتورها را به گونه‌ای قرار دادیم که کلاس‌های جدید سایدبار را هم پوشش دهد
    const menuLinks = document.querySelectorAll(".sidebar-nav a, .nav-menu a, .panel-nav a, .sidebar-footer a");
    
    menuLinks.forEach((link) => {
      link.addEventListener("click", () => {
        // اجازه می‌دهیم ابتدا اکشن کلیک (مانند رفتن به صفحه جدید یا خروج) انجام شود، سپس منو بسته شود
        setTimeout(() => {
          navMenu.classList.remove("active");
          const icon = menuToggle.querySelector("i");
          if (icon) icon.className = "fa-solid fa-bars";
        }, 150); // یک تاخیر بسیار کوتاه ۱۵۰ میلی‌ثانیه‌ای برای اجرای روان‌تر ترنزیشن
      });
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


/* ===========================
   Lightbox Gallery
=========================== */

const galleryImages = document.querySelectorAll(".gallery-img");
const lightbox = document.getElementById("lightbox");
const lightboxImg = document.getElementById("lightbox-img");

if (galleryImages.length && lightbox && lightboxImg) {
  galleryImages.forEach((img) => {
    img.addEventListener("click", () => {
      lightboxImg.src = img.src;
      lightbox.classList.add("active");
      document.body.style.overflow = "hidden";
    });
  });

  lightbox.addEventListener("click", () => {
    lightbox.classList.remove("active");
    document.body.style.overflow = "";
  });

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") {
      lightbox.classList.remove("active");
      document.body.style.overflow = "";
    }
  });
}