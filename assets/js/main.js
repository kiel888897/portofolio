const header = document.querySelector(".site-header");
const menuToggle = document.querySelector(".menu-toggle");
const navLinks = document.querySelector(".nav-links");

window.addEventListener("scroll", () => header.classList.toggle("scrolled", window.scrollY > 20), { passive: true });
menuToggle?.addEventListener("click", () => {
  const isOpen = navLinks.classList.toggle("open");
  menuToggle.setAttribute("aria-expanded", String(isOpen));
});
navLinks?.querySelectorAll("a").forEach((link) =>
  link.addEventListener("click", () => {
    navLinks.classList.remove("open");
    menuToggle?.setAttribute("aria-expanded", "false");
  }),
);

const revealObserver = new IntersectionObserver(
  (entries) =>
    entries.forEach((entry) => {
      if (entry.isIntersecting) (revealObserver.observe(entry.target), entry.target.classList.add("in-view"));
    }),
  { threshold: 0.12 },
);
document.querySelectorAll(".reveal").forEach((item) => revealObserver.observe(item));

const countObserver = new IntersectionObserver(
  (entries) =>
    entries.forEach((entry) => {
      if (!entry.isIntersecting) return;
      const target = entry.target;
      const end = Number(target.dataset.count);
      let current = 0;
      const tick = () => {
        current += Math.ceil(end / 24);
        target.textContent = `${Math.min(current, end)}${end > 6 ? "+" : ""}`;
        if (current < end) requestAnimationFrame(tick);
      };
      tick();
      countObserver.unobserve(target);
    }),
  { threshold: 0.7 },
);
document.querySelectorAll("[data-count]").forEach((item) => countObserver.observe(item));

const filters = document.querySelectorAll(".filter");
const cards = document.querySelectorAll(".website-card");
let showAll = false;
function filterPortfolio(category = "All") {
  cards.forEach((card, index) => {
    const matches = category === "All" || card.dataset.category === category;
    card.classList.toggle("visible", matches && (showAll || category !== "All" || index < 8));
  });
}
filters.forEach((filter) =>
  filter.addEventListener("click", () => {
    filters.forEach((item) => item.classList.remove("active"));
    filter.classList.add("active");
    showAll = false;
    filterPortfolio(filter.dataset.filter);
  }),
);
document.querySelector("#show-all")?.addEventListener("click", (event) => {
  showAll = !showAll;
  event.currentTarget.innerHTML = showAll ? "Show featured <span>↗</span>" : "View all projects <span>↗</span>";
  filterPortfolio(document.querySelector(".filter.active").dataset.filter);
});
filterPortfolio();
