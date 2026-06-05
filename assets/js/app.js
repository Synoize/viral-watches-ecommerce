/* SEARCH OVERLAY */
document.addEventListener("DOMContentLoaded", () => {

  const openBtn = document.getElementById("openSearch");
  const closeBtn = document.getElementById("closeSearch");
  const overlay = document.getElementById("searchOverlay");
  const panel = document.getElementById("searchPanel");
  const input = panel.querySelector("input");

  // Open Search
  openBtn?.addEventListener("click", () => {

    overlay.classList.remove("invisible", "opacity-0");
    overlay.classList.add("opacity-100");

    setTimeout(() => {
      panel.classList.remove("-translate-y-full");
      panel.classList.add("translate-y-0");
    }, 50);

    document.body.style.overflow = "hidden";

    setTimeout(() => {
      input?.focus();
    }, 500);
  });

  // Close Search
  function closeSearch() {

    panel.classList.remove("translate-y-0");
    panel.classList.add("-translate-y-full");

    setTimeout(() => {
      overlay.classList.remove("opacity-100");
      overlay.classList.add("opacity-0");

      setTimeout(() => {
        overlay.classList.add("invisible");
      }, 300);

    }, 200);

    document.body.style.overflow = "";
  }

  // Close Button
  closeBtn?.addEventListener("click", closeSearch);

  // ESC Key
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") {
      closeSearch();
    }
  });

  // Click Outside Panel
  overlay.addEventListener("click", (e) => {
    if (e.target === overlay) {
      closeSearch();
    }
  });

});

/* SUMMER SALE COUNTDOWN TIMER */
function updateCountdown() {
  const now = new Date();

  // Next Midnight
  const tomorrow = new Date();
  tomorrow.setDate(now.getDate() + 1);
  tomorrow.setHours(0, 0, 0, 0);

  const diff = tomorrow - now;

  const hours = Math.floor(diff / (1000 * 60 * 60));
  const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
  const seconds = Math.floor((diff % (1000 * 60)) / 1000);

  const hh = String(hours).padStart(2, "0");
  const mm = String(minutes).padStart(2, "0");
  const ss = String(seconds).padStart(2, "0");

  // Hours
  document.getElementById("h1").innerText = hh[0];
  document.getElementById("h2").innerText = hh[1];

  // Minutes
  document.getElementById("m1").innerText = mm[0];
  document.getElementById("m2").innerText = mm[1];

  // Seconds
  document.getElementById("s1").innerText = ss[0];
  document.getElementById("s2").innerText = ss[1];
}

// Start Countdown
updateCountdown();
setInterval(updateCountdown, 1000);


/* TOP ANNOUNCEMENT BAR */
const messages = [
  "Prepaid Orders Deliver Faster!",
  "Free Shipping on Orders Above ₹999",
  "Easy Returns Within 7 Days",
  "Premium Quality Watches"
];

const textElement = document.getElementById("announcementText");
let currentIndex = 0;

// Rotate Messages Every 3 Seconds
setInterval(() => {
  currentIndex = (currentIndex + 1) % messages.length;

  if (textElement) {
    textElement.textContent = messages[currentIndex];
  }
}, 3000);


/* DESKTOP MENU */
document.addEventListener("DOMContentLoaded", () => {

  const details = document.getElementById("profileMenu");
  const summary = details.querySelector("summary");
  const dropdown = details.querySelector(".dropdown");

  function openDropdown() {
    details.open = true;

    requestAnimationFrame(() => {
      dropdown.classList.remove(
        "max-h-0",
        "opacity-0",
        "-translate-y-3"
      );

      dropdown.classList.add(
        "max-h-[400px]",
        "opacity-100",
        "translate-y-0"
      );
    });
  }

  function closeDropdown() {
    dropdown.classList.remove(
      "max-h-[400px]",
      "opacity-100",
      "translate-y-0"
    );

    dropdown.classList.add(
      "max-h-0",
      "opacity-0",
      "-translate-y-3"
    );

    setTimeout(() => {
      details.open = false;
    }, 300);
  }

  summary.addEventListener("click", (e) => {
    e.preventDefault();

    if (details.open) {
      closeDropdown();
    } else {
      openDropdown();
    }
  });

  document.addEventListener("click", (e) => {
    if (details.open && !details.contains(e.target)) {
      closeDropdown();
    }
  });

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && details.open) {
      closeDropdown();
    }
  });

});


