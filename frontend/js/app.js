import { getWeather } from "./weather-service.js";

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
    if (route === "weather") {
      bindWeatherForm();
    }
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

function bindWeatherForm() {
  const form = VIEW_ROOT.querySelector("#weather-form");
  const status = VIEW_ROOT.querySelector("#weather-status");
  const result = VIEW_ROOT.querySelector("#weather-result");
  const location = result.querySelector("#weather-location");
  const temperature = result.querySelector("#weather-temperature");
  const condition = result.querySelector("#weather-condition");
  const details = result.querySelector("#weather-details");
  const tips = result.querySelector("#weather-tips");
  const button = form.querySelector("button");

  form.addEventListener("submit", async (event) => {
    event.preventDefault();
    button.disabled = true;
    result.hidden = true;
    status.textContent = "Checking weather…";

    try {
      const weather = await getWeather(form.elements.city.value.trim());
      location.textContent = weather.city;
      temperature.textContent = `${weather.temperature_c} °C`;
      condition.textContent = weather.condition;
      details.textContent = `Wind ${weather.wind_kmh} km/h · Precipitation ${weather.precipitation_mm_1h} mm in the last hour`;
      tips.replaceChildren(...weather.tips.map((tip) => {
        const item = document.createElement("li");
        item.textContent = tip;
        return item;
      }));
      status.textContent = "";
      result.hidden = false;
    } catch (error) {
      status.textContent = error instanceof SyntaxError
        ? "Weather is temporarily unavailable."
        : error.message;
    } finally {
      button.disabled = false;
    }
  });
}
