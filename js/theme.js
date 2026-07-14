// ۱. بررسی و اعمال تم ذخیره‌شده به محض لود شدن هر صفحه‌ای از سایت
const currentTheme = localStorage.getItem("theme") || "light";
document.documentElement.setAttribute("data-theme", currentTheme);

// پیدا کردن دکمه تغییر تم (اگر در صفحه وجود داشته باشد)
const themeToggle = document.getElementById("themeToggle");

if (themeToggle) {
  const themeIcon = themeToggle.querySelector("i");
  
  // بروزرسانی آیکون دکمه تم در اولین اجرای صفحه اصلی
  updateThemeIcon(currentTheme, themeToggle, themeIcon);

  // رویداد کلیک برای سوئیچ بین حالت شب و روز
  themeToggle.addEventListener("click", () => {
    let theme = document.documentElement.getAttribute("data-theme");
    let newTheme = theme === "dark" ? "light" : "dark";

    document.documentElement.setAttribute("data-theme", newTheme);
    localStorage.setItem("theme", newTheme);
    updateThemeIcon(newTheme, themeToggle, themeIcon);
  });
}

// تابع کمکی برای تغییر شکل ظاهری آیکون تم
function updateThemeIcon(theme, toggleElement, iconElement) {
  if (!toggleElement || !iconElement) return;

  if (theme === "dark") {
    iconElement.className = "fa-solid fa-sun";
    toggleElement.style.color = "#eab308"; // طلایی شدن آیکون خورشید در تم تاریک
  } else {
    iconElement.className = "fa-solid fa-moon";
    toggleElement.style.color = ""; // بازگشت به رنگ پیش‌فرض در تم روشن
  }
}

// ==========================================================================
// ۲. مدیریت منوی واکنش‌گرا (مخصوص موبایل - فقط در صورت وجود در صفحه)
// ==========================================================================
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
<<<<<<< HEAD

  // بسته شدن خودکار منوی موبایل پس از کلیک روی لینک‌های منو
  document.querySelectorAll(".nav-menu a").forEach((link) => {
    link.addEventListener("click", () => {
      navMenu.classList.remove("active");
      const icon = menuToggle.querySelector("i");
      if (icon) icon.className = "fa-solid fa-bars";
    });
  });
}
=======
});
const lenis = new Lenis();

function raf(time) {
  lenis.raf(time);
  requestAnimationFrame(raf);
}

requestAnimationFrame(raf);
>>>>>>> bd0785140aab110cfa756d7ecb4e32dea03b637d
