// سوئیچ تم پیشرفته
const themeToggle = document.getElementById("themeToggle");
const themeIcon = themeToggle.querySelector("i");
const currentTheme = localStorage.getItem("theme") || "light";

document.documentElement.setAttribute("data-theme", currentTheme);
updateThemeIcon(currentTheme);

themeToggle.addEventListener("click", () => {
  let theme = document.documentElement.getAttribute("data-theme");
  let newTheme = theme === "dark" ? "light" : "dark";

  document.documentElement.setAttribute("data-theme", newTheme);
  localStorage.setItem("theme", newTheme);
  updateThemeIcon(newTheme);
});

function updateThemeIcon(theme) {
  if (theme === "dark") {
    themeIcon.className = "fa-solid fa-sun";
    themeToggle.style.color = "#eab308"; // خورشید در تم تاریک طلایی شود
  } else {
    themeIcon.className = "fa-solid fa-moon";
    themeToggle.style.color = "";
  }
}

// کنترل باز و بسته‌شدن منوی موبایل
const menuToggle = document.getElementById("menuToggle");
const navMenu = document.getElementById("navMenu");

menuToggle.addEventListener("click", () => {
  navMenu.classList.toggle("active");
  const icon = menuToggle.querySelector("i");
  if (navMenu.classList.contains("active")) {
    icon.className = "fa-solid fa-xmark";
  } else {
    icon.className = "fa-solid fa-bars";
  }
});

// بسته شدن خودکار منوی موبایل پس از کلیک روی لینک‌ها
document.querySelectorAll(".nav-menu a").forEach((link) => {
  link.addEventListener("click", () => {
    navMenu.classList.remove("active");
    menuToggle.querySelector("i").className = "fa-solid fa-bars";
  });
});
const lenis = new Lenis();

function raf(time) {
  lenis.raf(time);
  requestAnimationFrame(raf);
}

requestAnimationFrame(raf);
