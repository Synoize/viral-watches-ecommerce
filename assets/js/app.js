// Toast message
document.addEventListener("DOMContentLoaded", () => {
  const toast = document.getElementById("toast");

  if (!toast) return;

  toast.style.transform = "translateX(120%)";
  toast.style.opacity = "0";

  setTimeout(() => {
    toast.style.transform = "translateX(0)";
    toast.style.opacity = "1";
  }, 100);

  setTimeout(closeToast, 3500);
});

function closeToast() {
  const toast = document.getElementById("toast");

  if (!toast) return;

  toast.style.transform = "translateX(120%)";
  toast.style.opacity = "0";

  setTimeout(() => toast.remove(), 500);
}


/* SEARCH OVERLAY */
document.addEventListener("DOMContentLoaded", () => {

  const openBtn = document.getElementById("openSearch");
  const closeBtn = document.getElementById("closeSearch");
  const overlay = document.getElementById("searchOverlay");
  const panel = document.getElementById("searchPanel");
  const input = panel?.querySelector("input");

  // Open Search
  openBtn?.addEventListener("click", () => {
    if (!overlay || !panel) return;

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
    if (!overlay || !panel) return;

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
  overlay?.addEventListener("click", (e) => {
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

  const h1 = document.getElementById("h1");
  const h2 = document.getElementById("h2");
  const m1 = document.getElementById("m1");
  const m2 = document.getElementById("m2");
  const s1 = document.getElementById("s1");
  const s2 = document.getElementById("s2");
  if (!h1 || !h2 || !m1 || !m2 || !s1 || !s2) return;

  // Hours
  h1.innerText = hh[0];
  h2.innerText = hh[1];

  // Minutes
  m1.innerText = mm[0];
  m2.innerText = mm[1];

  // Seconds
  s1.innerText = ss[0];
  s2.innerText = ss[1];
}

// Start Countdown
updateCountdown();
setInterval(updateCountdown, 1000);


/* TOP ANNOUNCEMENT BAR */
const messages = [
  "Prepaid Orders Deliver Faster!",
  "Free Shipping on Orders Above Rs. 999",
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
  if (!details) return;

  const summary = details.querySelector("summary");
  const dropdown = details.querySelector(".dropdown");
  if (!summary || !dropdown) return;

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
    if (!sidebar || !overlay) return;
    sidebar.style.left = "0";
    overlay.classList.remove("hidden");
    document.body.style.overflow = "hidden";
  });

  // Close Sidebar Function
  function closeSidebar() {
    if (!sidebar || !overlay) return;
    sidebar.style.left = "-320px";
    overlay.classList.add("hidden");
    document.body.style.overflow = "";
  }

  // Close Events
  closeMenu?.addEventListener("click", closeSidebar);
  overlay?.addEventListener("click", closeSidebar);
});


/* HERO SLIDER */
if (typeof Swiper !== "undefined" && document.querySelector(".heroSlider")) {
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
}


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
const watchWishlistForm = document.getElementById("watchWishlistForm");
const watchWishlistAction = document.getElementById("watchWishlistAction");
const watchWishlistProductId = document.getElementById("watchWishlistProductId");
const watchWishlistButton = document.getElementById("watchWishlistButton");
const watchWishlistLabel = document.getElementById("watchWishlistLabel");

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
    productUrl: card.dataset.productUrl || "",
    productId: card.dataset.productId || "",
    wished: card.dataset.wished === "1"
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

  if (mainVideo) {
    mainVideo.muted = isMuted;
  }

  setVideo(prevVideo, previous.src, true);
  setVideo(nextVideo, next.src, true);

  if (productThumb && current.thumb) {
    productThumb.src = current.thumb;
    productThumb.alt = current.title;
  }

  if (modalTitleTop) {
    modalTitleTop.textContent = current.title;
  }
  if (modalTitle) {
    modalTitle.textContent = current.title;
  }

  if (modalPrice) {
    modalPrice.textContent = current.price;
  }
  if (modalOldPrice) {
    modalOldPrice.textContent = current.oldPrice;
  }

  if (productViewLink) {
    productViewLink.href = current.productUrl;
  }

  if (watchBuyForm) {
    watchBuyForm.action = current.productUrl;
  }

  if (watchWishlistForm && watchWishlistAction && watchWishlistProductId && watchWishlistButton) {
    watchWishlistAction.value = current.wished ? "remove" : "add";
    watchWishlistProductId.value = current.productId;
    watchWishlistButton.setAttribute(
      "aria-label",
      current.wished ? "Remove from wishlist" : "Add to wishlist"
    );
    watchWishlistButton.setAttribute(
      "title",
      current.wished ? "Remove from wishlist" : "Add to wishlist"
    );
    watchWishlistButton.classList.toggle("bg-rose-500/80", current.wished);
    watchWishlistButton.classList.toggle("bg-black/35", !current.wished);
    watchWishlistButton.innerHTML = current.wished
      ? '<i class="fa-solid fa-heart text-[20px] text-white"></i>'
      : '<i data-lucide="heart" class="h-6 w-6 stroke-[1]"></i>';
    if (watchWishlistLabel) {
      watchWishlistLabel.textContent = current.wished ? "Saved" : "Like";
    }
    lucide.createIcons();
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

  if (mainVideo) {
    mainVideo.muted = true;
  }

  if (soundToggle) {
    soundToggle.innerHTML =
      '<i data-lucide="volume-x" class="h-6 w-6 stroke-[1]"></i>';
  }

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
soundToggle?.addEventListener("click", () => {

  if (!mainVideo) return;

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
    ?.addEventListener("click", closeModal);

  document
    .getElementById("prevModal")
    ?.addEventListener("click", () => {

      activeIndex =
        (activeIndex - 1 + cards.length) %
        cards.length;

      renderModal();

    });

  document
    .getElementById("nextModal")
    ?.addEventListener("click", () => {

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


// get dates for order, ready and delivery
function formatDate(date) {
  return date.toLocaleDateString('en-US', {
    month: 'short',
    day: '2-digit'
  });
}

const today = new Date();

const readyDate = new Date(today);
readyDate.setDate(today.getDate() + 1);

const deliveryStart = new Date(today);
deliveryStart.setDate(today.getDate() + 3);

const deliveryEnd = new Date(today);
deliveryEnd.setDate(today.getDate() + 4);

document.getElementById('ordered-date').textContent =
  formatDate(today);

document.getElementById('ready-date').textContent =
  formatDate(readyDate);

document.getElementById('delivery-date').textContent =
  formatDate(deliveryStart) + ' - ' + formatDate(deliveryEnd);

if (typeof lucide !== 'undefined') {
  lucide.createIcons();
}