/* MOBILE SIDEBAR MENU */
document.addEventListener("DOMContentLoaded", () => {
  const menuBtn = document.getElementById("menuBtn");
  const closeMenu = document.getElementById("closeMenu");
  const sidebar = document.getElementById("sidebar");
  const overlay = document.getElementById("overlay");

  // Open Sidebar
  menuBtn?.addEventListener("click", () => {
    sidebar.style.left = "0";
    overlay.classList.remove("hidden");
    document.body.style.overflow = "hidden";
  });

  // Close Sidebar Function
  function closeSidebar() {
    sidebar.style.left = "-320px";
    overlay.classList.add("hidden");
    document.body.style.overflow = "";
  }

  // Close Events
  closeMenu?.addEventListener("click", closeSidebar);
  overlay?.addEventListener("click", closeSidebar);
});


/* HERO SLIDER */
new Swiper(".heroSlider", {
  loop: true,

  navigation: {
    nextEl: ".swiper-button-next",
    prevEl: ".swiper-button-prev",
  },

  autoplay: {
    delay: 4000,
    disableOnInteraction: false,
  },
});


/* WATCH & BUY SECTION */

const cards = Array.from(document.querySelectorAll(".watch-card"));

const modal = document.getElementById("watchModal");

const mainVideo = document.getElementById("mainVideo");
const prevVideo = document.getElementById("prevVideo");
const nextVideo = document.getElementById("nextVideo");

const productThumb = document.getElementById("productThumb");

const modalTitleTop = document.getElementById("modalTitleTop");
const modalTitle = document.getElementById("modalTitle");

const modalPrice = document.getElementById("modalPrice");
const modalOldPrice = document.getElementById("modalOldPrice");

const productViewLink = document.getElementById("productViewLink");
const watchBuyForm = document.getElementById("watchBuyForm");

const soundToggle = document.getElementById("soundToggle");

const shareBtn = document.getElementById("shareProduct");

let activeIndex = 0;
let isMuted = true;

/* -----------------------------
   CARD DATA
------------------------------ */

function cardData(index) {
  const card = cards[(index + cards.length) % cards.length];

  const video = card.querySelector("video");
  const title = card.querySelector("h2");
  const prices = card.querySelectorAll("span");

  return {
    src: video ? video.currentSrc || video.getAttribute("src") : "",
    title: card.dataset.title || (title ? title.innerText.trim() : ""),
    price: card.dataset.price || (prices[0] ? prices[0].innerText.trim() : ""),
    oldPrice: card.dataset.oldPrice || (prices[1] ? prices[1].innerText.trim() : ""),
    thumb: card.dataset.thumb || "",
    productUrl: card.dataset.productUrl || ""
  };
}

/* -----------------------------
   VIDEO HELPER
------------------------------ */

function setVideo(video, src, play = true) {
  if (!video) return;

  if (video.getAttribute("src") !== src) {
    video.src = src;
  }

  video.muted = true;
  video.currentTime = 0;

  if (play) {
    video.play().catch(() => { });
  } else {
    video.pause();
  }
}

/* -----------------------------
   RENDER MODAL
------------------------------ */

