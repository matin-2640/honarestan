const currentTheme = localStorage.getItem("theme") || "light";
document.documentElement.setAttribute("data-theme", currentTheme);

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

  const menuToggle = document.getElementById("menuToggle");
  const navMenu =
    document.getElementById("navMenu") || document.getElementById("panelNav");

  if (menuToggle && navMenu) {
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

    menuToggle.addEventListener("click", toggleMenu);

    const menuLinks = document.querySelectorAll(
      ".sidebar-nav a, .nav-menu a, .panel-nav a, .sidebar-footer a",
    );

    menuLinks.forEach((link) => {
      link.addEventListener("click", () => {
        setTimeout(() => {
          navMenu.classList.remove("active");
          const icon = menuToggle.querySelector("i");
          if (icon) icon.className = "fa-solid fa-bars";
        }, 150);
      });
    });
  }
});

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

if (typeof Lenis !== "undefined") {
  const lenis = new Lenis();

  function raf(time) {
    lenis.raf(time);
    requestAnimationFrame(raf);
  }

  requestAnimationFrame(raf);
}

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
document.querySelectorAll("a").forEach((link) => {
  if (link.hostname === location.hostname) {
    link.addEventListener("click", function (e) {
      e.preventDefault();
      document.body.classList.add("fade-out");

      setTimeout(() => {
        location.href = this.href;
      }, 300);
    });
  }
});
const modal = document.getElementById("newsModal");

const modalImage = document.getElementById("modalImage");
const modalTitle = document.getElementById("modalTitle");
const modalDate = document.getElementById("modalDate");
const modalCategory = document.getElementById("modalCategory");
const modalText = document.getElementById("modalText");

document.querySelectorAll(".news-card").forEach((card) => {
  card.addEventListener("click", () => {
    const img = card.querySelector(".gallery-img");

    if (img) {
      modalImage.src = img.src;
      modalImage.style.display = "block";
    } else {
      modalImage.style.display = "none";
    }

    modalTitle.innerHTML = card.querySelector(".news-title").innerHTML;
    modalDate.innerHTML = card.querySelector(".news-date").innerHTML;
    modalCategory.innerHTML = card.querySelector(".news-category").innerHTML;
    modalText.innerHTML = card
      .querySelector(".news-excerpt")
      .innerHTML.repeat(8);

    modal.classList.add("active");

    document.body.style.overflow = "hidden";
  });
});

const closeNews = document.querySelector(".close-news");

if (closeNews && modal) {
  closeNews.onclick = () => {
    modal.classList.remove("active");
    document.body.style.overflow = "";
  };
}

if (modal) {
  modal.onclick = (e) => {
    if (e.target === modal) {
      modal.classList.remove("active");
      document.body.style.overflow = "";
    }
    document.body.style.overflow = "";
  };
}
const studentSearch = document.getElementById("studentSearch");
const resultCount = document.getElementById("searchResultCount");
const clearSearch = document.getElementById("clearSearch");
const noResultMessage = document.getElementById("noResultMessage");
const totalStudents = document.querySelectorAll(".student-linear-row").length;
if (studentSearch) {
  resultCount.textContent = "تعداد هنرجویان :" + totalStudents;
  document.querySelectorAll(".searchable").forEach((el) => {
    el.dataset.original = el.textContent;
  });

  studentSearch.addEventListener("input", function () {
    clearSearch.style.display = this.value ? "block" : "none";
    let visibleCount = 0;
    const value = this.value.trim().toLowerCase();

    document.querySelectorAll(".student-linear-row").forEach((row) => {
      let found = false;

      row.querySelectorAll(".searchable").forEach((el) => {
        const original = el.dataset.original;

        el.innerHTML = original;

        if (value !== "") {
          const regex = new RegExp(value, "gi");

          if (regex.test(original)) {
            found = true;

            el.innerHTML = original.replace(
              regex,
              '<span class="search-highlight">$&</span>',
            );
          }
        }
      });

      if (value === "") {
        row.classList.remove("hide");
        row.style.display = "flex";
        visibleCount++;
      } else {
        if (found) {
          row.style.display = "flex";

          requestAnimationFrame(() => {
            row.classList.remove("hide");
          });

          visibleCount++;
        } else {
          row.classList.add("hide");

          setTimeout(() => {
            if (row.classList.contains("hide")) {
              row.style.display = "none";
            }
          }, 250);
        }
      }
    });
    if (value === "") {
      resultCount.textContent = "تعداد هنرجویان : " + totalStudents;
    } else {
      resultCount.textContent = "تعداد نتایج : " + visibleCount;
    }
    if (visibleCount === 0 && value !== "") {
      noResultMessage.style.display = "block";
    } else {
      noResultMessage.style.display = "none";
    }
  });
  if (clearSearch) {
    clearSearch.addEventListener("click", () => {
      studentSearch.value = "";

      studentSearch.dispatchEvent(new Event("input"));

      studentSearch.focus();
    });
  }
}
