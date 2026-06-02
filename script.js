// SUMMER SALE BAR

function updateCountdown() {

  const now = new Date();

  // Next midnight
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

  document.getElementById("h1").innerText = hh[0];
  document.getElementById("h2").innerText = hh[1];

  document.getElementById("m1").innerText = mm[0];
  document.getElementById("m2").innerText = mm[1];

  document.getElementById("s1").innerText = ss[0];
  document.getElementById("s2").innerText = ss[1];
}

updateCountdown();
setInterval(updateCountdown, 1000);

// top Bar

const messages = [
  "Prepaid Orders Deliver Faster!",
  "Free Shipping on Orders Above ₹999",
  "Easy Returns Within 7 Days",
  "Premium Quality Watches "
];

let currentIndex = 0;
const textElement = document.getElementById("announcementText");

setInterval(() => {
  currentIndex = (currentIndex + 1) % messages.length;
  textElement.textContent = messages[currentIndex];
}, 3000);


// Header

document.addEventListener("DOMContentLoaded", () => {

  const menuBtn = document.getElementById("menuBtn");
  const closeMenu = document.getElementById("closeMenu");
  const sidebar = document.getElementById("sidebar");
  const overlay = document.getElementById("overlay");

  menuBtn.addEventListener("click", () => {
    sidebar.style.left = "0";
    overlay.classList.remove("hidden");
    document.body.style.overflow = "hidden";
  });

  function closeSidebar() {
    sidebar.style.left = "-320px";
    overlay.classList.add("hidden");
    document.body.style.overflow = "";
  }

  closeMenu.addEventListener("click", closeSidebar);
  overlay.addEventListener("click", closeSidebar);

});


// hero slider

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

// best seller slider


    new Swiper(".bestSellerSlider", {
    slidesPerView: "auto",
    spaceBetween: 14,
  });

  // chainslider
    new Swiper(".chainsSlider", {
    slidesPerView: 2.2,
    spaceBetween: 16,

    breakpoints: {
      768: {
        slidesPerView: 2,
      },

      1024: {
        slidesPerView: 4,
      }
    }
  });

  // CUSTOMER REVIEWS 
  const slider = document.getElementById("reviewSlider");

let scrollAmount = 0;

function autoSlide() {

  const cardWidth =
    slider.querySelector("div").offsetWidth + 32;

  scrollAmount += cardWidth;

  if (
    scrollAmount >=
    slider.scrollWidth - slider.clientWidth
  ) {
    scrollAmount = 0;
  }

  slider.scrollTo({
    left: scrollAmount,
    behavior: "smooth",
  });
}

setInterval(autoSlide, 2000);
    

