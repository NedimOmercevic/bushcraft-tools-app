# Bushcraft Tools

Web Programming university project: a bushcraft gear catalog with user reviews, favorites, and camp-weather recommendations.

Milestone 2 delivers the MySQL schema (five entities, primary and foreign keys, sample data) and a PDO DAO layer with REST CRUD.

## Technology stack

- **Frontend:** vanilla HTML5, CSS, JavaScript (hash-based SPA), Bootstrap 5
- **Backend:** PHP 8.x, FlightPHP, MySQL via PDO, JWT authentication (JWT wired in a later milestone)
- **External API (planned):** [OpenWeather](https://openweathermap.org/api) for weather and camp recommendations

## Folder structure

```
backend/
  config/       secrets in config.php (gitignored); template in config.example.php
  dao/          PDO data access (BaseDao + one class per entity)
  sql/          MySQL schema and sample data dump
  lib/          vendored FlightPHP (used if Composer is unavailable)
  routes/       REST CRUD endpoints
  services/     business logic and OpenWeather client (later)
  middleware/   JWT, logging, errors (later)
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
- **admin** — manage categories and tools

## Database (5 entities)

See `docs/erd.png`, `docs/erd.dbml`, and the importable dump `backend/sql/bushcraft_tools.sql`.

| Entity | Table | Primary key | Foreign keys |
| --- | --- | --- | --- |
| User | `users` | `id` | — |
| Category | `categories` | `id` | — |
| Tool | `tools` | `id` | `category_id` → `categories.id` |
| Review | `reviews` | `id` | `user_id` → `users.id`, `tool_id` → `tools.id` |
| Favorite | `favorites` | `id` | `user_id` → `users.id`, `tool_id` → `tools.id` |

Columns:

1. **users** — `id`, `email` (unique), `password_hash`, `display_name`, `role` (`user` \| `admin`), `created_at`
2. **categories** — `id`, `name`, `slug` (unique)
3. **tools** — `id`, `category_id`, `name`, `brand`, `description`, `price`, `stock`
4. **reviews** — `id`, `user_id`, `tool_id`, `rating` (1–5), `comment`, `created_at`; unique `(user_id, tool_id)`
5. **favorites** — `id`, `user_id`, `tool_id`, `created_at`; unique `(user_id, tool_id)`

### Relationships

- Category **1 : N** Tool
- User **1 : N** Review
- Tool **1 : N** Review
- User **N : M** Tool through Favorite

### Sample accounts (in the SQL dump)

| Email | Password | Role |
| --- | --- | --- |
| `admin@bushcraft.local` | `Admin123!` | admin |
| `mara@bushcraft.local` | `User123!` | user |
| `jonas@bushcraft.local` | `User123!` | user |

## DAO layer

`App\Dao\BaseDao` holds reusable **getAll / getById / insert / update / delete** methods. Every query uses PDO prepared statements. Column names are limited to a per-entity whitelist so identifiers cannot come from request input.

Entity classes: `UserDao`, `CategoryDao`, `ToolDao`, `ReviewDao`, `FavoriteDao`.

`Database` reads host, port, name, user, and password from `backend/config/config.php` (not committed). Copy `config.example.php` to `config.php` locally.

`UserDao` hashes `password` with `password_hash()` and never returns `password_hash` in JSON.

## REST CRUD

Each entity supports Create (POST), Read (GET), Update (PUT and PATCH), and Delete (DELETE).

| Method | Path | Notes |
| --- | --- | --- |
| GET | `/users`, `/users/{id}` | List / one (no password hash) |
| POST | `/users` | Body: `email`, `password`, `display_name`, optional `role` |
| PUT / PATCH | `/users/{id}` | PUT expects full resource; PATCH is partial |
| DELETE | `/users/{id}` | |
| GET | `/categories`, `/categories/{id}` | |
| POST | `/categories` | `name`, `slug` |
| PUT / PATCH | `/categories/{id}` | |
| DELETE | `/categories/{id}` | Blocked if tools still reference the category (FK RESTRICT) |
| GET | `/tools`, `/tools/{id}` | Optional `?category_id=` |
| POST | `/tools` | `category_id`, `name`, `price`, optional `brand`, `description`, `stock` |
| PUT / PATCH | `/tools/{id}` | |
| DELETE | `/tools/{id}` | |
| GET | `/reviews`, `/reviews/{id}` | Optional `?tool_id=` or `?user_id=` |
| POST | `/reviews` | `user_id`, `tool_id`, `rating` (1–5), optional `comment` |
| PUT / PATCH | `/reviews/{id}` | |
| DELETE | `/reviews/{id}` | |
| GET | `/favorites`, `/favorites/{id}` | Optional `?user_id=` |
| POST | `/favorites` | `user_id`, `tool_id` |
| PUT / PATCH | `/favorites/{id}` | |
| DELETE | `/favorites/{id}` | |

JSON status codes: **200**, **201** (create), **400**, **404**, **500**.

## OpenWeather (external API)

The **Weather & camp recommendations** page (`#/weather`) is where the external API will appear. No weather calls run in Milestone 2.

## Setup

1. Start MySQL (WAMP: start `wampmysqld64`).
2. Copy config and set credentials:

   ```bash
   copy backend\config\config.example.php backend\config\config.php
   ```

   Default WAMP values: user `root`, empty password, database `bushcraft_tools`.

3. Import schema and sample data:

   ```bash
   "C:\wamp64\bin\mysql\mysql9.1.0\bin\mysql.exe" -u root < backend\sql\bushcraft_tools.sql
   ```

4. Optional: `composer install` in `backend/` (FlightPHP is also vendored under `backend/lib/flightphp/`).

## Run locally

Frontend (from `frontend/`):

```bash
php -S localhost:8766
```

Open `http://localhost:8766/`.

API (from `backend/`):

```bash
php -S localhost:8080 index.php
```

Example:

```bash
curl http://localhost:8080/tools
curl -X POST http://localhost:8080/categories -H "Content-Type: application/json" -d "{\"name\":\"Saws\",\"slug\":\"saws\"}"
```

Do not commit `backend/config/config.php`.

## Milestone 1 deliverables

- Project structure and `.gitignore`
- Static SPA with consistent bushcraft theme and responsive Bootstrap layout
- Draft ERD (`docs/erd.png`)
- README draft
- AI usage log (`docs/AI_LOG.md`)

## Milestone 2 deliverables

- MySQL dump with five entities and sample data: `backend/sql/bushcraft_tools.sql`
- Documented schema / ERD (`docs/erd.png`, `docs/erd.dbml`)
- `BaseDao` plus DAO classes for all five entities
- REST CRUD (GET, POST, PUT, PATCH, DELETE) using prepared statements
- Credentials loaded from `config.php`; `config.example.php` is what is committed
