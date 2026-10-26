# Bushcraft Tools

Web Programming university project: a bushcraft gear catalog with user reviews, favorites, and camp-weather recommendations.

Milestone 1 delivers the folder structure, a static Bootstrap 5 SPA, and a draft database plan. The PHP REST API is not connected yet.

## Technology stack

- **Frontend:** vanilla HTML5, CSS, JavaScript (hash-based SPA), Bootstrap 5
- **Backend (planned):** PHP 8.x, FlightPHP, MySQL via PDO, JWT authentication
- **External API (planned):** [OpenWeather](https://openweathermap.org/api) for weather and camp recommendations

## Folder structure

```
backend/          REST API (FlightPHP) — empty in Milestone 1
  config/       secrets in config.php (gitignored); template in config.example.php
  dao/          PDO data access
  services/     business logic and OpenWeather client
  routes/       HTTP endpoints
  middleware/   JWT, logging, errors
  logs/         runtime logs (gitignored except .gitkeep)
frontend/         static SPA
  css/          theme
  js/           view router
  views/        HTML templates loaded without full page reload
  static/       images and other assets
docs/             AI log and ERD
```

## SPA pages

| Route | View | Purpose |
| --- | --- | --- |
| `#/login` | Login / Register | JWT account forms (static) |
| `#/dashboard` | Home | App overview |
| `#/catalog` | Tools catalog | Entity list (axes, knives, firesteels, …) |
| `#/details` | Tool details / review | Entity detail + review form |
| `#/profile` | Profile | Name, email, role, change password |
| `#/weather` | Weather & camp tips | Placeholder for OpenWeather |

Navigation never reloads `index.html`; `frontend/js/app.js` fetches the matching view into `#view-root`.

## Roles

- **user** — register, log in, browse tools, write reviews, save favorites, view own profile
- **admin** — manage categories and tools (API in later milestones)

## Draft database (5 entities)

See `docs/erd.png` and the source sketch in `docs/erd.dbml`.

1. **User** — `id`, `email`, `password_hash`, `display_name`, `role` (`user` \| `admin`)
2. **Category** — `id`, `name`, `slug` (e.g. axes, knives, ignition)
3. **Tool** — `id`, `category_id`, `name`, `brand`, `description`, `price`, `stock`
4. **Review** — `id`, `user_id`, `tool_id`, `rating` (1–5), `comment`
5. **Favorite** — `id`, `user_id`, `tool_id` (wishlist / saved gear)

### Relationships

- Category **1 : N** Tool (`Tool.category_id` → `Category.id`)
- User **1 : N** Review (`Review.user_id` → `User.id`)
- Tool **1 : N** Review (`Review.tool_id` → `Tool.id`)
- User **N : M** Tool through Favorite (`Favorite.user_id`, `Favorite.tool_id`)
- Unique pair `(user_id, tool_id)` on Favorite so a user cannot star the same tool twice

## OpenWeather (external API)

The **Weather & camp recommendations** page (`#/weather`) is where the external API will appear.

Later, a backend service will call OpenWeather (current weather / forecast) using the key in `backend/config/config.php`, then return JSON to the SPA. The frontend will show temperature, wind, and precipitation, plus camp tips (tarp setup, fire restrictions). No weather calls run in Milestone 1.

## Run the SPA locally

Views are loaded with `fetch()`, so the frontend must be served over HTTP (not opened as `file://`).

From `frontend/`:

```bash
php -S localhost:8766
```

Then open `http://localhost:8766/`.

Copy `backend/config/config.example.php` to `backend/config/config.php` when you add database, JWT, and OpenWeather secrets. Do not commit `config.php`.

## Milestone 1 deliverables

- Project structure and `.gitignore`
- Static SPA with consistent bushcraft theme and responsive Bootstrap layout
- Draft ERD (`docs/erd.png`)
- This README draft
- AI usage log (`docs/AI_LOG.md`)
