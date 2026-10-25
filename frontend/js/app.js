const VIEW_ROOT = document.getElementById("view-root");

const ROUTES = {
  login: "views/login.html",
  dashboard: "views/dashboard.html",
  catalog: "views/catalog.html",
  details: "views/tool-details.html",
  weather: "views/weather.html",
  profile: "views/profile.html",
};

function currentRoute() {
  const hash = window.location.hash.replace("#/", "").replace("#", "");
  return ROUTES[hash] ? hash : "dashboard";
}

function setActiveNav(route) {
  document.querySelectorAll("[data-route]").forEach((link) => {
    link.classList.toggle("active", link.dataset.route === route);
  });
}

async function loadView(route) {
  const path = ROUTES[route] || ROUTES.dashboard;
  VIEW_ROOT.innerHTML = `
    <div class="view-loading d-flex align-items-center justify-content-center">
      <div class="spinner-border text-secondary" role="status" aria-label="Loading view"></div>
    </div>`;

  try {
    const response = await fetch(path);
    if (!response.ok) {
      throw new Error("View not found");
    }
    VIEW_ROOT.innerHTML = await response.text();
    setActiveNav(route);
    window.scrollTo({ top: 0, behavior: "smooth" });
  } catch (error) {
    VIEW_ROOT.innerHTML = `
      <div class="alert alert-danger" role="alert">
        Could not load this view. Check that the frontend is served over HTTP (not a file:// URL).
      </div>`;
  }
}

function navigate() {
  const route = currentRoute();
  if (!window.location.hash) {
    window.location.hash = "#/dashboard";
    return;
  }
  loadView(route);
}

window.addEventListener("hashchange", navigate);
window.addEventListener("DOMContentLoaded", navigate);