function renderModal() {

  const current = cardData(activeIndex);
  const previous = cardData(activeIndex - 1);
  const next = cardData(activeIndex + 1);

  setVideo(mainVideo, current.src, true);

  mainVideo.muted = isMuted;

  setVideo(prevVideo, previous.src, true);
  setVideo(nextVideo, next.src, true);

  if (productThumb && current.thumb) {
    productThumb.src = current.thumb;
    productThumb.alt = current.title;
  }

  modalTitleTop.textContent = current.title;
  modalTitle.textContent = current.title;

  modalPrice.textContent = current.price;
  modalOldPrice.textContent = current.oldPrice;

  if (productViewLink) {
    productViewLink.href = current.productUrl;
  }

  if (watchBuyForm) {
    watchBuyForm.action = current.productUrl;
  }
}

/* -----------------------------
   OPEN MODAL
------------------------------ */

function openModal(index) {

  activeIndex = index;

  modal.classList.remove("hidden");

  document.body.classList.add("overflow-hidden");

  renderModal();
}

/* -----------------------------
   CLOSE MODAL
------------------------------ */

function closeModal() {

    modal.classList.add("hidden");

    document.body.classList.remove("overflow-hidden");

    isMuted = true;

    mainVideo.muted = true;

    soundToggle.innerHTML =
        '<i data-lucide="volume-x" class="h-6 w-6 stroke-[1]"></i>';

    lucide.createIcons();

    [mainVideo, prevVideo, nextVideo].forEach(video => {

        if (!video) return;

        video.pause();
        video.removeAttribute("src");
        video.load();

    });
}

/* -----------------------------
   MUTE / UNMUTE
------------------------------ */
soundToggle.addEventListener("click", () => {

    mainVideo.muted = !mainVideo.muted;

    soundToggle.innerHTML = mainVideo.muted
        ? '<i data-lucide="volume-x" class="h-6 w-6 stroke-[1]"></i>'
        : '<i data-lucide="volume-2" class="h-6 w-6 stroke-[1]"></i>';

    lucide.createIcons();

});

/* -----------------------------
   SHARE PRODUCT
------------------------------ */

shareBtn?.addEventListener("click", async () => {

  const current = cardData(activeIndex);

  const shareData = {
    title: current.title,
    text: `Check out ${current.title}`,
    url: current.productUrl
  };

  try {

    if (navigator.share) {

      await navigator.share(shareData);

    } else {

      await navigator.clipboard.writeText(current.productUrl);

      const original = shareBtn.innerHTML;

      shareBtn.innerHTML =
        `<i data-lucide="check" class="h-6 w-6 stroke-[1]"></i>`;

      lucide.createIcons();

      setTimeout(() => {

        shareBtn.innerHTML = original;

        lucide.createIcons();

      }, 2000);

    }

  } catch (error) {

    console.log("Share cancelled");

  }
});

/* -----------------------------
   INIT
------------------------------ */

if (cards.length && modal) {

  cards.forEach((card, index) => {

    card.addEventListener("click", () => {

      openModal(index);

    });

  });

  document
    .getElementById("closeModal")
    .addEventListener("click", closeModal);

  document
    .getElementById("prevModal")
    .addEventListener("click", () => {

      activeIndex =
        (activeIndex - 1 + cards.length) %
        cards.length;

      renderModal();

    });

  document
    .getElementById("nextModal")
    .addEventListener("click", () => {

      activeIndex =
        (activeIndex + 1) %
        cards.length;

      renderModal();

    });

  modal.addEventListener("click", event => {

    if (event.target === modal) {

      closeModal();

    }

  });

  document.addEventListener("keydown", event => {

    if (
      event.key === "Escape" &&
      !modal.classList.contains("hidden")
    ) {

      closeModal();

    }

    if (
      event.key === "ArrowRight" &&
      !modal.classList.contains("hidden")
    ) {

      activeIndex =
        (activeIndex + 1) %
        cards.length;

      renderModal();

    }

    if (
      event.key === "ArrowLeft" &&
      !modal.classList.contains("hidden")
    ) {

      activeIndex =
        (activeIndex - 1 + cards.length) %
        cards.length;

      renderModal();

    }

  });

}